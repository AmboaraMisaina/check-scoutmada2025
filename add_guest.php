<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();

$message = '';
$error = '';

// Traitement du formulaire
if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = 'guest';

    $result = addParticipant($pdo, $nom, $prenom, $email, $type, null);

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width:500px; margin:2rem auto;">
    <div class="page-header" style="margin-bottom:2rem;">
        <h2 style="margin-bottom:0.5rem;">Add guest</h2>
        <p style="color:#555;">Fill guest information</p>
    </div>

    <div class="card" style="padding:2rem; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
        <?php if ($message): ?>
            <div class="alert alert-success" style="background:#eafaf1; color:#27ae60; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" style="background:#fdeaea; color:#e74c3c; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="prenom" style="display:block; margin-bottom:0.4rem; font-weight:500;">First name</label>
                <input type="text" id="prenom" name="prenom" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nom" style="display:block; margin-bottom:0.4rem; font-weight:500;">Last name</label>
                <input type="text" id="nom" name="nom" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="mail" style="display:block; margin-bottom:0.4rem; font-weight:500;">Email</label>
                <input type="email" id="mail" name="mail" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <button type="submit" class="btn" style="width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem;">
                Register
            </button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>