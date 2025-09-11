<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();

// Vérifier les droits
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registration') {
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

// Supprimer un participant si demandé
if (isset($_GET['delete'])) {
    deleteParticipant($pdo, intval($_GET['delete']));
    header("Location: participants.php");
    exit;
}

// Récupérer les filtres
$filter_name = trim($_GET['filter_name'] ?? '');
$filter_printed = $_GET['filter_printed'] ?? '';

// Pagination
$perPage = 10;
$pages = max(1, intval($_GET['page'] ?? 1));
$offset = ($pages - 1) * $perPage;

$participants = getAllParticipantsWithFilter($pdo, $filter_name, $filter_printed, $perPage, $offset);
$totalParticipants = getTotalParticipantsWithFilter($pdo, $filter_name, $filter_printed);
$totalPages = ceil($totalParticipants / $perPage);

include 'includes/header.php';
?>

<div class="container">

    <!-- Header page -->
    <div class="page-header" style="margin-bottom:1.5rem;">
        <h2>Participants</h2>
        <p>Manage all registered participants</p>
    </div>

    <!-- Actions principales -->
    <div class="card" style="display:flex; flex-wrap:wrap; gap:0.5rem; padding:1rem; margin-bottom:1rem;">
        <a href="add_participant.php" class="btn">➕ Add Participant</a>
        <a href="add_guest.php" class="btn">➕ Add Guest</a>
        <a href="import_participants.php" class="btn btn-primary">📥 Import Participants</a>
    </div>

    <!-- Filtre -->
    <div class="card" style="padding:1rem; margin-bottom:1rem;">
        <form method="GET" action="participants.php" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <input type="text" name="filter_name" placeholder="Search by name" value="<?= htmlspecialchars($filter_name) ?>" style="padding:0.5rem; border-radius:5px; border:1px solid #ccc; flex:1;">
            <select name="filter_printed" style="padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
                <option value="">All</option>
                <option value="1" <?= ($filter_printed === '1') ? 'selected' : '' ?>>Printed</option>
                <option value="0" <?= ($filter_printed === '0') ? 'selected' : '' ?>>Not Printed</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="participants.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Table des participants -->
    <form method="post" action="functions/print_badge.php" id="printForm">
        <?php
        if ($_SESSION['role'] == 'admin' ) {
            ?>
            <div style="display:flex; justify-content:flex-end; margin-bottom:0.5rem;">
                <button type="submit" class="btn btn-success">🖨️ Print Selected Badges</button>
            </div>
        <?php
        } ?>

        <div class="card" style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:700px;">
                <thead>
                    <tr style="background:#f1f1f1;">
                        <th style="padding:0.75rem;"><input type="checkbox" id="checkAll" onclick="toggleAll(this)"></th>
                        <th style="padding:0.75rem;">Name</th>
                        <!-- <th style="padding:0.75rem;">Email</th> -->
                        <!-- <th style="padding:0.75rem;">Country</th> -->
                        <th style="padding:0.75rem;">Category</th>
                        <th style="padding:0.75rem;">Printed</th>
                        <?php if ($_SESSION['role'] == 'admin') { ?>
                        <th style="padding:0.75rem;">Actions</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants): ?>
                        <?php foreach ($participants as $p): ?>
                            <tr style="border-bottom:1px solid #e1e5e9;">
                                <td style="text-align:center;"><input type="checkbox" class="print-checkbox" name="print_ids[]" value="<?= $p['id'] ?>" <?= !empty($p['isPrinted']) ? 'disabled' : '' ?>></td>
                                <td><?= htmlspecialchars($p['nom']); ?></td>
                                <!-- <td><?= htmlspecialchars($p['email']); ?></td> -->
                                <!-- <td><?= htmlspecialchars($p['pays']); ?></td> -->
                                <td><?= htmlspecialchars($p['type']); ?></td>
                                <td style="text-align:center;"><?= !empty($p['isPrinted']) ? '<span style="color:green; font-weight:bold;">✔</span>' : '<span style="color:#aaa;">✗</span>' ?></td>
                                <?php if ($_SESSION['role'] == 'admin') { ?>
                                <td>
                                    <a href="edit_participant.php?id=<?= $p['id']; ?>" class="btn btn-secondary">✏️</a>
                                    <a href="participants.php?delete=<?= $p['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce participant ?')">🗑️</a>
                                </td>
                                <?php } else ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:1rem;">No participants found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form> <!-- FORM fermé ici -->

    <!-- Pagination moderne avec Précédent / Suivant -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; align-items:center; margin-top:1rem; gap:0.3rem; flex-wrap:wrap;">
        <!-- Bouton Précédent -->
        <?php if ($pages > 1): ?>
            <a href="?page=<?= $pages-1 ?>&filter_name=<?= urlencode($filter_name) ?>&filter_printed=<?= $filter_printed ?>"
               style="padding:0.4rem 0.8rem; border-radius:5px; text-decoration:none; background:#3498db; color:white; font-weight:bold;">« Précédent</a>
        <?php endif; ?>

        <!-- Numéros de page -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>&filter_name=<?= urlencode($filter_name) ?>&filter_printed=<?= $filter_printed ?>"
               style="padding:0.4rem 0.8rem; border-radius:5px; text-decoration:none; background:<?= ($p === $pages) ? '#2ecc71' : '#f1f1f1' ?>; color:<?= ($p === $pages) ? 'white' : '#333' ?>; font-weight:<?= ($p === $pages) ? 'bold' : 'normal' ?>;">
               <?= $p ?>
            </a>
        <?php endfor; ?>

        <!-- Bouton Suivant -->
        <?php if ($pages < $totalPages): ?>
            <a href="?page=<?= $pages+1 ?>&filter_name=<?= urlencode($filter_name) ?>&filter_printed=<?= $filter_printed ?>"
               style="padding:0.4rem 0.8rem; border-radius:5px; text-decoration:none; background:#3498db; color:white; font-weight:bold;">Suivant »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Fenêtre modale QR Code -->
<div id="qrModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); min-width:300px; text-align:center; position:relative;">
        <span style="position:absolute; top:10px; right:15px; font-size:1.5rem; cursor:pointer;" onclick="closeQrModal()">&times;</span>
        <h3 id="qrParticipantName" style="margin-bottom:1rem;"></h3>
        <img id="qrImg" src="" alt="QR Code" style="margin-bottom:1rem; max-width:200px;">
        <br>
        <a id="qrDownload" href="#" download class="btn btn-success" style="padding:0.5rem 1rem;">Download QR Code</a>
    </div>
</div>

<script>
function showQrModal(id, qrText, nom) {
    var qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(qrText) + "&size=200x200";
    document.getElementById('qrImg').src = qrUrl;
    document.getElementById('qrDownload').href = "functions/download_qr.php?id=" + id;
    document.getElementById('qrDownload').removeAttribute('download');
    document.getElementById('qrParticipantName').innerText = nom;
    document.getElementById('qrModal').style.display = "flex";
}

function closeQrModal() { document.getElementById('qrModal').style.display = "none"; }

function toggleAll(source) {
    document.querySelectorAll('.print-checkbox').forEach(cb => { if (!cb.disabled) cb.checked = source.checked; });
}
</script>

<?php renderFooter(); ?>
