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
            <a href="dashboard.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Retour au tableau de bord
            </a>
        </div>
    </div>
    <?php
    renderFooter();
    exit;
}
if (isset($_GET['delete'])) {
    deleteJourProgramme($pdo, intval($_GET['delete']));
    header("Location: programmes.php");
    exit;
}

$jours = getAllJoursProgrammes($pdo);
include 'includes/header.php';

?>

<div class="container">
    <div class="page-header">
        <h2>Programmes par jour</h2>
        <p>Liste de toutes les journées programmées</p>
    </div>

    <div class="card">
        <a href="add_jour.php" class="btn">➕ Ajouter une journée</a>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f1f1f1;">
                    <th style="padding: 0.75rem;">ID</th>
                    <th style="padding: 0.75rem;">Date</th>
                    <th style="padding: 0.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jours): ?>
                    <?php foreach ($jours as $j): ?>
                        <?php
                        // Convertir la date SQL (YYYY-MM-DD) en timestamp
                        $timestamp = strtotime($j['date_jour']);

                        // Définir la locale en français
                        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

                        // Formater en français
                        $dateFormatee = strftime("%A %e %B %Y", $timestamp);
                        ?>
                        <tr style="border-bottom: 1px solid #e1e5e9;">
                            <td style="padding: 0.75rem;"><?= $j['id']; ?></td>
                            <td style="padding: 0.75rem;"><?= ucfirst($dateFormatee); ?></td>
                            <td style="padding: 0.75rem;">
                                <a href="view_jour.php?id=<?= $j['id']; ?>" class="btn btn-secondary">🔍</a>
                                <a href="programmes.php?delete=<?= $j['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette journée ?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding: 1rem; text-align: center;">Aucune journée trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>
