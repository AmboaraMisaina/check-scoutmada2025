<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: programmes.php');
    exit;
}

// Récupération de l'événement
$evenement = getEvenementById($pdo, $id);
if (!$evenement) {
    header('Location: programmes.php');
    exit;
}

$message = '';
$error = '';

if ($_POST) {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $horaire_debut = $_POST['horaire_debut'] ?? '';
    $horaire_fin = $_POST['horaire_fin'] ?? '';
    $ouvert_a = $_POST['ouvert_a'] ?? [];
    $date_evenement = $_POST['date'] ?? '';
    $participation_unique = isset($_POST['unique_event']) ? 1 : 0;

     // Validation de base

    if (!$titre || !$horaire_debut || !$horaire_fin || !$date_evenement) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {

        $result = updateEvenement($pdo, $date_evenement, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a, $id, $participation_unique);
        if ($result) {
            $message = "Événement mis à jour avec succès.";
            $evenement = getEvenementById($pdo, $id);
        } else {
            $error = "Une erreur est survenue lors de la mise à jour.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Modifier l'événement</h2>
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
                <label for="titre">Titre de l'événement</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($evenement['titre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($evenement['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($evenement['date_evenement']) ?>" required>
            </div>

            <div class="form-group">
                <label for="horaire_debut">Horaire de début</label>
                <input type="time" id="horaire_debut" name="horaire_debut" value="<?= $evenement['horaire_debut'] ?>" required>
            </div>

            <div class="form-group">
                <label for="horaire_fin">Horaire de fin</label>
                <input type="time" id="horaire_fin" name="horaire_fin" value="<?= $evenement['horaire_fin'] ?>" required>
            </div>

            <div class="form-group">
                <label>Ouvert à</label><br>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="delegate" <?= in_array('delegate', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Delegate
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="observer" <?= in_array('observer', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Observer
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="organizing_committee" <?= in_array('organizing_committee', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Organizing Committee
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="wosm_team" <?= in_array('wosm_team', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> WOSM Team
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="volunteer" <?= in_array('volunteer', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Volunteer
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="staff" <?= in_array('staff', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Staff
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="partner" <?= in_array('partner', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Partner
                </label>
                <label style="margin-left: 1rem;">
                    <input type="checkbox" name="ouvert_a[]" value="guest" <?= in_array('guest', explode(',', $evenement['ouvert_a'])) ? 'checked' : '' ?>> Guest
                </label>
            </div>


             <div class="form-group">
                <label for="unique_event">
                    <input type="checkbox" id="unique_event" name="unique_event" value="1" <?= $evenement['unique_event'] ? 'checked' : '' ?>>
                    Événement à participation unique
                </label>
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
    </div>
</div>

<style>
    .container {
        max-width: 700px;
        margin: 2rem auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .page-header h2 {
        margin-bottom: 0.2rem;
    }

    .card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.3rem;
        font-weight: 500;
    }

    .form-group input[type="text"],
    .form-group input[type="time"],
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 0.95rem;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .btn {
        background: #667eea;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn:hover {
        background: #556cd6;
    }

    .alert {
        padding: 0.5rem 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<?php renderFooter(); ?>