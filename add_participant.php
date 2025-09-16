<?php
require_once 'functions/auth.php';
require_once 'functions/db.php';
require_once 'functions/functions.php';

$countries = require "country.php";
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
    $email = NULL;
    $type = $_POST['type'] ?? '';
    $nso = trim($_POST['nso'] ?? '');


    $photoPath = null;
    if (isset($_POST['photoData']) && !empty($_POST['photoData'])) {
        $data = $_POST['photoData'];
        $data = preg_replace('#^data:image/[^;]+;base64,#', '', $data);
        $decoded = base64_decode($data);

        $uploadDir = 'uploads/photos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $photoName = uniqid('participant_') . '.jpg';
        $photoPath = $uploadDir . $photoName;

        file_put_contents($photoPath, $decoded);
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

include 'includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<style>
#nso { width: 100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem; }
#photo-preview { display:none; border-radius:16px; border:2px solid #eee; max-width:160px; max-height:160px; background:#fafafa; }
.form-group label { display:block; margin-bottom:0.4rem; font-weight:500; }
.form-group input, .form-group select { width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem; }
.btn { width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem; cursor:pointer; }
.card { padding:2rem; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); background:white; }
.alert-success { background:#eafaf1; color:#27ae60; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem; }
.alert-error { background:#fdeaea; color:#e74c3c; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem; }
</style>

<div class="container" style="max-width:500px; margin:2rem auto;">
    <div class="page-header" style="margin-bottom:2rem;">
        <h2 style="margin-bottom:0.5rem;">Add participant</h2>
        <p style="color:#555;">Fill in the participant's information</p>
    </div>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="participantForm" autocomplete="off">
            <!-- <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="prenom">First name</label>
                <input type="text" id="prenom" name="prenom" required>
            </div> -->

            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="nom">Name</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <!-- <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="mail">Email</label>
                <input type="email" id="mail" name="mail" required>
            </div> -->

            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="photo">Photo</label>
                <input type="file" id="photo" accept="image/*" capture="environment">
                <div id="photo-preview-box" style="display:none; justify-content:center; margin-top:1rem;">
                    <canvas id="photo-preview" style="border-radius:16px; border:2px solid #eee; max-width:160px; max-height:160px; background:#fafafa;" data-existing="<?= $participant['photo'] ?? '' ?>"></canvas>
                </div>
                <input type="hidden" name="photoData" id="photoData">
            </div>

            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="type">Category</label>
                <select id="type" name="type" required>
                    <option value="">Category</option>
                    <option value="delegate">Delegate</option>
                    <option value="observer">Observer</option>
                    <option value="organizing team">organizing team</option>
                    <option value="wosm team">WOSM Team</option>
                    <option value="international service team">International Service Team</option>
                    <option value="partner">Partner</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:1.2rem;" id="nso-group">
                <label for="nso" id="nso-label">Country</label>
                <select id="nso" name="nso" required>
                    <option value="">Country</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            

            <button type="submit" class="btn">Add Participant</button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>

<script>
new TomSelect("#nso", { create: false, sortField: {field:"text", direction:"asc"} });

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

function updateNsoField() {
    const type = document.getElementById('type').value;
    const nsoGroup = document.getElementById('nso-group');
    let html = '';
    if (type === 'delegate' || type === 'observer') {
        html = `
            <label for="nso" id="nso-label">Country</label>
            <select id="nso" name="nso" required>
                <option value="">Country</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                <?php endforeach; ?>
            </select>
        `;
    } else if (type === 'partner') {
        html = `
            <label for="nso" id="nso-label">Institution / Organization</label>
            <input type="text" id="nso" name="nso" required
                style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
        `;
    }else if (type === 'organizing team') {
        html = `
            <label for="nso" id="nso-label">Responsibility</label>
            <input type="text" id="nso" name="nso" required
                style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
        `;
    }else if (type === 'wosm team') {
        html = `
            <label for="nso" id="nso-label">Role / Resonsibility</label>
            <input type="text" id="nso" name="nso" required
                style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
        `;
    }else if (type === 'international service team') {
        html = `
            <label for="nso" id="nso-label">Role / Task</label>
            <input type="text" id="nso" name="nso" required
                style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
        `;
    } 
    nsoGroup.innerHTML = html;
    // Réinitialise TomSelect si c'est un select
    if (type == 'delegate' || type == 'observer' ) {
        new TomSelect("#nso", { create: false, sortField: {field:"text", direction:"asc"} });
    }
}

document.getElementById('type').addEventListener('change', updateNsoField);
window.addEventListener('DOMContentLoaded', updateNsoField);
</script>
