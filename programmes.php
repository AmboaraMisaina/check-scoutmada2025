<?php
require_once 'functions.php';
checkAuthOrRedirect();

if (isset($_GET['delete'])) {
    deleteJourProgramme($pdo, intval($_GET['delete']));
    header("Location: programmes.php");
    exit;
}

$jours = getAllJoursProgrammes($pdo);
renderHeader('Programmes');
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
                        <tr style="border-bottom: 1px solid #e1e5e9;">
                            <td style="padding: 0.75rem;"><?= $j['id']; ?></td>
                            <td style="padding: 0.75rem;"><?= $j['date_jour']; ?></td>
                            <td style="padding: 0.75rem;">
                                <a href="view_jour.php?id=<?= $j['id']; ?>" class="btn btn-secondary">📋 Voir</a>
                                <a href="programmes.php?delete=<?= $j['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette journée ?')">🗑️ Supprimer</a>
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
