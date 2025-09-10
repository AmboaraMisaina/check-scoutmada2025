<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();



$message = '';
$error = '';

if ($_POST) {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $horaire_debut = $_POST['horaire_debut'] ?? '';
    $horaire_fin = $_POST['horaire_fin'] ?? '';
    $ouvert_a = $_POST['ouvert_a'] ?? []; // tableau CSV
    $date_evenement = $_POST['date_evenement'] ?? '';

    // Récupère la valeur de la case à cocher "Événement à participation unique"
    $unique_event = isset($_POST['unique_event']) ? 1 : 0;

    if (!$titre || !$horaire_debut || !$horaire_fin) {
        $error = "Please fill in all required fields.";
    } else {
        // Transformer le tableau en CSV
        $ouvert_a_csv = implode(',', $ouvert_a);

        // Ajouter l'événement dans la table, en passant nb_participation
        $stmt = $pdo->prepare("INSERT INTO evenements (date_evenement, titre, description, horaire_debut, horaire_fin, ouvert_a, nb_participation) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$date_evenement, $titre, $description, $horaire_debut, $horaire_fin, $ouvert_a_csv, $unique_event])) {
            $message =  "Event added successfully.";
        } else {
            $error = "An error occurred while adding the event.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="titre">Event Title</label>
                <input type="text" id="titre" name="titre" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="description">Date</label>
                <input id="date" name="date_evenement" type="date" required>
            </div>

            <div class="form-group">
                <label for="horaire_debut">Start Time</label>
                <input type="time" id="horaire_debut" name="horaire_debut" required>
            </div>

            <div class="form-group">
                <label for="horaire_fin">End Time</label>
                <input type="time" id="horaire_fin" name="horaire_fin" required>
            </div>

            <div class="form-group">
                <label>Open to</label>
                    <ul style="list-style-type: disc; margin-left: 2rem; font-family: sans-serif;">
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="observer"> Observer
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="delegate"> Delegate
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="organizing committee"> Organizing Committee
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="wosm team"> WOSM Team
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="volunteer"> Volunteer
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="staff"> Staff
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="partner"> Partner
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="checkbox" name="ouvert_a[]" value="guest"> Guest
                            </label>
                        </li>
                    </ul>

            </div><br>

            <div class="form-group">
                <label for="unique_event">
                    <input type="checkbox" id="unique_event" name="unique_event" value="1">
                    Unique Participation Event
                </label>
            </div>
            <button type="submit" class="btn">Add Event</button>
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

    .page-header p {
        margin-bottom: 1rem;
        color: #555;
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