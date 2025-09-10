<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();

if ($_SESSION['role'] !== 'admin') {
    include 'includes/header.php';

?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Forbidden</h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;">You do not have the necessary rights to access this page.</p>
            <a href="checkin.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Back
            </a>
        </div>
    </div>
<?php
    renderFooter();
    exit;
}


// Vérifier si une suppression est demandée
if (isset($_GET['delete'])) {
    deleteEvenement($pdo, intval($_GET['delete']));
    header("Location: programmes.php");
    exit;
}


// Récupérer tous les événements
$evenements = [];
$stmt = $pdo->query("SELECT * FROM evenements ORDER BY id DESC");
if ($stmt) {
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>List of Events</h2>
        <p>Here are all the scheduled events</p>
    </div>

    <div class="card">
        <a href="add_evenement.php" class="btn">➕ Add Event</a>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f1f1f1;">
                    <th style="padding: 0.75rem;">Title</th>
                    <th style="padding: 0.75rem;">Date</th>
                    <th style="padding: 0.75rem;">Start</th>
                    <th style="padding: 0.75rem;">End</th>
                    <th style="padding: 0.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($evenements): ?>
                    <?php foreach ($evenements as $e): ?>
                        <tr style="border-bottom: 1px solid #e1e5e9;">
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($e['titre']); ?></td>
                            <td style="padding: 0.75rem;">
                                <?php
                                // Affiche la date si disponible
                                if (!empty($e['date_evenement'])) {
                                    $timestamp = strtotime($e['date_evenement']);
                                    setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
                                    echo ucfirst(strftime("%A %e %B %Y", $timestamp));
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($e['horaire_debut'] ?? '-'); ?></td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($e['horaire_fin'] ?? '-'); ?></td>
                            <td style="padding: 0.75rem;">
                                <a href="edit_evenement.php?id=<?= $e['id']; ?>" class="btn btn-secondary">🔍</a>
                                <a href="programmes.php?delete=<?= $e['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cet événement ?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 1rem; text-align: center;">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>