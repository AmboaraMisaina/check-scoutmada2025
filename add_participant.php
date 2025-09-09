<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
$countries = require "country.php"; // <- ton fichier avec le tableau des pays
checkAuth();

if ($_SESSION['role'] !== 'admin' &&  $_SESSION['role'] !== 'registration') {
    include 'includes/header.php';
    ?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Forbidden  </h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;"> <?= $_SESSION['role'] ?>  You do not have the necessary rights to access this page.</p>
            <a href="checkin.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Back
            </a>
        </div>
    </div>
    <?php
    renderFooter();
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
    // Vérification que le pays est valide dans le tableau
    $normalizedNso = mb_strtolower(trim($nso));
    $normalizedCountries = array_map(function($c){ return mb_strtolower(trim($c)); }, $countries);

    if (!in_array($normalizedNso, $normalizedCountries)) {
        $error = "Le pays sélectionné n'est pas valide.";
} else {
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
            $nso = ucfirst($nso);
            $result = addParticipant($pdo, $nom, $prenom, $email, $type, $nso, $photoPath);

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
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <canvas id="photo-preview" style="display:none; border-radius:16px; border:2px solid #eee; max-width:180px; max-height:180px;"></canvas>
                </div>
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

photoInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const MAX_WIDTH = 600;   // largeur max
            const MAX_HEIGHT = 600; // hauteur max
            let width = img.width;
            let height = img.height;

            // Redimension proportionnel
            if (width > height) {
                if (width > MAX_WIDTH) {
                    height = Math.round(height * (MAX_WIDTH / width));
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width = Math.round(width * (MAX_HEIGHT / height));
                    height = MAX_HEIGHT;
                }
            }

            const canvas = photoPreview;
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // Compression en JPEG (qualité 0.7 = 70%)
            const compressedData = canvas.toDataURL('image/jpeg', 0.7);

            canvas.style.display = 'block';
            photoDataInput.value = compressedData;
        }
        img.src = e.target.result;
    }
    reader.readAsDataURL(file);
});
</script>

