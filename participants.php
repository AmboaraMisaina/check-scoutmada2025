<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();

// Vérifier les droits
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registration' && $_SESSION['role'] !== 'kit' && $_SESSION['role'] !== 'print')  {
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
$filter_type = $_GET['filter_type'] ?? '';
$filter_paid = $_GET['filter_paid'] ?? '';
$to_print = $_GET['to_print'] ?? 0;

// Pagination
$perPage = 20;
$pages = max(1, intval($_GET['page'] ?? 1));
$offset = ($pages - 1) * $perPage;

$participants = getAllParticipantsWithFilter($pdo, $filter_name, $to_print, $filter_type, $filter_paid, $perPage, $offset);
$totalParticipants = getTotalParticipantsWithFilter($pdo, $filter_name, $to_print, $filter_type);
$totalPages = ceil($totalParticipants / $perPage);

// Récupération des pays dans un tableau
$paysList = array_column($participants, 'pays');

// Suppression des doublons
$distinctCountries = array_unique($paysList);

// Nombre de pays distincts
$totalCountries = count($distinctCountries);


include 'includes/header.php';
?>

<style>
/* Container */
.container {
  padding: 0.5rem;
}

/* Actions principales */
.card {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.card a.btn {
  flex: 1;
  text-align: center;
  min-width: 120px;
}

/* Filtre */
.card form {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.card form input,
.card form select,
.card form button,
.card form a {
  flex: 1;
  min-width: 140px;
}

table {
  width: 100%;
  border-collapse: collapse;
}


</style>


<div class="container">

    <!-- Header page -->
    <div class="page-header" style="margin-bottom:1.5rem;">
        <h2>Participants</h2>
        <p>Manage all registered participants</p>
    </div>

    <!-- Actions principales -->
    <div class="card" style="display:flex; flex-wrap:wrap; gap:0.5rem; padding:1rem; margin-bottom:1rem;">
        <a href="add_participant.php" class="btn">➕ Add Participant</a>
        <?php if ($_SESSION['role'] == 'admin') {  ?>
        <a href="add_guest.php" class="btn">➕ Add Guest</a>
        <?php } ?> 
        <a href="import_participants.php" class="btn btn-primary">📥 Import Participants</a>
    </div>

    <!-- Filtre -->
    <div class="card" style="padding:1rem; margin-bottom:1rem;">
        <form method="GET" action="participants.php" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <input type="text" name="filter_name" placeholder="Search by name" value="<?= htmlspecialchars($filter_name) ?>" style="padding:0.5rem; border-radius:5px; border:1px solid #ccc; flex:1;">
            <select name="filter_paid" style="padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
                <option value="">Payment</option>
                <option value="1" <?= ($filter_paid === '1') ? 'selected' : '' ?>>Paid</option>
                <option value="0" <?= ($filter_paid === '0') ? 'selected' : '' ?>>Not Paid</option>
            </select>


            <select name="filter_type" style="padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
                <option value="">Category</option>
                <option value="delegate" <?= ($filter_type === 'delegate') ? 'selected' : '' ?>>Delegate</option>
                <option value="observer" <?= ($filter_type === 'observer') ? 'selected' : '' ?>>Observer</option>
                <option value="organizing team" <?= ($filter_type === 'organizing team') ? 'selected' : '' ?>>Organizing Team</option>
                <option value="wosm team" <?= ($filter_type === 'wosm team') ? 'selected' : '' ?>>WOSM Team</option>
                <option value="International service team" <?= ($filter_type === 'International service team') ? 'selected' : '' ?>>International Service Team</option>
                <option value="Partner" <?= ($filter_type === 'Partner') ? 'selected' : '' ?>>Partner</option>
                <option value="guest" <?= ($filter_type === 'guest') ? 'selected' : '' ?>>Guest</option>
            </select>
            <label class="checkbox-inline">
                <input type="checkbox" name="to_print" value="1" <?= ($to_print === '1') ? 'checked' : '' ?>> To Print
            </label>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="participants.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Table des participants -->
    <form method="post" action="functions/print_badge.php" id="printForm">
        <?php
        if ($_SESSION['role'] == 'print' || $_SESSION['role'] == 'admin') {
            ?>
            <div style="display:flex; justify-content:flex-end; margin-bottom:0.5rem;">
                <button type="submit" class="btn btn-success">🖨️ Print Selected Badges</button>
            </div>
        <?php
        } ?>

        <div class="card" style="overflow-x:auto;">
            <p><?php echo "Total: ". $totalParticipants; ?> | <?php echo "NSO: ".$totalCountries ?></p>
            <table style="width:100%; border-collapse:collapse; min-width:700px;">
                <thead>
                    <tr style="background:#f1f1f1;">
                        <th style="padding:0.75rem;"><input type="checkbox" id="checkAll" onclick="toggleAll(this)"></th>
                        <th style="padding:0.75rem;">Name</th>
                        <!-- <th style="padding:0.75rem;">Email</th> -->
                        <th style="padding:0.75rem;">Country</th>
                        <th style="padding:0.75rem;">Category</th>
                        <th style="padding:0.75rem;">Process</th>
                        <?php if ($_SESSION['role'] == 'admin') { ?>
                        <th style="padding:0.75rem;">Actions</th>
                        <th style="padding:0.75rem;">   </th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants): ?>
                        <?php foreach ($participants as $p): ?>
                            <tr style="border-bottom:1px solid #e1e5e9;">
                                <td style="text-align:center;">
                                    <?php
                                        $disabled = true; // par défaut désactivé

                                        // Si pas encore imprimé
                                        if (empty($p['isPrinted'])) {
                                            if (in_array(strtolower($p['type']), ['delegate', 'observer'])) {
                                                // Pour délégué et observateur → payant et photo obligatoires
                                                if (!empty($p['paid']) && !empty($p['withPhoto'])) {
                                                    $disabled = false;
                                                }
                                            } else {
                                                // Pour les autres types → pas de conditions
                                                $disabled = false;
                                            }
                                        }
                                    ?>
                                    <input type="checkbox" class="print-checkbox" name="print_ids[]" value="<?= $p['id'] ?>" <?= $disabled ? 'disabled' : '' ?>>
                                </td>
                                <td><?= htmlspecialchars($p['nom']); ?></td>
                                <!-- <td><?= htmlspecialchars($p['email']); ?></td> -->
                                <td><?= htmlspecialchars($p['pays']); ?></td>
                                <td><?= htmlspecialchars($p['type']); ?></td>
                                <td style="text-align:center;">
                                    <?= !empty($p['isPrinted']) ? '🖨️' : '' ?> 
                                    <?= !empty($p['withPhoto']) ? '📸' : '' ?> 
                                    <?= !empty($p['kit']) ? '👕' : '' ?>
                                    <?= !empty($p['paid']) ? '💰' : '' ?>
                                </td>
                                <?php if ($_SESSION['role'] == 'admin') { ?>
                                    <td>
                                        <button type="button" class="btn btn-info" onclick="document.getElementById('photoInput-<?= $p['id'] ?>').click()">📸</button>
                                        <input type="file" id="photoInput-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>" accept="image/*" capture="environment" style="display:none;">
                                        
                                        <button type="button" class="btn btn-warning" onclick="toggleKit(<?= $p['id']; ?>, this)">👕</button>
                                        <button type="button" class="btn btn-success" onclick="togglePay(<?= $p['id']; ?>, this)">💰</button>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-danger" onclick="if(confirm('Supprimer ce participant ?')) window.location.href='participants.php?delete=<?= $p['id']; ?>'">🗑️</button>
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='edit_participant.php?id=<?= $p['id']; ?>'">✏️</button>
                                    </td>
                                    
                                    
                                <?php } if ($_SESSION['role'] == 'registration') {
                                    ?>
                                    <td>
                                        <button type="button" class="btn btn-info" onclick="document.getElementById('photoInput-<?= $p['id'] ?>').click()">📸</button>
                                        <input type="file" id="photoInput-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>" accept="image/*" capture="environment" style="display:none;">

                                        <button type="button" class="btn btn-success" onclick="togglePay(<?= $p['id']; ?>, this)">💰</button>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="window.location.href='edit_participant.php?id=<?= $p['id']; ?>'">✏️</button>
                                    </td>
                                <?php } if ($_SESSION['role'] == 'kit') {
                                    ?>
                                    <td>
                                        <button type="button" class="btn btn-warning" onclick="toggleKit(<?= $p['id']; ?>, this)">👕</button>
                                    </td>
                                    <?php
                                } ?>
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
            <a href="?page=<?= $pages-1 ?>&filter_name=<?= urlencode($filter_name) ?>&to_print=<?= $to_print ?>"
               style="padding:0.4rem 0.8rem; border-radius:5px; text-decoration:none; background:#3498db; color:white; font-weight:bold;">« Précédent</a>
        <?php endif; ?>

        <!-- Numéros de page -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>&filter_name=<?= urlencode($filter_name) ?>&to_print=<?= $to_print ?>"
               style="padding:0.4rem 0.8rem; border-radius:5px; text-decoration:none; background:<?= ($p === $pages) ? '#2ecc71' : '#f1f1f1' ?>; color:<?= ($p === $pages) ? 'white' : '#333' ?>; font-weight:<?= ($p === $pages) ? 'bold' : 'normal' ?>;">
               <?= $p ?>
            </a>
        <?php endfor; ?>

        <!-- Bouton Suivant -->
        <?php if ($pages < $totalPages): ?>
            <a href="?page=<?= $pages+1 ?>&filter_name=<?= urlencode($filter_name) ?>&to_print=<?= $to_print ?>"
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


<script>
document.querySelectorAll('input[type=file][id^="photoInput-"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const participantId = e.target.dataset.id;
        const reader = new FileReader();

        reader.onload = function(ev) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;
                const MAX_WIDTH = 600, MAX_HEIGHT = 600;

                if (width > height && width > MAX_WIDTH) {
                    height = Math.round(height * MAX_WIDTH / width);
                    width = MAX_WIDTH;
                } else if (height > MAX_HEIGHT) {
                    width = Math.round(width * MAX_HEIGHT / height);
                    height = MAX_HEIGHT;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                let quality = 0.9;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);
                const maxSizeKB = 100;

                while ((dataUrl.length/1024) > maxSizeKB && quality > 0.1) {
                    quality -= 0.05;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                }

                // Envoi AJAX au serveur
                fetch('functions/update_photo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: participantId, photoData: dataUrl })
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        alert("✅ Photo mise à jour avec succès !");
                        location.reload();
                    } else {
                        alert("❌ Erreur : " + resp.message);
                    }
                })
                .catch(err => alert("Erreur réseau : " + err));
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    });
});

function toggleKit(id, btn) {
    fetch('functions/updateKit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    // .then(res => res.json()) 
    .then(async res => {
        console.log("Raw response :", res);
        const txt = await res.text();
        console.log("Raw response text:", txt);
        return JSON.parse(txt); // essaie de parser
    })
    .then(resp => {
        console.log(resp)
        if (resp.success) {
            location.reload();
        } else {
            alert("❌ Erreur : " + resp.message);
        }
    })
    .catch(err => alert("Erreur réseau : " + err));
}

function togglePay(id, btn) {
    fetch('functions/updatePay.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    // .then(res => res.json()) 
    .then(async res => {
        console.log("Raw response :", res);
        const txt = await res.text();
        console.log("Raw response text:", txt);
        return JSON.parse(txt); // essaie de parser
    })
    .then(resp => {
        console.log(resp)
        if (resp.success) {
            location.reload();
        } else {
            alert("❌ Erreur : " + resp.message);
        }
    })
    .catch(err => alert("Erreur réseau : " + err));
}
</script>