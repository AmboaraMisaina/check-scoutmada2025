<?php
header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = intval($_POST['id']);

try {
    
    $stmt = $pdo->prepare("UPDATE participants SET kit=true WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
