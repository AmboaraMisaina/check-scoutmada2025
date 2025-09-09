<?php
require_once 'functions.php';
checkAuthOrRedirect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['print_ids'])) {
    $ids = array_map('intval', $_POST['print_ids']);

    // Création d'un fichier temporaire ZIP
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

        // --- Générer le recto ---
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

        // --- Générer le verso ---
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

    // Envoyer le ZIP au navigateur
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="badges.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);

    // Supprimer le fichier temporaire
    unlink($zipFile);
    exit;
}

// Si pas de POST → retour
header('Location: ../participants.php');
exit;
    