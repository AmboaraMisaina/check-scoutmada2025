<?php
// login_debug.php - Version debug pour tester
session_start();
require_once 'functions/db.php';

// Debug: afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si déjà connecté, rediriger
if (isset($_SESSION['admin_id'])) {
    echo "DEBUG: Already connected, redirecting to checkin...<br>";
    header('Location: ../checkin.php');
    exit;
}

$error = '';
$debug = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $debug .= "DEBUG: Username : '" . htmlspecialchars($username) . "'<br>";
    $debug .= "DEBUG: Password : '" . htmlspecialchars($password) . "'<br>";

    if ($username && $password) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            $debug .= "DEBUG: Request executed <br>";
            
            if ($admin) {
                $debug .= "DEBUG: Admin found - ID: " . $admin['id'] . ", Username: " . $admin['username'] . "<br>";
                $debug .= "DEBUG: Hash : " . $admin['password'] . "<br>";

                if (password_verify($password, $admin['password'])) {
                    $debug .= "DEBUG: Password verified successfully!<br>";
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];

                    $debug .= "DEBUG: Session created, redirecting...<br>";
                    // Attendre 2 secondes pour voir le debug
                    echo $debug;
                    echo "<script>setTimeout(function(){ window.location.href = 'checkin.php'; }, 2000);</script>";
                    exit;
                } else {
                    $debug .= "DEBUG: Password verification failed<br>";

                    // Test manuel du hash
                    $testHash = password_hash($password, PASSWORD_DEFAULT);
                    $debug .= "DEBUG:  Test - New Hash : " . $testHash . "<br>";

                    // Test avec le hash connu
                    $knownHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                    $testResult = password_verify($password, $knownHash);
                    $debug .= "DEBUG: Test with known hash: " . ($testResult ? 'SUCCESS' : 'FAILURE') . "<br>";

                    $error = 'Invalid credentials';
                }
            } else {
                $debug .= "DEBUG: No admin found with this username<br>";
                $error = 'Invalid credentials';
            }
        } catch (PDOException $e) {
            $debug .= "DEBUG: PDO Error: " . $e->getMessage() . "<br>";
            $error = 'Database error';
        }
    } else {
        $debug .= "DEBUG: Missing fields<br>";
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - Checkin App</title>
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
            <strong>Debug Information:</strong><br>
            <?php echo $debug; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="admin" required>
            <!-- <small> Prérempli avec 'admin'</small> -->
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="text" id="password" name="password" value="admin123" required>
            <!-- <small>Prérempli avec 'admin123' (en texte visible pour debug)</small> -->
        </div>
        
        <button type="submit" class="btn"> Login (DEBUG)</button>
    </form>
    
    <hr>
    <p><strong> For testing :</strong></p>
    <ul>
        <li>Username: <code>admin</code></li>
        <li>Password: <code>admin123</code></li>
    </ul>

    <p><a href="login.php">Back to normal login</a></p>
</body>
</html>