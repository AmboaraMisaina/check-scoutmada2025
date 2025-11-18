<?php
// fix_admin.php - Script pour corriger le mot de passe de l'admin et du checkin (hashage SHA-256)
require_once 'functions/db.php';

// Définir les mots de passe
$passwordAdmin = 'admin123';
$passwordCheckin = 'checkin123';

// Générer les hash SHA-256
$correctHashAdmin = hash('sha256', $passwordAdmin);
$correctHashCheckin = hash('sha256', $passwordCheckin);

echo "<h1>Correction du mot de passe admin</h1>";
echo "<p><strong>Mot de passe :</strong> admin123</p>";
echo "<p><strong>Nouveau hash :</strong> $correctHashAdmin</p>";

echo "<h1>Correction du mot de passe checkin</h1>";
echo "<p><strong>Mot de passe :</strong> checkin123</p>";
echo "<p><strong>Nouveau hash :</strong> $correctHashCheckin</p>";

try {
    // Mettre à jour le mot de passe dans la base
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $resultAdmin = $stmt->execute([$correctHashAdmin]);

    // Mettre à jour le mot de passe pour l'utilisateur checkin
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'checkin'");
    $resultCheckin = $stmt->execute([$correctHashCheckin]);

    if ($resultAdmin && $resultCheckin) {
        echo "<p style='color: green;'><strong>✅ Succès !</strong> Admin password has been updated.</p>";
        echo "<p style='color: green;'><strong>✅ Succès !</strong> Checkin password has been updated.</p>";
        // Vérifier que ça fonctionne
        $stmtAdmin = $pdo->prepare("SELECT password FROM admins WHERE username = 'admin'");
        $stmtAdmin->execute();
        $admin = $stmtAdmin->fetch();

        $stmtCheckin = $pdo->prepare("SELECT password FROM admins WHERE username = 'checkin'");
        $stmtCheckin->execute();
        $checkin = $stmtCheckin->fetch();

        if ($admin && $admin['password'] === $correctHashAdmin) {
            echo "<p style='color: green;'><strong>✅ Vérification :</strong> Admin password is now working!</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Erreur :</strong> Verification failed.</p>";
        }

        if ($checkin && $checkin['password'] === $correctHashCheckin) {
            echo "<p style='color: green;'><strong>✅ Vérification :</strong> Checkin password is now working!</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Erreur :</strong> Verification failed for 'checkin'.</p>";
        }

    } else {
        echo "<p style='color: red;'><strong>❌ Erreur :</strong> Cannot update passwords.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Erreur PDO :</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong> You can now log in with :</strong></p>";
echo "<ul>";
echo "<li><strong>Username :</strong> admin</li>";
echo "<li><strong>Password :</strong> admin123</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>You can now log in with :</strong></p>";
echo "<hr>";
echo "<ul>";
echo "<li><strong>Username :</strong> checkin</li>";
echo "<li><strong>Password :</strong> checkin123</li>";
echo "</ul>";
echo "<p><a href='login.php'>🔐 Go to login page</a></p>";
echo "<p><a href='login_debug.php'>🔧 Test with login debug</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1 {
    color: #333;
    text-align: center;
}
p {
    background: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}
ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
}
a {
    display: inline-block;
    background: #007bff;
    color: white;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 5px;
}
a:hover {
    background: #0056b3;
}
</style>