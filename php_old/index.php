<?php
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers le checkin
if (isset($_SESSION['admin_id'])) {
    header('Location: checkin.php');
    exit;
}

// Sinon, rediriger vers la page de login
header('Location: login.php');
exit;
