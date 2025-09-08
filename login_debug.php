<?php
// login_debug.php - Version debug pour tester
session_start();
require_once '../functions/db.php';

// Debug: afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si déjà connecté, rediriger
if (isset($_SESSION['admin_id'])) {
    echo "DEBUG: Déjà connecté, redirection vers dashboard...<br>";
    header('Location: ../dashboard.php');
    exit;
}

$error = '';
$debug = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $debug .= "DEBUG: Username reçu: '" . htmlspecialchars($username) . "'<br>";
    $debug .= "DEBUG: Password reçu: '" . htmlspecialchars($password) . "'<br>";
    
    if ($username && $password) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            $debug .= "DEBUG: Requête exécutée<br>";
            
            if ($admin) {
                $debug .= "DEBUG: Admin trouvé - ID: " . $admin['id'] . ", Username: " . $admin['username'] . "<br>";
                $debug .= "DEBUG: Hash en base: " . $admin['password'] . "<br>";
                
                if (password_verify($password, $admin['password'])) {
                    $debug .= "DEBUG: Mot de passe vérifié avec succès !<br>";
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    $debug .= "DEBUG: Session créée, redirection...<br>";
                    // Attendre 2 secondes pour voir le debug
                    echo $debug;
                    echo "<script>setTimeout(function(){ window.location.href = 'dashboard.php'; }, 2000);</script>";
                    exit;
                } else {
                    $debug .= "DEBUG: Échec de la vérification du mot de passe<br>";
                    
                    // Test manuel du hash
                    $testHash = password_hash($password, PASSWORD_DEFAULT);
                    $debug .= "DEBUG: Test - nouveau hash pour ce mot de passe: " . $testHash . "<br>";
                    
                    // Test avec le hash connu
                    $knownHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                    $testResult = password_verify($password, $knownHash);
                    $debug .= "DEBUG: Test avec hash connu: " . ($testResult ? 'SUCCÈS' : 'ÉCHEC') . "<br>";
                    
                    $error = 'Identifiants incorrects';
                }
            } else {
                $debug .= "DEBUG: Aucun admin trouvé avec ce username<br>";
                $error = 'Identifiants incorrects';
            }
        } catch (PDOException $e) {
            $debug .= "DEBUG: Erreur PDO: " . $e->getMessage() . "<br>";
            $error = 'Erreur de base de données';
        }
    } else {
        $debug .= "DEBUG: Champs manquants<br>";
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - Système de Checking</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .debug { background: #f0f0f0; padding: 15px; margin: 15px 0; border: 1px solid #ccc; }
        .error { background: #fee; color: #c33; padding: 10px; margin: 15px 0; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Login Debug</h1>
    
    <?php if ($debug): ?>
        <div class="debug">
            <strong>Informations de debug :</strong><br>
            <?php echo $debug; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="admin" required>
            <small>Prérempli avec 'admin'</small>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="text" id="password" name="password" value="admin123" required>
            <small>Prérempli avec 'admin123' (en texte visible pour debug)</small>
        </div>
        
        <button type="submit" class="btn">Se connecter (DEBUG)</button>
    </form>
    
    <hr>
    <p><strong>Pour tester :</strong></p>
    <ul>
        <li>Username: <code>admin</code></li>
        <li>Password: <code>admin123</code></li>
    </ul>
    
    <p><a href="login.php">Retour au login normal</a></p>
</body>
</html>