<?php
require_once 'functions/functions.php';
// checkAuthOrRedirect();

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

<?php include 'includes/footer.php'; ?>