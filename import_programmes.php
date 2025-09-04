<?php
require_once 'functions.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv'])) {
    $file = $_FILES['csv']['tmp_name'];
    $handle = fopen($file, 'r');
    $row = 0;
    $imported = 0;
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $row++;
        if ($row == 1) continue; // Ignore header

        // Supposons que le CSV contient : titre,description,horaire_debut,horaire_fin,ouvert_a
        list($titre, $description, $horaire_debut, $horaire_fin, $ouvert_a) = $data;

        $stmt = $pdo->prepare("INSERT INTO programmes (titre, description, horaire_debut, horaire_fin, ouvert_a) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$titre, $description, $horaire_debut, $horaire_fin, $ouvert_a])) {
            $imported++;
        }
    }
    fclose($handle);
    $message = "<div style='margin-top:1rem; text-align:center; color:green; font-weight:bold;'>$imported programmes importés.</div>";
}
renderHeader('Import Programme');
?>


<div class="container" style="max-width:500px; margin:auto; margin-top:2rem;">
    <div class="card" style="padding:2rem; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="text-align:center; margin-bottom:1.5rem;">Importer des Programmes</h2>
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