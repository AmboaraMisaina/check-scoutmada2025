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

// Supprimer si paramètre delete est présent
if (isset($_GET['delete'])) {
    deleteParticipant($pdo, intval($_GET['delete']));
    header("Location: participants.php");
    exit;
}

// Récupérer tous les participants
$participants = getAllParticipants($pdo);

include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Liste des participants</h2>
        <p>Visualisez tous les participants enregistrés</p>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <a href="add_participant.php" class="btn">➕ Ajouter un participant</a>
        <a href="import_participants.php" class="btn btn-primary">📥 Importer des participants</a>
        <a href="functions/download_qr.php?download_all=1" class="btn btn-success">📅 Télécharger tous les QR Codes</a>
        <a href="dashboard.php" class="btn btn-secondary">📊 Retour</a>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f1f1f1;">
                    <th style="padding: 0.75rem;">ID</th>
                    <th style="padding: 0.75rem;">Nom</th>
                    <th style="padding: 0.75rem;">Prénom</th>
                    <th style="padding: 0.75rem;">Email</th>
                    <th style="padding: 0.75rem;">Type</th>
                    <th style="padding: 0.75rem;">QR Code</th>
                    <th style="padding: 0.75rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($participants): ?>
                    <?php foreach ($participants as $p): ?>
                        <tr style="border-bottom: 1px solid #e1e5e9;">
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($p['id']); ?></td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($p['nom']); ?></td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($p['prenom']); ?></td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($p['email']); ?></td>
                            <td style="padding: 0.75rem;"><?= htmlspecialchars($p['type']); ?></td>
                            <td style="padding: 0.75rem;">
                                <?php if ($p['qr_code']): ?>
                                    <button onclick="showQrModal('<?= htmlspecialchars($p['id']) ?>', '<?= htmlspecialchars($p['qr_code']) ?>')" style="padding:0.3rem 0.7rem; border-radius:5px; background:#38ef7d; color:white; border:none; font-weight:bold; cursor:pointer;">
                                        Voir QR
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <a href="edit_participant.php?id=<?= $p['id']; ?>" class="btn btn-secondary">✏️</a>
                                <a href="participants.php?delete=<?= $p['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce participant ?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="padding: 1rem; text-align: center;">Aucun participant trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fenêtre modale QR Code -->
<div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); min-width:300px; text-align:center; position:relative;">
        <span style="position:absolute; top:10px; right:15px; font-size:1.5rem; cursor:pointer;" onclick="closeQrModal()">&times;</span>
        <h3 style="margin-bottom:1rem;">QR Code du participant</h3>
        <img id="qrImg" src="" alt="QR Code" style="margin-bottom:1rem; max-width:200px;">
        <br>
        <a id="qrDownload" href="#" download style="padding:0.5rem 1rem; background:#38ef7d; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
            Télécharger le QR Code
        </a>
    </div>
</div>

<script>
function showQrModal(id, qrText) {
    var qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(qrText) + "&size=200x200";
    document.getElementById('qrImg').src = qrUrl;
    document.getElementById('qrDownload').href = "download_qr.php?id=" + id;
    document.getElementById('qrDownload').removeAttribute('download'); // Le script force le téléchargement
    document.getElementById('qrModal').style.display = "flex";
}
function closeQrModal() {
    document.getElementById('qrModal').style.display = "none";
}
</script>

<?php renderFooter(); ?>