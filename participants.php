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

// Récupérer les participants filtrés
$participants = getAllParticipantsWithFilter($pdo, $filter_name, $filter_printed);

include 'includes/header.php';
?>

<div class="container">

    <!-- Affichage des warnings -->
    <?php if (!empty($_SESSION['warning'])): ?>
        <div id="warningMessage" style="background:#fff3cd; color:#856404; padding:1rem; border-radius:5px; margin-bottom:1rem; border:1px solid #ffeeba;">
            <?= htmlspecialchars($_SESSION['warning']); ?>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

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
    <form method="post" action="functions/print_badge.php" id="printForm" target="downloadFrame">
        <div style="display:flex; justify-content:flex-end; margin-bottom:0.5rem;">
            <button type="submit" class="btn btn-success" onclick="handlePrint()">🖨️ Print Selected Badges</button>
        </div>

        <div class="card" style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:700px;">
                <thead>
                    <tr style="background:#f1f1f1;">
                        <th style="padding:0.75rem;"><input type="checkbox" id="checkAll" onclick="toggleAll(this)"></th>
                        <th style="padding:0.75rem;">Name</th>
                        <th style="padding:0.75rem;">Email</th>
                        <th style="padding:0.75rem;">Country</th>
                        <th style="padding:0.75rem;">Category</th>
                        <th style="padding:0.75rem;">Printed</th>
                        <th style="padding:0.75rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants): ?>
                        <?php foreach ($participants as $p): ?>
                            <tr style="border-bottom:1px solid #e1e5e9;">
                                <td style="text-align:center;"><input type="checkbox" class="print-checkbox" name="print_ids[]" value="<?= $p['id'] ?>" <?= !empty($p['isPrinted']) ? 'disabled' : '' ?>></td>
                                <td><?= htmlspecialchars($p['nom']); ?></td>
                                <td><?= htmlspecialchars($p['email']); ?></td>
                                <td><?= htmlspecialchars($p['pays']); ?></td>
                                <td><?= htmlspecialchars($p['type']); ?></td>
                                <td style="text-align:center;"><?= !empty($p['isPrinted']) ? '<span style="color:green; font-weight:bold;">✔</span>' : '<span style="color:#aaa;">✗</span>' ?></td>
                                <td>
                                    <a href="edit_participant.php?id=<?= $p['id']; ?>" class="btn btn-secondary">✏️</a>
                                    <a href="participants.php?delete=<?= $p['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce participant ?')">🗑️</a>
                                </td>
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
    </form>

    <!-- iframe invisible pour le téléchargement -->
    <iframe name="downloadFrame" style="display:none;"></iframe>
</div>

<script>
function toggleAll(source) {
    document.querySelectorAll('.print-checkbox').forEach(cb => { if (!cb.disabled) cb.checked = source.checked; });
}

// Rafraîchir et afficher le warning après traitement
function handlePrint() {
    // Optionnel : tu peux afficher un toast ici avant le reload
    setTimeout(() => { window.location.reload(); }, 1000);
}
</script>

<?php renderFooter(); ?>
