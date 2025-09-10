<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();


$countries = require "country.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: participants.php');
    exit;
}

// Récupérer le participant
$participant = getParticipantById($pdo, (int)$id);
if (!$participant) {
    header('Location: participants.php');
    exit;
}


$message = '';
$error = '';


if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = $_POST['type'] ?? '';
    $nso = trim($_POST['nso'] ?? '');

    // Vérification que le pays est valide
    $normalizedNso = mb_strtolower($nso);
    $normalizedCountries = array_map(fn($c) => mb_strtolower($c), $countries);

    if (!in_array($normalizedNso, $normalizedCountries)) {
        $error = "The selected country is not valid.";
    } else {
        // Gestion de la photo uploadée
        $photoPath = $participant['photo'] ?? null;
        if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
            $data = $_POST['photoData'];
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);

            $uploadDir = 'uploads/photos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $photoName = uniqid('participant_') . '.png';
            $photoPath = $uploadDir . $photoName;

            file_put_contents($photoPath, $decoded);
        }

        if (!$error) {
            if (updateParticipant($pdo, $id, $nom, $prenom, $email, $type, $nso, $photoPath)) {
                $message = "Participant updated successfully!";
                $participant = getParticipantById($pdo, (int)$id);
            } else {
                $error = "Error updating participant.";
            }
        }
    }
}