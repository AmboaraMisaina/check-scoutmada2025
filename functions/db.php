<?php
    // $host = "localhost";   
    // $dbname = "checking";  
    // $username = "root";    
    // $password = "";      

$host = "178.159.5.244";   
$dbname = "mahaymg1_checkscoutmada2025";  
$username = "mahaymg1_checkscoutmada2025";    
$password = "yfJ4CtmnnexBLYCuLt4Y";     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
