<?php
require_once 'db.php';
require_once 'auth.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

// Vérifie si l'utilisateur est connecté
function checkAuthOrRedirect()
{
    checkAuth();
}

// ---------------- PARTICIPANTS ----------------

// Récupère tous les participants
function getAllParticipants(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT * from participants ORDER BY participants.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllParticipantsWithFilter(PDO $pdo, $filter_name = '', $filter_printed = '', $filter_type = '', $limit = 20, $offset = 0) {
    $sql = "SELECT * FROM participants WHERE 1=1";
    $params = [];

    // Filtre par nom ou prénom
    if (!empty($filter_name)) {
        $sql .= " AND (nom LIKE :name OR prenom LIKE :name OR pays LIKE :name)";
        $params[':name'] = '%' . $filter_name . '%';
    }

    // Filtre par imprimé
    if ($filter_printed === '1') {
        $sql .= " AND isPrinted = 1";
    } elseif ($filter_printed === '0') {
        $sql .= " AND (isPrinted = 0 OR isPrinted IS NULL)";
    }
    // Filtre par type
    if (!empty($filter_type)) {
        $sql .= " AND type = :type";
        $params[':type'] = $filter_type;
    }

    $sql .= " ORDER BY nom ASC, prenom ASC";
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getTotalParticipantsWithFilter(PDO $pdo, $filter_name = '', $filter_printed = '' , $filter_type = '') {
    $sql = "SELECT COUNT(*) as total FROM participants WHERE 1=1";
    $params = [];

    // Filtre par nom (nom ou prénom)
    if (!empty($filter_name)) {
        $sql .= " AND (nom LIKE :name OR prenom LIKE :name OR pays LIKE :name)";
        $params[':name'] = '%' . $filter_name . '%';
    }

    // Filtre par colonne isPrinted
    if ($filter_printed === '1') {
        $sql .= " AND isPrinted = 1";
    } elseif ($filter_printed === '0') {
        $sql .= " AND (isPrinted = 0 OR isPrinted IS NULL)";
    }
    // Filtre par type
    if (!empty($filter_type)) {
        $sql .= " AND type = :type";
        $params[':type'] = $filter_type;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return intval($result['total'] ?? 0);
}

// Récupère un participant par ID
function getParticipantById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function normalizeName($name) {
    $name = mb_strtolower($name, 'UTF-8');            // minuscules
    $name = str_replace(['-', ' '], '', $name);       // supprimer tirets et espaces
    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name); // enlever accents
    return $name;
}

function addParticipant($pdo, $nom, $prenom, $email, $type, $pays, $photoPath)
{

    // Vérifier les champs obligatoires
    if (!$nom ) {
        return ['success' => false, 'message' => ' Please fill in all fields correctly.'];
    }

    // Normaliser le nom : retirer espaces multiples et tirets, mettre en majuscules
    $normalizedNom = normalizeName($nom);

    // Séparer les mots du nom
    $words = explode(' ', $normalizedNom);

    // Construire les conditions LIKE pour chaque mot
    $likeConditions = [];
    $params = [];
    foreach ($words as $word) {
        $likeConditions[] = "nom LIKE ?";
        $params[] = "%$word%";
    }
    $likeSql = implode(' AND ', $likeConditions);


    // Vérifier si un nom similaire existe avec SOUNDEX
    $stmt = $pdo->prepare("
        SELECT id, nom 
        FROM participants 
        WHERE 
            SOUNDEX(REPLACE(REPLACE(LOWER(nom), ' ', ''), '-', ''))
            = SOUNDEX(REPLACE(REPLACE(LOWER(?), ' ', ''), '-', ''))
            AND $likeSql
    ");
    $stmt->execute([$nom]);
    if ($stmt->rowCount() > 0) {
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'success' => false, 
            'message' => "A participant with a similar sounding name already exists: ID = {$existing['id']}, Name = {$existing['nom']}"
        ];
    }

    // Insertion avec pays
    $stmt = $pdo->prepare("INSERT INTO participants (nom, email, type, pays , photo) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$nom . ' ' . $prenom, $email, $type, $pays, $photoPath])) {
        // Récupérer l'ID du participant inséré
        $participantId = $pdo->lastInsertId();

        // Générer le contenu du QR code (nom_prenom_id)
        $qr_code = $nom . '' . $prenom . '_' . $participantId;

        // Mettre à jour le participant avec le QR code
        $updateStmt = $pdo->prepare("UPDATE participants SET qr_code = ? WHERE id = ?");
        $updateStmt->execute([$qr_code, $participantId]);
        if ($type  == 'guest') {
            return ['success' => true, 'message' => 'Guest added successfully!'];
        }

        return ['success' => true, 'message' => 'Participant added successfully!'];
    } else {
        return ['success' => false, 'message' => 'Error adding participant.'];
    }
}
// Met à jour un participant
function updateParticipant(PDO $pdo, $id, $nom, $prenom, $email, $type, $pays, $photoPath = null)
{
    // Vérifier les champs obligatoires
    if (!$nom || !$email || !$type || !$pays) {
        return ['success' => false, 'message' => 'Please fill in all fields correctly.'];
    }

    // Vérifier si l'email existe déjà pour un autre participant
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Another participant with this email already exists.'];
    }

    // Mettre à jour le participant
    $stmt = $pdo->prepare("UPDATE participants SET nom = ?, email = ?, type = ?, pays = ?, photo = ? WHERE id = ?");
    $result = $stmt->execute([$nom . ' ' . $prenom, $email, $type, $pays, $photoPath, $id]);

    if ($result) {
        return ['success' => true, 'message' => 'Participant updated successfully!'];
    } else {
        return ['success' => false, 'message' => 'Error updating participant.'];
    }
}
function updatePhotoParticipant(PDO $pdo, $id, $photoPath)
{
    $stmt = $pdo->prepare("UPDATE participants SET photo = ? WHERE id = ?");
    return $stmt->execute([$photoPath, $id]);
}
// Supprime un participant
function deleteParticipant(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id=?");
    return $stmt->execute([$id]);
}

function updateKit(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("UPDATE participants SET kit=true WHERE id = ?");
    return $stmt->execute([$id]);
}

function updateWithPhoto(PDO $pdo, $id, $withPhoto)
{
    $stmt = $pdo->prepare("UPDATE participants SET withPhoto = ? WHERE id = ?");
    return $stmt->execute([$withPhoto, $id]);
}


// ---------------- EVENEMENTS ----------------
function getEvenementsByJour(PDO $pdo, DateTime $date)
{
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE date_evenement=? ORDER BY horaire_debut ASC");
    $stmt->execute([$date->format('Y-m-d')]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEvenementById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addEvenement(PDO $pdo, $date_evenement, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a = [])
{
    $ouvert_a_str = implode(',', $ouvert_a);
    $stmt = $pdo->prepare("INSERT INTO evenements (date_evenement, titre, description, horaire_debut, horaire_fin, ouvert_a) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$date_evenement, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a_str]);
}

function updateEvenement(PDO $pdo, $date_evenement , $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a = [], $id,$participation_unique = 0)
{
    $ouvert_a_str = implode(',', $ouvert_a);
    $stmt = $pdo->prepare("UPDATE evenements SET date_evenement=?, titre=?, description=?, horaire_debut=?, horaire_fin=?, ouvert_a=?, nb_participation=? WHERE id=?");
    return $stmt->execute([$date_evenement, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a_str, $participation_unique, $id]);
}

function deleteEvenement(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM evenements WHERE id=?");
    return $stmt->execute([$id]);
}




// ---------------- STATISTIQUES ----------------

function getStats($pdo)
{
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants");
    $totalParticipants = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants WHERE type = 'delegue'");
    $totalDelegues = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants WHERE type = 'observateur'");
    $totalObservateurs = $stmt->fetch()['total'];

    return [
        'total' => $totalParticipants,
        'delegues' => $totalDelegues,
        'observateurs' => $totalObservateurs,
    ];
}

// ---------------- CHECK IN ----------------
// Récupère les événements du jour avec indicateur "en cours"
function getEvenementsDuJour(PDO $pdo, string $date, string $timezone = 'Indian/Antananarivo') {
    date_default_timezone_set($timezone);
    $maintenant = time();

    $stmt = $pdo->prepare("
        SELECT e.*, e.date_evenement
        FROM evenements e
        WHERE e.date_evenement = ?
        ORDER BY e.horaire_debut ASC
    ");
    $stmt->execute([$date]);
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter un indicateur 'enCours' pour chaque événement
    foreach ($evenements as &$e) {
        $timestampDebut = strtotime("$date {$e['horaire_debut']}");
        $timestampDebut = strtotime("-30 minutes", $timestampDebut); // Décalage de 15 min
        $timestampFin = strtotime("$date {$e['horaire_fin']}");
        $e['enCours'] = ($maintenant >= $timestampDebut && $maintenant <= $timestampFin);
    }

    return $evenements;
}

/**
 * CRUD pour la table admins
 */

// Créer un admin
function createAdmin($pdo, $username, $password, $role) {
    $hash = hash('sha256', $password);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $hash, $role]);
}

// Lire un admin par ID
function getAdminById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Lire tous les admins
function getAllAdmins($pdo) {
    $stmt = $pdo->query("SELECT * FROM admins ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mettre à jour un admin (mot de passe optionnel)
function updateAdmin($pdo, $id, $username, $role, $password = null) {
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $hash, $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $role, $id]);
    }
}

// Supprimer un admin
function deleteAdmin($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    return $stmt->execute([$id]);
}

function getAllPays($pdo) {
    $stmt = $pdo->query("SELECT * FROM pays ORDER BY nom ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getQrCodeUrl($qrText, $size = 300)
{
    $encodedText = urlencode($qrText);
    return "https://api.qrserver.com/v1/create-qr-code/?data={$encodedText}&size={$size}x{$size}";
}



function genererFeuilleBadges($pdf, $badge1, $badge2 = null) {
    $pageWidth = 210;
    $pageHeight = 297;
    $halfHeight = $pageHeight / 2; 
    $halfWidth  = $pageWidth / 2; 

    $pdf->AddPage();

    ajouterBadgeRectoVerso($pdf, $badge1, 0, 0, $halfWidth, $halfHeight);

    if ($badge2) {
        ajouterBadgeRectoVerso($pdf, $badge2, 0, $halfHeight, $halfWidth, $halfHeight);
    }
}

function ajouterBadgeRectoVerso($pdf, $badgeData, $x, $y, $w, $h) {
    switch ($badgeData['type']) {
        case 'organizing team':
            $type = '1';
            break;
        case 'delegate':
            $type = '2';
            break;
        case 'observer':
            $type = '3';
            break;
        case 'wosm team':
            $type = '4';
            break;  
        case 'partner':
            $type = '5';
            break;
        case 'international service team':
            $type = '6';
            break;
        default:
            $type = '-1';
            break;
    }

    $rectoTemplate = __DIR__ . '/../template/BADGE_' . $type . '.png';
    $versoTemplate = __DIR__ . '/../template/BADGE_0.png';
    $pdf->Image($rectoTemplate, $x, $y, $w, $h);

    $maxWidth = $w - 26; // largeur max dispo pour le texte
    $nom = utf8_decode($badgeData['nom']);

    // Définir police
    $pdf->AddFont('NotoSans','','NotoSans.php');
    $pdf->AddFont('NotoSansBold','B','NotoSans-Bold.php');
    $pdf->AddFont('ScoutsGTPlanarBold', 'B', 'Scouts-GT-Planar-Bold.php');

    $pdf->SetFont('ScoutsGTPlanarBold', 'B', 14);
    $pdf->SetTextColor(0, 0, 0, 0);

    // Découper le nom en mots dynamiquement
    $words = explode(" ", $nom);
    $line1 = "";
    $line2 = "";

    foreach ($words as $word) {
        $testLine = trim($line1 . " " . $word);
        if ($pdf->GetStringWidth($testLine) <= $maxWidth) {
            $line1 = $testLine;
        } else {
            $line2 .= " " . $word;
        }
    }
    if (!empty(trim($line2))) {
        $pdf->SetFont('ScoutsGTPlanarBold', 'B', 12);
    }

    if ($type != "-1") {
        // --- Première ligne  ---
        $pdf->SetXY($x + 12 , $y + $h - 66);
        $pdf->Cell($maxWidth, 6, trim($line1), 0, 0, 'C');

        // --- Deuxième ligne si nécessaire ---
        if (!empty(trim($line2))) {
            $pdf->SetXY($x + 11, $y + $h - 62);
            $pdf->Cell($maxWidth, 6, trim($line2), 0, 0, 'C');
        }

        
        // === Pays ===
        $pdf->SetFont('ScoutsGTPlanarBold', 'B', 14);
        $pdf->SetTextColor(0,0,0,0);
        $pays = utf8_decode($badgeData['pays']);
        $pdf->SetXY($x, $y + $h - 45);
        $pdf->Cell($w, 6, $pays, 0, 0, 'C');
    }else{
        $pdf->SetXY($x + 12 , $y + $h - 62);
        $pdf->Cell($maxWidth, 6, trim($line1), 0, 0, 'C');

        // --- Deuxième ligne si nécessaire ---
        if (!empty(trim($line2))) {
            $pdf->SetXY($x + 11, $y + $h - 60);
            $pdf->Cell($maxWidth, 6, trim($line2), 0, 0, 'C');
        }
    }

    // --- Verso (QR Code) ---
    if (!empty($badgeData['qr_code'])) {
        $pdf->Image($versoTemplate, $x + $w, $y, $w, $h);
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($badgeData['qr_code']) . "&size=200x200";

        $tmpDir = __DIR__ . '/../tmp';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $qr_tmp = $tmpDir . "/qr_" . md5($badgeData['qr_code']) . ".png";
        file_put_contents($qr_tmp, file_get_contents($qr_url));

        $qrCodeSize = 50; // Modifier cette valeur pour ajuster la taille du QR code
        $pdf->Image($qr_tmp, $x + $w + ($w / 2 - $qrCodeSize / 2), $y + ($h / 2 - $qrCodeSize / 2), $qrCodeSize, $qrCodeSize);
        unlink($qr_tmp);
    }
}

