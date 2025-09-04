<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'functions.php'; // On inclut les fonctions centralisées
checkAuth();

// Récupérer les statistiques via la fonction
date_default_timezone_set('Indian/Antananarivo');

$aujourdhui = $_GET['date'] ?? date('Y-m-d');
$filter = $_GET['filter'] ?? 'current'; // 'current' ou 'all'

$evenements = getEvenementsDuJour($pdo, $aujourdhui);
renderHeader('Dashboard');
?>

<div class="container">
    <div class="page-header">
        <h2>Evenement en cours</h2>
    </div>


    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
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
                height: 160px;       /* Hauteur fixe */
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                color: white;
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                text-decoration: none;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
                cursor: pointer;
            "
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';"
            >
                <h3 style="font-size: 1.6rem; margin-bottom: 0.5rem;">
                    <?= htmlspecialchars($e['titre']) ?>
                </h3>
                <p style="font-size: 1.1rem; opacity: 0.9;">
                    <?= $e['horaire_debut'] ?> - <?= $e['horaire_fin'] ?>
                </p>
                <?php if ($e['enCours']): ?>
                    <span style="background:#fff; color:#11998e; padding:0.3rem 0.7rem; border-radius:5px; font-weight:bold;">
                        EN COURS
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>



    <?php if (!$evenementAffiche): ?>
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding:1rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); margin-top:1rem;">
            <p style="margin:0; text-align:center;">Aucun événement à afficher pour ce filtre.</p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(3, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem;">Actions rapides</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="participants.php" class="btn btn-secondary">👥 Voir tous les participants</a>
            <a href="programmes.php" class="btn btn-info">📅 Voir tous les programmes</a>
            <a href="download_qr.php?download_all=1" class="btn btn-success">📅 Télécharger tous les QR Codes</a>
            <a href="import_participants.php" class="btn btn-primary">📥 Importer des participants</a>
        </div>
    </div>
</div>

<?php renderFooter(); ?>