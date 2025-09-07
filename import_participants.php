<?php
require_once 'functions/functions.php';


if ($_SESSION['role'] !== 'admin') {
    renderHeader("Accès interdit");
    ?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Accès interdit</h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;">Vous n’avez pas les droits nécessaires pour accéder à cette page.</p>
            <a href="dashboard.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Retour au tableau de bord
            </a>
        </div>
    </div>
    <?php
    renderFooter();
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv'])) {
    $file = $_FILES['csv']['tmp_name'];
    $handle = fopen($file, 'r');
    $row = 0;
    $imported = 0;
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $row++;
        if ($row == 1) continue; // Ignore header

        list($nom, $prenom, $email, $type) = $data;

        $result = addParticipant($pdo, $nom, $prenom, $email, $type);
        if ($result['success']) $imported++;
    }
    fclose($handle);
    $message = "<div style='margin-top:1rem; text-align:center; color:green; font-weight:bold;'>$imported participants importés.</div>";
}



renderHeader('Import Participants');
?>


<div class="container" style="max-width:500px; margin:auto; margin-top:2rem;">
    <div class="card" style="padding:2rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="text-align:center; margin-bottom:1.5rem;">Importer des Participants</h2>
        <form method="post" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:1rem;">
            <label for="csv" style="font-weight:bold;">Fichier CSV :</label>
            <input type="file" name="csv" id="csv" accept=".csv" required style="padding:0.5rem;">
            <button type="submit" style="padding:0.7rem 1.5rem; border-radius:5px; background:#38ef7d; color:white; border:none; font-weight:bold; cursor:pointer;">
                Importer
            </button>
        </form>
        <?= $message ?>
    </div>
</div>
<?php renderFooter(); ?>