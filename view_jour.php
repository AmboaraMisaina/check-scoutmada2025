<?php
require_once 'functions.php';
checkAuthOrRedirect();

$jour_id = intval($_GET['id'] ?? 0);
if (!$jour_id) { header('Location: programmes.php'); exit; }

$jour = getJourById($pdo, $jour_id);
$evenements = getEvenementsByJour($pdo, $jour_id);

// Formater la date avec strftime
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
$dateFormatee = strftime('%A %e %B %Y', strtotime($jour['date_jour']));

renderHeader("Programme du $dateFormatee");
?>

<div class="container">
    <div class="page-header">
        <h2>Programme du <?= ucfirst($dateFormatee) ?></h2>
    </div>

    <div class="card">
        <a href="add_evenement.php?jour_id=<?= $jour_id ?>" class="btn">➕ Ajouter un événement</a>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f1f1f1;">
                    <th style="padding:0.75rem;">ID</th>
                    <th style="padding:0.75rem;">Titre</th>
                    <th style="padding:0.75rem;">Description</th>
                    <th style="padding:0.75rem;">Horaire</th>
                    <th style="padding:0.75rem;">Ouvert à</th>
                    <th style="padding:0.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($evenements): ?>
                    <?php foreach ($evenements as $e): ?>
                        <tr style="border-bottom:1px solid #e1e5e9;">
                            <td style="padding:0.75rem;"><?= $e['id'] ?></td>
                            <td style="padding:0.75rem;"><?= htmlspecialchars($e['titre']) ?></td>
                            <td style="padding:0.75rem;"><?= htmlspecialchars($e['description']) ?></td>
                            <td style="padding:0.75rem;"><?= $e['horaire_debut'] ?> - <?= $e['horaire_fin'] ?></td>
                            <td style="padding:0.75rem;"><?= implode(', ', array_map('ucfirst', explode(',', $e['ouvert_a']))) ?></td>
                            <td style="padding:0.75rem;">
                                <a href="edit_evenement.php?id=<?= $e['id'] ?>" class="btn btn-secondary">✏️</a>
                                <a href="delete_evenement.php?id=<?= $e['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cet événement ?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:1rem;text-align:center;">Aucun événement trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <a href="programmes.php" class="btn btn-secondary">⬅️ Retour aux journées</a>
    </div>
</div>

<?php renderFooter(); ?>
