<?php
require_once 'functions.php';
checkAuthOrRedirect();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: participants.php');
    exit;
}

// Récupérer le participant
$participant = getParticipantById($pdo, (int)$id);
if (!$participant) {
    header('Location: participants.php');
    exit;
}

$message = '';
$error = '';

// Traitement du formulaire
if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = $_POST['type'] ?? '';

    if (updateParticipant($pdo, $id, $nom, $prenom, $email, $type)) {
        $message = "Participant mis à jour avec succès !";
        $participant = getParticipantById($pdo, (int)$id); // recharger
    } else {
        $error = "Erreur lors de la mise à jour du participant.";
    }
}

renderHeader('Modifier Participant');
?>

<div class="container">
    <div class="page-header">
        <h2>Modifier Participant</h2>
        <p>Mettez à jour les informations du participant</p>
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
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($participant['nom']) ?>"
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="prenom" style="display:block; margin-bottom:0.5rem;">Prénom</label>
                <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($participant['prenom']) ?>"
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="mail" style="display:block; margin-bottom:0.5rem;">Email</label>
                <input type="email" id="mail" name="mail" required value="<?= htmlspecialchars($participant['email']) ?>"
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="type" style="display:block; margin-bottom:0.5rem;">Type</label>
                <select id="type" name="type" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
                    <option value="">-- Sélectionner --</option>
                    <option value="Delegue" <?= $participant['type'] === 'Delegue' ? 'selected' : '' ?>>Délégué</option>
                    <option value="Observateur" <?= $participant['type'] === 'Observateur' ? 'selected' : '' ?>>Observateur</option>
                    <option value="Comité d'organisation" <?= $participant['type'] === 'Comité d\'organisation' ? 'selected' : '' ?>>Comité d'organisation</option>
                    <option value="WOSM Team" <?= $participant['type'] === 'WOSM Team' ? 'selected' : '' ?>>WOSM Team</option>
                </select>
            </div>

            <button type="submit" class="btn" style="margin-top:1rem;">Mettre à jour</button>
            <a href="participants.php" class="btn btn-secondary" style="margin-top:1rem;">Retour</a>
        </form>
    </div>
</div>

<?php renderFooter(); ?>
