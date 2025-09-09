<?php
require_once 'db.php';
require_once 'auth.php';

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
function getAllParticipantsWithFilter(PDO $pdo, $filter_name = '', $filter_printed = '') {
    $sql = "SELECT * FROM participants WHERE 1=1";
    $params = [];

    // Filtre par nom ou prénom
    if ($filter_name) {
        $sql .= " AND (nom LIKE :name OR prenom LIKE :name)";
        $params[':name'] = "%$filter_name%";
    }

    // Filtre printed directement si valeur fournie
    if ($filter_printed !== '') {
        $sql .= " AND isPrinted = :printed";
        $params[':printed'] = $filter_printed;
    }

    $sql .= " ORDER BY nom ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère un participant par ID
function getParticipantById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addParticipant($pdo, $nom, $prenom, $email, $type, $pays_id, $photoPath = null)
{
    // Vérifier les champs obligatoires
    if (!$nom || !$prenom || !$email || !$type || !$pays_id) {
        return ['success' => false, 'message' => 'Veuillez remplir tous les champs correctement.'];
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Un participant avec cet email existe déjà.'];
    }

    // Insertion avec pays
    $stmt = $pdo->prepare("INSERT INTO participants (nom, email, type, pays , photo) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$nom . ' ' . $prenom, $email, $type, $pays_id, $photoPath])) {
        // Récupérer l'ID du participant inséré
        $participantId = $pdo->lastInsertId();

        // Générer le contenu du QR code (nom_prenom_id)
        $qr_code = $nom . '' . $prenom . '_' . $participantId;

        // Mettre à jour le participant avec le QR code
        $updateStmt = $pdo->prepare("UPDATE participants SET qr_code = ? WHERE id = ?");
        $updateStmt->execute([$qr_code, $participantId]);

        return ['success' => true, 'message' => 'Participant ajouté avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de l\'ajout du participant.'];
    }
}


function getQrCodeUrl($qrText, $size = 300)
{
    $encodedText = urlencode($qrText);
    return "https://api.qrserver.com/v1/create-qr-code/?data={$encodedText}&size={$size}x{$size}";
}

// Met à jour un participant
function updateParticipant(PDO $pdo, $id, $nom, $prenom, $email, $type, $pays_id, $photoPath = null)
{
    // Vérifier les champs obligatoires
    if (!$nom || !$prenom || !$email || !$type || !$pays_id) {
        return ['success' => false, 'message' => 'Veuillez remplir tous les champs correctement.'];
    }

    // Vérifier si l'email existe déjà pour un autre participant
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Un autre participant avec cet email existe déjà.'];
    }

    // Mettre à jour le participant
    $stmt = $pdo->prepare("UPDATE participants SET nom = ?, email = ?, type = ?, pays = ?, photo = ? WHERE id = ?");
    $result = $stmt->execute([$nom . ' ' . $prenom, $email, $type, $pays_id, $photoPath, $id]);

    if ($result) {
        return ['success' => true, 'message' => 'Participant mis à jour avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du participant.'];
    }
}

