<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();

$message = '';
$error = '';

$pays = getAllPays($pdo);

if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = $_POST['type'] ?? '';
    $nso = trim($_POST['nso'] ?? '');

    // Vérification que le pays existe dans la base
    $stmt = $pdo->prepare("SELECT id FROM pays WHERE nom = :nom LIMIT 1");
    $stmt->execute(['nom' => $nso]);
    $paysData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paysData) {
        $error = "Le pays sélectionné n'est pas valide.";
    } else {
        $pays_id = $paysData['id'];

        // Gestion de la photo uploadée
        $photoPath = null;
        if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
            // photoData contient la base64 compressée
            $data = $_POST['photoData'];
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);

            $uploadDir = 'uploads/photos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $photoName = uniqid('participant_') . '.png';
            $photoPath = $uploadDir . $photoName;

            file_put_contents($photoPath, $decoded);
        } else {
            $error = "Photo obligatoire.";
        }

        if (!$error) {
            // Appel à la fonction addParticipant
            $result = addParticipant($pdo, $nom, $prenom, $email, $type, $pays_id, $photoPath);

            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
.autocomplete-wrapper {
  position: relative;
  width: 100%;
}
#nso, #nso-suggestion {
  width: 100%;
  padding: 0.5rem;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-family: inherit;
  font-size: inherit;
}
#nso-suggestion {
  position: absolute;
  top: 0;
  left: 0;
  color: #aaa;
  pointer-events: none;
}
</style>

<div class="container" style="max-width:500px; margin:2rem auto;">
    <div class="page-header" style="margin-bottom:2rem;">
        <h2 style="margin-bottom:0.5rem;">Add participant</h2>
        <p style="color:#555;">Fill in the participant's information</p>
    </div>

    <div class="card" style="padding:2rem; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
        <?php if ($message): ?>
            <div class="alert alert-success" style="background:#eafaf1; color:#27ae60; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" style="background:#fdeaea; color:#e74c3c; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="participantForm" autocomplete="off">
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="prenom" style="display:block; margin-bottom:0.4rem; font-weight:500;">First name</label>
                <input type="text" id="prenom" name="prenom" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nom" style="display:block; margin-bottom:0.4rem; font-weight:500;">Last name</label>
                <input type="text" id="nom" name="nom" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="mail" style="display:block; margin-bottom:0.4rem; font-weight:500;">Email</label>
                <input type="email" id="mail" name="mail" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <!-- Photo -->
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="photo" style="display:block; margin-bottom:0.4rem; font-weight:500;">Photo</label>
                <input type="file" id="photo" accept="image/*"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
                <canvas id="photo-preview" style="display:none; margin-top:10px; max-width:200px; border-radius:8px; border:1px solid #eee;"></canvas>
                <input type="hidden" name="photoData" id="photoData">
            </div>

            <!-- NSO (Country) -->
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nso" style="display:block; margin-bottom:0.4rem; font-weight:500;">NSO (Country)</label>
                <div class="autocomplete-wrapper" style="width:100%;">
                    <input type="text" id="nso" name="nso" autocomplete="off" required
                        style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <input type="text" id="nso-suggestion" tabindex="-1" disabled
                        style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; position:absolute; top:0; left:0; color:#aaa; pointer-events:none; background:transparent;">
                </div>
            </div>

            <!-- Category -->
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="type" style="display:block; margin-bottom:0.4rem; font-weight:500;">Category</label>
                <select id="type" name="type" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <option value="">-- Select --</option>
                    <option value="delegate">Delegate</option>
                    <option value="observer">Observer</option>
                    <option value="organizing_committee">Organizing Committee</option>
                    <option value="wosm_team">WOSM Team</option>
                    <option value="volunteer">Volunteer</option>
                    <option value="staff">Staff</option>
                    <option value="partner">Partner</option>
                </select>
            </div>

            <button type="submit" class="btn"
                style="width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem;">
                Add Participant
            </button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>

<!-- JS Autocomplete -->
<script>
const countries = [
<?php foreach ($pays as $p): ?>
  "<?= addslashes($p['nom']) ?>",
<?php endforeach; ?>
];

const input = document.getElementById("nso");
const suggestion = document.getElementById("nso-suggestion");

input.addEventListener("input", () => {
  const value = input.value.toLowerCase();
  if (!value) { suggestion.value = ""; return; }
  const match = countries.find(c => c.toLowerCase().startsWith(value));
  suggestion.value = match ? input.value + match.slice(value.length) : "";
});

input.addEventListener("keydown", e => {
  if (e.key === "Tab" || e.key === "ArrowRight") {
    if (suggestion.value) {
      e.preventDefault();
      input.value = suggestion.value;
      suggestion.value = "";
    }
  }
});
</script>

<!-- JS Photo Capture & Compression -->
<script>
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo-preview');
const photoDataInput = document.getElementById('photoData');

photoInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const MAX_WIDTH = 600;
            const scale = Math.min(1, MAX_WIDTH / img.width);
            const canvas = photoPreview;
            canvas.width = img.width * scale;
            canvas.height = img.height * scale;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.style.display = 'block';
            // enregistrer l'image compressée en base64
            photoDataInput.value = canvas.toDataURL('image/png');
        }
        img.src = e.target.result;
    }
    reader.readAsDataURL(file);
});
</script>
