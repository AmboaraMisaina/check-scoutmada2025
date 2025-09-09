<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();


$countries = require "country.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: participants.php');
    exit;
}

// Récupérer le participant
$participant = getParticipantById($pdo, (int)$id);
if (!$participant) {
    header('Location: participants.php');
    exit;
}

$message = '';
$error = '';

if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['mail'] ?? '');
    $type = $_POST['type'] ?? '';
    $nso = trim($_POST['nso'] ?? '');

    // Vérification que le pays est valide
    $normalizedNso = mb_strtolower($nso);
    $normalizedCountries = array_map(fn($c) => mb_strtolower($c), $countries);

    if (!in_array($normalizedNso, $normalizedCountries)) {
        $error = "Le pays sélectionné n'est pas valide.";
    } else {
        // Gestion de la photo uploadée
        $photoPath = $participant['photo'] ?? null;
        if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
            $data = $_POST['photoData'];
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            $decoded = base64_decode($data);

            $uploadDir = 'uploads/photos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $photoName = uniqid('participant_') . '.png';
            $photoPath = $uploadDir . $photoName;

            file_put_contents($photoPath, $decoded);
        }

        if (!$error) {
            if (updateParticipant($pdo, $id, $nom, $prenom, $email, $type, $nso, $photoPath)) {
                $message = "Participant mis à jour avec succès !";
                $participant = getParticipantById($pdo, (int)$id);
            } else {
                $error = "Erreur lors de la mise à jour du participant.";
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
        <h2>Edit Participant</h2>
        <p>Update participant information</p>
    </div>

    <div class="card" style="padding:2rem; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="participantForm" autocomplete="off">


            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="nom" style="display:block; margin-bottom:0.4rem;">Name</label>
                <input type="text" id="nom" name="nom" required
                    value="<?= htmlspecialchars($participant['nom']) ?>"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="mail" style="display:block; margin-bottom:0.4rem;">Email</label>
                <input type="email" id="mail" name="mail" required
                    value="<?= htmlspecialchars($participant['email']) ?>"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
            </div>

            <!-- Photo -->
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="photo" style="display:block; margin-bottom:0.4rem;">Photo</label>
                <input type="file" id="photo" accept="image/*"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <canvas id="photo-preview" style="border-radius:16px; border:2px solid #eee; max-width:180px; max-height:180px;"></canvas>
                </div>
                <input type="hidden" name="photoData" id="photoData">

            </div>

            <!-- NSO (Country) -->
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nso" style="display:block; margin-bottom:0.4rem; font-weight:500;">NSO (Country)</label>
                <div class="autocomplete-wrapper" style="width:100%;">
                    <input type="text" id="nso" name="nso" autocomplete="off" required
                    value="<?= htmlspecialchars($participant['pays']) ?>"
                        style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <input type="text" id="nso-suggestion" tabindex="-1" disabled
                        style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; position:absolute; top:0; left:0; color:#aaa; pointer-events:none; background:transparent;">
                </div>
            </div>

            <!-- Category -->
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="type" style="display:block; margin-bottom:0.4rem;">Category</label>
                <select id="type" name="type" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <option value="">-- Select --</option>
                    <option value="delegate" <?= $participant['type'] === 'delegate' ? 'selected' : '' ?>>Delegate</option>
                    <option value="observer" <?= $participant['type'] === 'observer' ? 'selected' : '' ?>>Observer</option>
                    <option value="organizing_committee" <?= $participant['type'] === 'organizing_committee' ? 'selected' : '' ?>>Organizing Committee</option>
                    <option value="wosm_team" <?= $participant['type'] === 'wosm_team' ? 'selected' : '' ?>>WOSM Team</option>
                    <option value="volunteer" <?= $participant['type'] === 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
                    <option value="staff" <?= $participant['type'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="partner" <?= $participant['type'] === 'partner' ? 'selected' : '' ?>>Partner</option>
                </select>
            </div>

            <button type="submit" class="btn" style="width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem;">Update</button>
            <a href="participants.php" class="btn btn-secondary" style="margin-top:1rem;">Back</a>
        </form>
    </div>
</div>

<?php renderFooter(); ?>

<!-- JS Autocomplete -->
<script>
const countries = <?= json_encode($countries, JSON_UNESCAPED_UNICODE) ?>;

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

<!-- JS Photo Capture -->
<script>
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo-preview');
const photoDataInput = document.getElementById('photoData');

// Afficher photo existante dans canvas
<?php if ($participant['photo']): ?>
const img = new Image();
img.onload = () => {
    photoPreview.width = img.width;
    photoPreview.height = img.height;
    photoPreview.getContext('2d').drawImage(img, 0, 0);
    photoPreview.style.display = 'block';
};
img.src = '<?= htmlspecialchars($participant['photo']) ?>';
<?php endif; ?>

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
            photoDataInput.value = canvas.toDataURL('image/png');
        }
        img.src = e.target.result;
    }
    reader.readAsDataURL(file);
});
</script>
