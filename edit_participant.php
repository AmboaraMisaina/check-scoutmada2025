<?php
require_once 'functions/functions.php';
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
                    <option value="delegate" <?= $participant['type'] === 'delegate' ? 'selected' : '' ?>>Delegate</option>
                    <option value="observer" <?= $participant['type'] === 'observer' ? 'selected' : '' ?>>Dbserver</option>
                    <option value="organizing_comittee" <?= $participant['type'] === 'organizing_comittee' ? 'selected' : '' ?>>Organizing comittee</option>
                    <option value="wosm_team" <?= $participant['type'] === 'wosm_team' ? 'selected' : '' ?>>WOSM Team</option>
                    <option value="volunteer" <?= $participant['type'] === 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
                    <option value="staff" <?= $participant['type'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="partner" <?= $participant['type'] === 'partner' ? 'selected' : '' ?>>Partner</option>
                    <option value="guest" <?= $participant['type'] === 'guest' ? 'selected' : '' ?>>Guest</option>
                </select>
            </div>

            <button type="submit" class="btn" style="margin-top:1rem;">Mettre à jour</button>
            <a href="participants.php" class="btn btn-secondary" style="margin-top:1rem;">Retour</a>
        </form>
    </div>
</div>

<?php renderFooter(); ?>
