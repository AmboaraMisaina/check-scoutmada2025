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

    if (!in_array($nso, $countries)) {
        $error = "The selected country is not valid.";
    } else {
        // Gestion de la photo uploadée
        $photoPath = $participant['photo'] ?? null;
        if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
            $data = preg_replace('#^data:image/[^;]+;base64,#', '', $_POST['photoData']);
            $decoded = base64_decode($data);

            $uploadDir = 'uploads/photos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $photoName = uniqid('participant_') . '.jpg';
            $photoPath = $uploadDir . $photoName;

            file_put_contents($photoPath, $decoded);
        }

        $result = updateParticipant($pdo, $id, $nom, $prenom, $email, $type, $nso, $photoPath);
        if ($result['success']) {
            $message = $result['message'];
            $participant = getParticipantById($pdo, (int)$id);
        } else {
            $error = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

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
                <label for="nom">Name</label>
                <input type="text" id="nom" name="nom" required
                    value="<?= htmlspecialchars($participant['nom']) ?>"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
            </div>



            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="mail">Email</label>
                <input type="email" id="mail" name="mail" required
                    value="<?= htmlspecialchars($participant['email']) ?>"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
            </div>

            <!-- Photo -->
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="photo">Photo</label>
                <input type="file" id="photo" accept="image/*" capture="environment"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc;">
                <div style="display:flex; justify-content:center; margin-top:1rem;">
                    <canvas id="photo-preview" style="border-radius:16px; border:2px solid #eee; max-width:180px; max-height:180px;"
                        data-existing="<?= $participant['photo'] ?? '' ?>"></canvas>
                </div>
                <input type="hidden" name="photoData" id="photoData">
            </div>

            <!-- NSO (Country) -->
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nso">NSO (Country)</label>
                <select id="nso" name="nso" required style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <option value="">Country</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>" <?= $participant["pays"] === $country ? 'selected' : '' ?>><?= htmlspecialchars($country) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Category -->
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="type">Category</label>
                <select id="type" name="type" required style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <option value="">-- Select --</option>
                    <option value="delegate" <?= $participant['type'] === 'delegate' ? 'selected' : '' ?>>Delegate</option>
                    <option value="observer" <?= $participant['type'] === 'observer' ? 'selected' : '' ?>>Observer</option>
                    <option value="organizing committee" <?= $participant['type'] === 'organizing committee' ? 'selected' : '' ?>>Organizing Committee</option>
                    <option value="wosm team" <?= $participant['type'] === 'wosm team' ? 'selected' : '' ?>>WOSM Team</option>
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

<script>
new TomSelect("#nso", {
    create: false,
    sortField: {field: "text", direction: "asc"}
});

// Photo preview + compression
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo-preview');
const photoDataInput = document.getElementById('photoData');

// Affichage photo existante
const existingPhoto = photoPreview.getAttribute('data-existing');
if (existingPhoto) {
    const img = new Image();
    img.onload = function() {
        photoPreview.width = img.width;
        photoPreview.height = img.height;
        const ctx = photoPreview.getContext('2d');
        ctx.clearRect(0,0,img.width,img.height);
        ctx.drawImage(img,0,0,img.width,img.height);
        photoPreview.style.display = 'block';
    }
    img.src = existingPhoto;
}

// Compression et redimension
photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

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
            ctx.drawImage(img,0,0,width,height);

            // Compression progressive
            let quality = 0.9;
            let dataUrl = canvas.toDataURL('image/jpeg', quality);
            const maxSizeKB = 100;
            while ((dataUrl.length/1024) > maxSizeKB && quality > 0.1) {
                quality -= 0.05;
                dataUrl = canvas.toDataURL('image/jpeg', quality);
            }

            // Affichage preview
            photoPreview.width = width;
            photoPreview.height = height;
            const ctxPreview = photoPreview.getContext('2d');
            ctxPreview.clearRect(0,0,width,height);
            const previewImg = new Image();
            previewImg.onload = function() {
                ctxPreview.drawImage(previewImg,0,0,width,height);
                photoPreview.style.display = 'block';
            }
            previewImg.src = dataUrl;

            // Stockage base64 compressée pour POST
            photoDataInput.value = dataUrl;
        }
        img.src = ev.target.result;
    }
    reader.readAsDataURL(file);
});
</script>
