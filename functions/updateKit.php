<?php
require_once 'functions/functions.php';

// Capture toute sortie avant le JSON
ob_start();
header('Content-Type: application/json');

$response = [];

try {
    // Vérifie que l'id est envoyé
    if (!isset($_POST['id'])) {
        throw new Exception('Missing ID');
    }

    $id = intval($_POST['id']);

    // Vérifie que $pdo existe
    global $pdo;
    if (!$pdo) {
        throw new Exception('PDO not initialized');
    }

    // Appel de ta fonction existante
    updateKit($pdo, $id);

    $response = ['success' => true];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Supprime toute sortie non désirée
ob_end_clean();
echo json_encode($response);
exit;
