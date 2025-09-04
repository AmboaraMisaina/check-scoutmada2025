<?php
require_once 'functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    exit('ID manquant');
}

$participant = getParticipantById($pdo, $id);
if (!$participant) {
    http_response_code(404);
    exit('Participant introuvable');
}

$qr_url = getQrCodeUrl($participant['qr_code']);
$qr_image = file_get_contents($qr_url);

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="qr_code_' . $participant['id'] . '.png"');
echo $qr_image;
exit;