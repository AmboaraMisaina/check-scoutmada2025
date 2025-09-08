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
    $stmt = $pdo->query("SELECT * FROM participants ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère un participant par ID
function getParticipantById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addParticipant($pdo, $nom, $prenom, $email, $type)
{
    if (!$nom || !$prenom || !$email || !in_array($type, ['Delegue', 'Observateur', 'Comité d\'organisation', 'WOSM Team'])) {
        return ['success' => false, 'message' => 'Veuillez remplir tous les champs correctement.'];
    }

    // Vérifier si l'email existe
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Un participant avec cet email existe déjà.'];
    }

    // Insertion
    $stmt = $pdo->prepare("INSERT INTO participants (nom, prenom, email, type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nom, $prenom, $email, $type])) {
        // Récupérer l'ID du participant inséré
        $participantId = $pdo->lastInsertId();

        // Générer le contenu du QR code (nom_participant_id)
        $qr_code = $nom . '_' . $prenom . '_' . $participantId;

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
function updateParticipant(PDO $pdo, $id, $nom, $prenom, $email, $type)
{
    $stmt = $pdo->prepare("UPDATE participants SET nom=?, prenom=?, email=?, type=? WHERE id=?");
    return $stmt->execute([$nom, $prenom, $email, $type, $id]);
}

// Supprime un participant
function deleteParticipant(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id=?");
    return $stmt->execute([$id]);
}
// ---------------- JOURS PROGRAMMES ----------------
function getAllJoursProgrammes($pdo)
{
    $stmt = $pdo->query("SELECT * FROM jours_programmes ORDER BY date_jour ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJourById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM jours_programmes WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addJourProgramme(PDO $pdo, string $date_jour)
{
    $stmt = $pdo->prepare("INSERT INTO jours_programmes (date_jour) VALUES (?)");
    return $stmt->execute([$date_jour]);
}

function deleteJourProgramme(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("DELETE FROM jours_programmes WHERE id=?");
    return $stmt->execute([$id]);
}

// ---------------- EVENEMENTS ----------------
function getEvenementsByJour(PDO $pdo, int $jour_id)
{
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE jour_id=? ORDER BY horaire_debut ASC");
    $stmt->execute([$jour_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEvenementById(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addEvenement(PDO $pdo, $jour_id, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a = [])
{
    $ouvert_a_str = implode(',', $ouvert_a);
    $stmt = $pdo->prepare("INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, ouvert_a) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$jour_id, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a_str]);
}

function updateEvenement(PDO $pdo, $id, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a = [])
{
    $ouvert_a_str = implode(',', $ouvert_a);
    $stmt = $pdo->prepare("UPDATE evenements SET titre=?, description=?, horaire_debut=?, horaire_fin=?, ouvert_a=? WHERE id=?");
    return $stmt->execute([$titre, $description, $horaire_debut, $horaire_fin, $ouvert_a_str, $id]);
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
        SELECT e.*, j.date_jour
        FROM evenements e
        JOIN jours_programmes j ON e.jour_id = j.id
        WHERE j.date_jour = ?
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
    $hash = password_hash($password, PASSWORD_DEFAULT);
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
