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
        // Ici tu peux récupérer l'id du pays
        $pays_id = $paysData['id'];

        // Appel à ta fonction centrale avec pays_id
        $result = addParticipant($pdo, $nom, $prenom, $email, $type, $pays_id);

        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
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
  color: #aaa; /* suggestion en gris */
  pointer-events: none;
}
</style>
<div class="container">
    <div class="page-header">
        <h2>Add participant</h2>
        <p>Fill in the participant's information</p>
    </div>

    <div class="card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="prenom" style="display:block; margin-bottom:0.5rem;">First name</label>
                <input type="text" id="prenom" name="prenom" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="nom" style="display:block; margin-bottom:0.5rem;">Last name</label>
                <input type="text" id="nom" name="nom" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="mail" style="display:block; margin-bottom:0.5rem;">Email</label>
                <input type="email" id="mail" name="mail" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="photo" style="display:block; margin-bottom:0.5rem;">Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*" capture="environment" required>
                <canvas id="photo-preview" style="display:none; margin-top:10px; max-width:200px;"></canvas>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="nso" style="display:block; margin-bottom:0.5rem;">NSO (Country)</label>
                <div class="autocomplete-wrapper">
                    <input type="text" id="nso" name="nso" autocomplete="off" required>
                    <input type="text" id="nso-suggestion" tabindex="-1" disabled>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="type" style="display:block; margin-bottom:0.5rem;">Category</label>
                <select id="type" name="type" required
                    style="width:100%; padding:0.5rem; border-radius:5px; border:1px solid #ccc;">
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

            <button type="submit" class="btn" style="margin-top:1rem;">Add Participant</button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>


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

  if (!value) {
    suggestion.value = "";
    return;
  }

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

<script>
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photo-preview');

photoInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Définir largeur max pour la compression
            const MAX_WIDTH = 600;
            const scale = Math.min(1, MAX_WIDTH / img.width);
            const canvas = photoPreview;
            canvas.width = img.width * scale;
            canvas.height = img.height * scale;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.style.display = 'block';
        }
        img.src = e.target.result;
    }
    reader.readAsDataURL(file);
});
</script>