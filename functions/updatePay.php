<?php
require_once 'db.php';

header('Content-Type: application/json');

$response = [];

try {
    // Vérifie que l'id est envoyé
    if (!isset($_POST['id'])) {
        throw new Exception('Missing ID');
    }

    $id = intval($_POST['id']);

    $stmt = $pdo->prepare("UPDATE participants SET paid=true WHERE id = ?");
    $stmt->execute([$id]);

    $response = ['success' => true];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
