<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'functions.php'; // On inclut les fonctions centralisées
checkAuth();

// Récupérer les statistiques via la fonction
$stats = getStats($pdo);
$totalParticipants = $stats['total'];
$totalDelegues = $stats['delegues'];
$totalObservateurs = $stats['observateurs'];

renderHeader('Dashboard');
?>

<div class="container">
    <div class="page-header">
        <h2>Chek-in</h2>
        <p>Vue d'ensemble du système de checking</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?= $totalParticipants ?></h3>
            <p style="font-size: 1.2rem; opacity: 0.9;">Total Participants</p>
        </div>

        <div class="card" style="text-align: center; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?= $totalDelegues ?></h3>
            <p style="font-size: 1.2rem; opacity: 0.9;">Délégués</p>
        </div>

        <div class="card" style="text-align: center; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: white;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?= $totalObservateurs ?></h3>
            <p style="font-size: 1.2rem; opacity: 0.9;">Observateurs</p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem;">Actions rapides</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <a href="add_participant.php" class="btn">➕ Ajouter un participant</a>
            <a href="participants.php" class="btn btn-secondary">👥 Voir tous les participants</a>
            <a href="programmes.php" class="btn btn-success">📅 Gérer les programmes</a>
        </div>
    </div>
</div>

<?php renderFooter(); ?>