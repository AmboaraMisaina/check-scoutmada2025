<?php
require_once 'functions.php';
checkAuthOrRedirect();

$filter_name = trim($_GET['filter_name'] ?? '');
$filter_type = $_GET['filter_type'] ?? '';
$filter_paid = $_GET['filter_paid'] ?? '';
$to_print = $_GET['to_print'] ?? 0;


$participants = getAllParticipantsWithFilter($pdo, $filter_name, $to_print, $filter_type, $filter_paid, 1000000, 0);

exportParticipantsToExcel($pdo, $participants, 'participants_export.csv');
header('Location: ../participants.php');