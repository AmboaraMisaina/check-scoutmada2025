<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'functions.php';
checkAuthOrRedirect();

// Définir le timezone à Madagascar
date_default_timezone_set('Indian/Antananarivo');

$aujourdhui = $_GET['date'] ?? date('Y-m-d');
$filter = $_GET['filter'] ?? 'current'; // 'current' ou 'all'

$evenements = getEvenementsDuJour($pdo, $aujourdhui);

renderHeader('Checking');
?>

<div class="container">
    <div class="page-header">
        <h2>Checking des événements</h2>
        <p>Événements programmés pour : <?= $aujourdhui ?></p>
    </div>

    <!-- Filtre date -->
    <form method="get" style="margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
        <input type="date" name="date" value="<?= htmlspecialchars($aujourdhui) ?>" onchange="this.form.submit()"
            style="padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
    </form>

    <!-- Boutons de filtre -->
    <div style="margin-bottom: 2rem; display:flex; gap:1rem;">
        <a href="checking.php?date=<?= $aujourdhui ?>&filter=current"
            class="btn <?= ($filter === 'current') ? 'btn-active' : 'btn-secondary' ?>"
            style="flex:1; text-align:center;">Juste actuellement</a>
        <a href="checking.php?date=<?= $aujourdhui ?>&filter=all"
            class="btn <?= ($filter === 'all') ? 'btn-active' : 'btn-secondary' ?>"
            style="flex:1; text-align:center;">Voir tous les programmes</a>
    </div>

    <div style="display:flex; flex-direction:column; gap:1rem;">
        <?php
        $evenementAffiche = false;
        foreach ($evenements as $e):
            // Calcul enCours
            $timestampDebut = strtotime($aujourdhui . ' ' . $e['horaire_debut']);
            $timestampFin = strtotime($aujourdhui . ' ' . $e['horaire_fin']);
            $timestampNow = time();
            $e['enCours'] = ($timestampNow >= $timestampDebut && $timestampNow <= $timestampFin);

            if ($filter === 'current' && !$e['enCours']) continue;

            $evenementAffiche = true;
            $urlCheck = "check_participants.php?evenement_id=" . $e['id'];
        ?>
            <a href="<?= $urlCheck ?>"
                class="card"
                style="
       padding:1rem 1.5rem; 
       border-radius:10px; 
       box-shadow:0 2px 8px rgba(0,0,0,0.1); 
       display:flex; 
       justify-content:space-between; 
       align-items:center;      
       text-decoration:none; 
       color:#333; 
       transition: transform 0.15s ease, box-shadow 0.15s ease;
       cursor: pointer;
   "
                onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                <div>
                    <strong><?= $e['horaire_debut'] ?> - <?= $e['horaire_fin'] ?>:</strong> <?= htmlspecialchars($e['titre']) ?>
                </div>
                <?php if ($e['enCours']): ?>
                    <span style="background:#38ef7d; color:white; padding:0.3rem 0.7rem; border-radius:5px; font-weight:bold;">EN COURS</span>
                <?php endif; ?>
            </a>

        <?php endforeach; ?>
    </div>

    <?php if (!$evenementAffiche): ?>
        <div class="card" style="padding:1rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-top:1rem;">
            <p style="margin:0; text-align:center;">Aucun événement à afficher pour ce filtre.</p>
        </div>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>