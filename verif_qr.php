<?php
require_once 'db.php';

if (!isset($_POST['qr_code'])) {
    echo "Aucun code QR reçu.";
    exit;
}

$qr_code = $_POST['qr_code'];
$stmt = $pdo->prepare("SELECT * FROM participants WHERE qr_code = ?");
$stmt->execute([$qr_code]);
$participant = $stmt->fetch();

if ($participant) {
    $evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : null;

    if ($evenement_id) {
        // Récupère l'événement pour vérifier nb_participation et ouvert_a
        $evtStmt = $pdo->prepare("SELECT nb_participation, ouvert_a FROM evenements WHERE id = ?");
        $evtStmt->execute([$evenement_id]);
        $evenement = $evtStmt->fetch();

        if (!$evenement) {
            echo "Événement introuvable.";
            exit;
        }
        
        // Vérifie si le type du participant est autorisé
        $typesAutorises = explode(',', $evenement['ouvert_a']);
        if (!in_array($participant['type'], $typesAutorises)) {
            echo "Ce type de participant n'est pas autorisé pour cet événement.  "  . implode(", ", $typesAutorises) . " / " . $participant['type'];
            exit;
        }

        // Vérifie la règle de participation
        if ($evenement['nb_participation'] == 1) {
            // Un seul check-in autorisé
            $checkStmt = $pdo->prepare("SELECT id FROM planing WHERE participant_id = ? AND evenement_id = ?");
            $checkStmt->execute([$participant['id'], $evenement_id]);
            if ($checkStmt->rowCount() > 0) {
                echo "Participation refusée.";
                exit;
            } else {
                // Insère la présence avec date/heure
                $insertStmt = $pdo->prepare("INSERT INTO planing (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
                if ($insertStmt->execute([$participant['id'], $evenement_id])) {
                    echo "Présence enregistrée pour " . htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);
                } else {
                    echo "Erreur lors de l'enregistrement de la présence.";
                }
            }
        } else {
            // Participation illimitée (enregistre à chaque fois)
            $insertStmt = $pdo->prepare("INSERT INTO planing (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
            if ($insertStmt->execute([$participant['id'], $evenement_id])) {
                echo "Présence enregistrée pour " . htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);
            } else {
                echo "Erreur lors de l'enregistrement de la présence.";
            }
        }
    } else {
        echo "Erreur : Événement non spécifié.";
    }
} else {
    echo "Erreur : Aucun participant correspondant.";
}