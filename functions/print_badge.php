<?php
require_once 'functions.php';
checkAuthOrRedirect();
session_start(); // nécessaire pour stocker le warning

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['print_ids'])) {
    $ids = array_map('intval', $_POST['print_ids']);
    $totalSelected = count($ids);

    // Limiter à 4 participants maximum
    if ($totalSelected > 4) {
        $_SESSION['warning'] = "Only the first 4 participants have been printed. " . ($totalSelected - 4) . " participant(s) were not processed.";
        $ids = array_slice($ids, 0, 4); // garder seulement les 4 premiers
    }

    // Création d'un fichier ZIP temporaire
    $zipFile = tempnam(sys_get_temp_dir(), 'badges_') . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        die("Impossible de créer l'archive ZIP.");
    }

    foreach ($ids as $id) {
        $p = getParticipantById($pdo, $id);
        if (!$p) continue;

        $nom = $p['nom'] ?? '';
        $prenom = $p['prenom'] ?? '';
        $pays = $p['pays'] ?? '';
        $type = $p['type'] ?? '';
        $qr = $p['qr_code'] ?? '';

        // Générer le recto
        $recto = genererBadge($nom, $pays, $type, $qr, 'recto');
        if ($recto) {
            ob_start();
            imagepng($recto);
            $imgData = ob_get_clean();
            imagedestroy($recto);

            if (!empty($imgData)) {
                $fileName = preg_replace('/[^a-z0-9_\-]/i', '_', $nom . '_' . $prenom) . '_recto.png';
                $zip->addFromString($fileName, $imgData);
            }
        }

        // Générer le verso
        $verso = genererBadge($nom, $pays, $type, $qr, 'verso');
        if ($verso) {
            ob_start();
            imagepng($verso);
            $imgData = ob_get_clean();
            imagedestroy($verso);

            if (!empty($imgData)) {
                $fileName = preg_replace('/[^a-z0-9_\-]/i', '_', $nom . '_' . $prenom) . '_verso.png';
                $zip->addFromString($fileName, $imgData);
            }
        }

        // Marquer participant comme imprimé
        $stmt = $pdo->prepare("UPDATE participants SET isPrinted = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    $zip->close();

    // Vérifier que le ZIP contient au moins un fichier
    $zipCheck = new ZipArchive();
    if ($zipCheck->open($zipFile) === true) {
        if ($zipCheck->numFiles === 0) {
            $zipCheck->close();
            unlink($zipFile);
            die("Aucun badge généré.");
        }
        $zipCheck->close();
    }

    // Télécharger le ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="badges.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);

    // Supprimer le fichier temporaire
    unlink($zipFile);

    // Après téléchargement, retour à participants.php
    header('Refresh: 0; url=../participants.php');
    exit;
}

header('Location: ../participants.php');
exit;
