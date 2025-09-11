<?php
require_once 'functions.php';
checkAuthOrRedirect();

header('Content-Type: application/json');

if ($_SESSION['role'] == 'checkin') {
    echo json_encode(['success' => false, 'message' => 'Accès interdit']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$photoData = $input['photoData'] ?? '';

if (!$id || !$photoData) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

if (isset($photoData) && !empty($photoData)) {
    $data = $photoData;
    $data = preg_replace('#^data:image/[^;]+;base64,#', '', $data);
    $decoded = base64_decode($data);

    $uploadDir = '../uploads/photos/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $photoName = uniqid('participant_') . '.jpg';
    $photoPath = $uploadDir . $photoName;

    file_put_contents($photoPath, $decoded);
}

$ok = updatePhotoParticipant($pdo, $id, $photoPath);
$ok = $ok && updateWithPhoto($pdo, $id, true);

echo json_encode(['success' => $ok]);
