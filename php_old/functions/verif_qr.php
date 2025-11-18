<?php
require_once 'db.php';

header('Content-Type: application/json'); // réponse JSON

$response = [
    'success' => false,
    'message' => '',
    'photo_path' => ''
];

if (!isset($_POST['qr_code'])) {
    $response['message'] = "No QR code received.";
    echo json_encode($response);
    exit;
}

$qr_code = $_POST['qr_code'];
$stmt = $pdo->prepare("SELECT * FROM participants WHERE qr_code = ?");
$stmt->execute([$qr_code]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

$response['name'] = htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);

if (!$participant) {
    $response['message'] = "No matching participant found.";
    echo json_encode($response);
    exit;
}

$evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : null;

if (!$evenement_id) {
    $response['message'] = "Error: Event not specified.";
    echo json_encode($response);
    exit;
}

// Récupération de l'événement
$evtStmt = $pdo->prepare("SELECT nb_participation, ouvert_a FROM evenements WHERE id = ?");
$evtStmt->execute([$evenement_id]);
$evenement = $evtStmt->fetch(PDO::FETCH_ASSOC);

if (!$evenement) {
    $response['message'] = "Event not found.";
    echo json_encode($response);
    exit;
}

// Vérifie si le type du participant est autorisé
$typesAutorises = explode(',', $evenement['ouvert_a']);
if (!in_array($participant['type'], $typesAutorises)) {
    $response['message'] = "This type of participant is not allowed for this event.";
    echo json_encode($response);
    exit;
}

if (($participant['type'] === 'delegate' || $participant['type'] === 'observer') && !$participant['paid']) {
    $response['message'] = "Participant must complete payment to access this event.";
    echo json_encode($response);
    exit;
}

// Vérifie la règle de participation
$alreadyChecked = false;
if ($evenement['nb_participation'] == 1) {
    $checkStmt = $pdo->prepare("SELECT id FROM enregistrement WHERE participant_id = ? AND evenement_id = ?");
    $checkStmt->execute([$participant['id'], $evenement_id]);
    if ($checkStmt->rowCount() > 0) {
        $alreadyChecked = true;
    }
}

if ($alreadyChecked) {
    $response['message'] = "Participation refused (already registered).";
} else {
    $insertStmt = $pdo->prepare("INSERT INTO enregistrement (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
    if ($insertStmt->execute([$participant['id'], $evenement_id])) {
        $response['success'] = true;
        $response['photo_path'] = $participant['photo']; // path to the photo
    } else {
        $response['message'] = "Error recording attendance.";
    }
}

echo json_encode($response);