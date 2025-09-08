<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();
if ($_SESSION['role'] !== 'admin') {
    include 'includes/header.php';
    ?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Accès interdit</h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;">Vous n’avez pas les droits nécessaires pour accéder à cette page.</p>
            <a href="checkin.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Retour
            </a>
        </div>
    </div>
    <?php
    renderFooter();
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if (!$username || !$password || !in_array($role, ['admin', 'checkin'])) {
        $message = '<div style="color:red;">Veuillez remplir tous les champs correctement.</div>';
    } else {
        if (createAdmin($pdo, $username, $password, $role)) {
            $message = '<div style="color:green;">Nouvel admin ajouté avec succès !</div>';
        } else {
            $message = '<div style="color:red;">Erreur lors de l\'ajout de l\'admin.</div>';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width:400px;margin:2rem auto;">
    <h2>Ajouter un administrateur</h2>
    <?= $message ?>
    <form method="post" style="display:flex;flex-direction:column;gap:1rem;">
        <label>
            Nom d'utilisateur :
            <input type="text" name="username" required autocomplete="off" />
        </label>
        <label>
            Mot de passe :
            <input type="password" name="password" required autocomplete="new-password" />
        </label>
        <label>
            Rôle :
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="checkin">Checkin</option>
            </select>
        </label>
        <button type="submit" style="padding:0.7rem 1.2rem;background:#38ef7d;color:white;border:none;border-radius:8px;font-weight:bold;">Ajouter</button>
    </form>
</div>
<?php renderFooter(); ?>