// Supprime un participant
function deleteParticipant(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id=?");
    return $stmt->execute([$id]);
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

function genererBadge($name, $nso, $type, $qr_code, $side = 'recto') {
    $template_recto = __DIR__ . '/../template/badge_recto.png';
    $template_verso = __DIR__ . '/../template/badge_verso.png';
    $font_bold = __DIR__ . '/../template/font/caliban-font/Caliban-m132.ttf';

    if (!file_exists($font_bold)) {
        die("Erreur : police introuvable ($font_bold)");
    }

    $name = mb_convert_encoding($name, 'UTF-8', mb_detect_encoding($name));
    $nso  = mb_convert_encoding($nso,  'UTF-8', mb_detect_encoding($nso));
    $type = mb_convert_encoding($type, 'UTF-8', mb_detect_encoding($type));

    if ($side === 'recto') {
        $im = imagecreatefrompng($template_recto);
    } else {
        $im = imagecreatefrompng($template_verso);
    }

    imagealphablending($im, true);
    imagesavealpha($im, true);

    if ($side === 'recto') {
        /** -------- TYPE -------- */
        $type_text = strtoupper($type);
        $type_font_size = 75;
        $type_color = imagecolorallocate($im, 255, 255, 255);

        $bbox = imagettfbbox($type_font_size, 0, $font_bold, $type_text);
        $text_width = $bbox[2] - $bbox[0];
        $type_x = (imagesx($im) - $text_width) / 2;
        $type_y = 1100;

        imagettftext($im, $type_font_size, 0, $type_x, $type_y, $type_color, $font_bold, $type_text);

        /** -------- NOM -------- */
        $name_font_size = 55;
        $name_color = imagecolorallocate($im, 106, 27, 154);
        $max_width = imagesx($im) - 400;

        // Découpage automatique
        $lines = [];
        $words = explode(" ", $name);
        $current_line = "";

        foreach ($words as $word) {
            $test_line = trim($current_line . " " . $word);
            $bbox = imagettfbbox($name_font_size, 0, $font_bold, $test_line);
            $test_width = $bbox[2] - $bbox[0];
            if ($test_width <= $max_width) {
                $current_line = $test_line;
            } else {
                if ($current_line !== "") {
                    $lines[] = $current_line;
                }
                $current_line = $word;
            }
        }
        if ($current_line !== "") {
            $lines[] = $current_line;
        }

        // Limite à 2 lignes max
        if (count($lines) > 2) {
            $lines = array_slice($lines, 0, 2);
            $last = $lines[1];
            while (true) {
                $bbox = imagettfbbox($name_font_size, 0, $font_bold, $last . '…');
                $test_width = $bbox[2] - $bbox[0];
                if ($test_width <= $max_width || mb_strlen($last) <= 1) break;
                $last = mb_substr($last, 0, -1);
            }
            $lines[1] = trim($last) . '…';
        }

        // Positionnement : si 1 ligne → on descend un peu, si 2 lignes → normal
        if (count($lines) === 1) {
            $start_y = 1560; // ligne unique (centrée un peu plus bas)
        } else {
            $start_y = 1540; // 2 lignes → on garde la position originale
        }
        $line_spacing = 100;

        foreach ($lines as $i => $line) {
            $bbox = imagettfbbox($name_font_size, 0, $font_bold, $line);
            $text_width = $bbox[2] - $bbox[0];
            $x = (imagesx($im) - $text_width) / 2;
            $y = $start_y + ($i * $line_spacing);
            imagettftext($im, $name_font_size, 0, $x, $y, $name_color, $font_bold, $line);
        }

        /** -------- NSO -------- */
        if (!empty($nso)) {
            $nso_font_size = 50;
            $nso_color = $type_color;
            $bbox = imagettfbbox($nso_font_size, 0, $font_bold, $nso);
            $text_width = $bbox[2] - $bbox[0];
            $nso_x = (imagesx($im) - $text_width) / 2;

            // On place le NSO toujours en dessous du texte
            $nso_y = $start_y + (count($lines) * $line_spacing) + 90;

            imagettftext($im, $nso_font_size, 0, $nso_x, $nso_y, $nso_color, $font_bold, $nso);
        }
    } else {
        /** -------- VERSO -------- */
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qr_code) . "&size=300x300";
        $qr_data = @file_get_contents($qr_url);
        if ($qr_data !== false) {
            $qr_img = imagecreatefromstring($qr_data);
            if ($qr_img) {
                $qr_size = 700;
                $qr_x = (imagesx($im) - $qr_size) / 2;
                $qr_y = 980;
                imagecopyresampled($im, $qr_img, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qr_img), imagesy($qr_img));
                imagedestroy($qr_img);
            }
        }
    }

    return $im;
}
    