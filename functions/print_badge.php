<?php
require_once 'functions.php';
checkAuthOrRedirect();

// --- TRAITEMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['print_ids'])) {
    $ids = array_map('intval', $_POST['print_ids']);

    $pdf = new FPDF('P', 'mm', 'A4');

    for ($i = 0; $i < count($ids); $i += 2) {
        $p1 = getParticipantById($pdo, $ids[$i]);
        $p2 = isset($ids[$i+1]) ? getParticipantById($pdo, $ids[$i+1]) : null;

        if ($p1) {
            $badge1 = [
                'nom' => $p1['nom'] ?? '',
                'prenom' => $p1['prenom'] ?? '',
                'pays' => $p1['pays'] ?? '',
                'type' => $p1['type'] ?? '',
                'qr_code' => $p1['qr_code'] ?? ''
            ];
                
            // Marquer comme printed
            $stmt = $pdo->prepare("UPDATE participants SET isPrinted = 1 WHERE id = ?");
            $stmt->execute([$p1['id']]);
        }

        $badge2 = null;
        if ($p2) {
            $badge2 = [
                'nom' => $p2['nom'] ?? '',
                'prenom' => $p2['prenom'] ?? '',
                'pays' => $p2['pays'] ?? '',
                'type' => $p2['type'] ?? '',
                'qr_code' => $p2['qr_code'] ?? ''
            ];

            // Marquer comme printed
            $stmt = $pdo->prepare("UPDATE participants SET isPrinted = 1 WHERE id = ?");
            $stmt->execute([$p2['id']]);
        }

        genererFeuilleBadges($pdf, $badge1, $badge2);
    }

    // Sortie PDF
    $pdf->Output('I', 'badges.pdf');
    exit;
}

// Si pas de POST → retour
header('Location: ../participants.php');
exit;
