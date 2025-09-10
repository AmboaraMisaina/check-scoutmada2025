<?php
require_once 'functions/functions.php';


$countries = require "country.php";


// // Récupérer le participant
// $participant = getParticipantById($pdo, (int)$id);
// if (!$participant) {
//     header('Location: participants.php');
//     exit;
// }

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
        $error = "The selected country is not valid.";
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
                $message = "Participant updated successfully!";
                $participant = getParticipantById($pdo, (int)$id);
            } else {
                $error = "Error updating participant.";
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

    
</div>

<?php renderFooter(); ?>

<!-- JS Autocomplete -->
<script>
new TomSelect("#nso", {
    create: false,
    sortField: {field: "text", direction: "asc"}
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
