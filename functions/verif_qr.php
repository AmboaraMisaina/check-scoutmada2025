<?php
// require_once 'db.php';

// if (!isset($_POST['qr_code'])) {
//     echo "Aucun code QR reçu.";
//     exit;
// }

// $qr_code = $_POST['qr_code'];
// $stmt = $pdo->prepare("SELECT * FROM participants WHERE qr_code = ?");
// $stmt->execute([$qr_code]);
// $participant = $stmt->fetch();

// if ($participant) {
//     $evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : null;

//     if ($evenement_id) {
//         // Récupère l'événement pour vérifier nb_participation et ouvert_a
//         $evtStmt = $pdo->prepare("SELECT nb_participation, ouvert_a FROM evenements WHERE id = ?");
//         $evtStmt->execute([$evenement_id]);
//         $evenement = $evtStmt->fetch();

//         if (!$evenement) {
//             echo "Événement introuvable.";
//             exit;
//         }
        
//         // Vérifie si le type du participant est autorisé
//         $typesAutorises = explode(',', $evenement['ouvert_a']);
//         if (!in_array($participant['type'], $typesAutorises)) {
//             echo "Ce type de participant n'est pas autorisé pour cet événement.";
//             exit;
//         }

//         // Vérifie la règle de participation
//         if ($evenement['nb_participation'] == 1) {
//             // Un seul check-in autorisé
//             $checkStmt = $pdo->prepare("SELECT id FROM enregistrement WHERE participant_id = ? AND evenement_id = ?");
//             $checkStmt->execute([$participant['id'], $evenement_id]);
//             if ($checkStmt->rowCount() > 0) {
//                 echo "Participation refusée.";
//                 exit;
//             } else {
//                 // Insère la présence avec date/heure
//                 $insertStmt = $pdo->prepare("INSERT INTO enregistrement (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
//                 if ($insertStmt->execute([$participant['id'], $evenement_id])) {
//                     echo "Présence enregistrée pour " . htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);
//                 } else {
//                     echo "Erreur lors de l'enregistrement de la présence.";
//                 }
//             }
//         } else {
//             // Participation illimitée (enregistre à chaque fois)
//             $insertStmt = $pdo->prepare("INSERT INTO enregistrement (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
//             if ($insertStmt->execute([$participant['id'], $evenement_id])) {
//                 echo "Présence enregistrée pour " . htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);
//             } else {
//                 echo "Erreur lors de l'enregistrement de la présence.";
//             }
//         }
//     } else {
//         echo "Erreur : Événement non spécifié.";
//     }
// } else {
//     echo "Erreur : Aucun participant correspondant.";
// }


require_once 'db.php';

header('Content-Type: application/json'); // réponse JSON

$response = [
    'success' => false,
    'message' => '',
    'photo_path' => ''
];

if (!isset($_POST['qr_code'])) {
    $response['message'] = "Aucun code QR reçu.";
    echo json_encode($response);
    exit;
}

$qr_code = $_POST['qr_code'];
$stmt = $pdo->prepare("SELECT * FROM participants WHERE qr_code = ?");
$stmt->execute([$qr_code]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    $response['message'] = "Aucun participant correspondant.";
    echo json_encode($response);
    exit;
}

$evenement_id = isset($_POST['evenement_id']) ? intval($_POST['evenement_id']) : null;

if (!$evenement_id) {
    $response['message'] = "Erreur : Événement non spécifié.";
    echo json_encode($response);
    exit;
}

// Récupération de l'événement
$evtStmt = $pdo->prepare("SELECT nb_participation, ouvert_a FROM evenements WHERE id = ?");
$evtStmt->execute([$evenement_id]);
$evenement = $evtStmt->fetch(PDO::FETCH_ASSOC);

if (!$evenement) {
    $response['message'] = "Événement introuvable.";
    echo json_encode($response);
    exit;
}

// Vérifie si le type du participant est autorisé
$typesAutorises = explode(',', $evenement['ouvert_a']);
if (!in_array($participant['type'], $typesAutorises)) {
    $response['message'] = "Ce type de participant n'est pas autorisé pour cet événement.";
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
    $response['message'] = "Participation refusée (déjà enregistrée).";
} else {
    $insertStmt = $pdo->prepare("INSERT INTO enregistrement (participant_id, evenement_id, created_at) VALUES (?, ?, NOW())");
    if ($insertStmt->execute([$participant['id'], $evenement_id])) {
        $response['success'] = true;
        $response['message'] = "Présence enregistrée pour " . htmlspecialchars($participant['nom']) . " " . htmlspecialchars($participant['prenom']);
        $response['photo_path'] = $participant['photo']; // chemin de la photo
    } else {
        $response['message'] = "Erreur lors de l'enregistrement de la présence.";
    }
}

echo json_encode($response);
