<?php
// fix_admin.php - Script pour corriger le mot de passe de l'admin
require_once '../db.php';

// Générer le bon hash pour 'admin123'
$password = 'admin123';
$correctHash = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Correction du mot de passe admin</h1>";
echo "<p><strong>Mot de passe :</strong> admin123</p>";
echo "<p><strong>Nouveau hash :</strong> $correctHash</p>";

try {
    // Mettre à jour le mot de passe dans la base
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $result = $stmt->execute([$correctHash]);
    
    if ($result) {
        echo "<p style='color: green;'><strong>✅ Succès !</strong> Le mot de passe a été mis à jour.</p>";
        
        // Vérifier que ça fonctionne
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            echo "<p style='color: green;'><strong>✅ Vérification :</strong> Le mot de passe fonctionne maintenant !</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Erreur :</strong> La vérification a échoué.</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ Erreur :</strong> Impossible de mettre à jour le mot de passe.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Erreur PDO :</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Tu peux maintenant te connecter avec :</strong></p>";
echo "<ul>";
echo "<li><strong>Username :</strong> admin</li>";
echo "<li><strong>Password :</strong> admin123</li>";
echo "</ul>";

echo "<p><a href='login.php'>🔐 Aller à la page de connexion</a></p>";
echo "<p><a href='login_debug.php'>🔧 Tester avec login debug</a></p>";
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