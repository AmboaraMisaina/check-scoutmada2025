<?php
require_once 'functions/functions.php';

// Pour éviter tout texte avant le JSON
ob_start();
header('Content-Type: application/json');

$response = [];

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Missing ID');
    }

    $id = intval($_POST['id']);

    // Assurez-vous que $pdo est défini
    global $pdo;
    if (!$pdo) {
        throw new Exception('PDO not initialized');
    }

    updateKit($pdo, $id);

    $response = ['success' => true];
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Nettoie toute sortie accidentelle
ob_end_clean();
echo json_encode($response);
