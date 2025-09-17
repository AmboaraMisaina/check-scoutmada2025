<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';
checkAuth();

$message = '';
$error = '';

// Traitement du formulaire
if ($_POST) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = NULL;
    $type = 'guest';
    $pays = NULL;
    $photoPath = NULL;
    if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
        $data = $_POST['photoData'];
        $data = preg_replace('#^data:image/[^;]+;base64,#', '', $data);
        $decoded = base64_decode($data);

        $uploadDir = 'uploads/photos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $photoName = uniqid('guest_') . '.jpg';
        $photoPath = $uploadDir . $photoName;

        file_put_contents($photoPath, $decoded);
    }
    $result = addParticipant($pdo, $nom, $prenom, $email, $type, $pays, $photoPath);

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

include 'includes/header.php';
?>

<div class="container" style="max-width:500px; margin:2rem auto;">
    <div class="page-header" style="margin-bottom:2rem;">
        <h2 style="margin-bottom:0.5rem;">Add guest</h2>
        <p style="color:#555;">Fill guest information</p>
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

        <form method="POST" autocomplete="off">
            <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="nom" style="display:block; margin-bottom:0.4rem; font-weight:500;">Name</label>
                <input type="text" id="nom" name="nom" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>

            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="photo">Photo</label>
                <input type="file" id="photo" accept="image/*" capture="environment">
                <div id="photo-preview-box" style="display:none; justify-content:center; margin-top:1rem;">
                    <canvas id="photo-preview" style="border-radius:16px; border:2px solid #eee; max-width:160px; max-height:160px; background:#fafafa;" data-existing="<?= $participant['photo'] ?? '' ?>"></canvas>
                </div>
                <input type="hidden" name="photoData" id="photoData">
            </div>
            <!-- <div class="form-group" style="margin-bottom: 1.2rem;">
                <label for="mail" style="display:block; margin-bottom:0.4rem; font-weight:500;">Email</label>
                <input type="email" id="mail" name="mail" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div> -->

            <button type="submit" class="btn" style="width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem;">
                Register
            </button>
        </form>
    </div>
</div>



<?php renderFooter(); ?>


<script>
    
// Compression et redimension image
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo-preview');
const photoPreviewBox = document.getElementById('photo-preview-box');
const photoDataInput = document.getElementById('photoData');

photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) {
        photoPreviewBox.style.display = 'none';
        return;
    }

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
                photoPreviewBox.style.display = 'flex'; // Affiche la boîte d'aperçu
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