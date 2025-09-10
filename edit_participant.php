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

    var_dump(extension_loaded('mbstring'));
    // Vérification que le pays est valide
    // $normalizedNso = mb_strtolower($nso);

    
}