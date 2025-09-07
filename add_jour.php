<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();

$message = '';
$error = '';

if ($_POST) {
    $titre = trim($_POST['titre'] ?? '');
    $date = $_POST['date'] ?? '';

    if (!$titre || !$date) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO jours_programmes (titre, date_jour) VALUES (?, ?)");
        if ($stmt->execute([$titre, $date])) {
            $message = "Journée ajoutée avec succès !";
        } else {
            $error = "Une erreur est survenue lors de l'ajout.";
        }
    }
}

renderHeader('Ajouter une Journée');
?>
<div class="container">
    <div class="page-header">
        <h2>Ajouter une Journée</h2>
        <p>Remplissez les informations de la journée</p>
    </div>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" required>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>

            <button type="submit" class="btn">Ajouter Journée</button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>