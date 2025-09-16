<?php
require_once 'db.php';

// require_once 'functions/functions.php';

// echo 1;

header('Content-Type: application/json');

$response = [];

try {
    // Vérifie que l'id est envoyé
    if (!isset($_GET['id'])) {
        throw new Exception('Missing ID');
    }

    $id = intval($_GET['id']);

    // Vérifie que $pdo existe
    global $pdo;
    if (!$pdo) {
        throw new Exception('PDO not initialized');
    }

    $stmt = $pdo->prepare("UPDATE participants SET kit=true WHERE id = ?");
    $stmt->execute([$id]);

    $response = ['success' => true];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
