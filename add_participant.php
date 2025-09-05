<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'functions.php';
checkAuth();

$message = '';
$error = '';

// Traitement du formulaire
if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = $_POST['type'] ?? '';

    $result = addParticipant($pdo, $nom, $prenom, $email, $type); // Fonction centralisée

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

renderHeader('Ajouter Participant');
?>

<div class="container">
    <div class="page-header">
        <h2>Ajouter un participant</h2>
        <p>Remplissez les informations du participant</p>
    </div>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="nom" style="display:block; margin-bottom:0.5rem;">Nom</label>
                <input type="text" id="nom" name="nom" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="prenom" style="display:block; margin-bottom:0.5rem;">Prénom</label>
                <input type="text" id="prenom" name="prenom" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="mail" style="display:block; margin-bottom:0.5rem;">Email</label>
                <input type="email" id="mail" name="mail" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="type" style="display:block; margin-bottom:0.5rem;">Type</label>
                <select id="type" name="type" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
                    <option value="">-- Sélectionner --</option>
                    <option value="Delegue">Délégué</option>
                    <option value="Observateur">Observateur</option>
                    <option value="Comite d'organisation">Comité d'organisation</option>
                    <option value="WOSM team">WOSM team</option>
                </select>
            </div>

            <button type="submit" class="btn" style="margin-top:1rem;">Ajouter Participant</button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>