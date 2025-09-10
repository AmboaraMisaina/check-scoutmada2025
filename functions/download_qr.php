<?php
require_once 'functions.php';

// =======================
// CONFIGURATION GLOBALE - STANDARD BADGE
// =======================
$IMAGE_WIDTH = 400;            // Largeur totale de l'image (px)
$IMAGE_HEIGHT = 400;           // Hauteur totale de l'image (px)
$QR_SIZE = 300;                // Taille du QR code (px)
$TEXT_TOP = 20;                // Distance du texte depuis le haut (px)
$FONT_SIZE = 18;               // Taille du texte (px)
$FONT_FILE = __DIR__ . '/arial.ttf'; // Police TTF
$TEXT_COLOR = [0, 0, 0];       // Couleur du texte RGB
$LEFT_MARGIN = 20;             // Marge gauche pour texte et QR
$QR_TOP_MARGIN = 10;           // Marge entre texte et QR code

// =======================
// FONCTION GENERATION QR
// =======================
function generateQrWithText($qrData, $text) {
    global $IMAGE_WIDTH, $IMAGE_HEIGHT, $QR_SIZE, $FONT_SIZE, $FONT_FILE, $TEXT_COLOR, $LEFT_MARGIN, $TEXT_TOP, $QR_TOP_MARGIN;

    // Génère le QR code
    $qr_url = getQrCodeUrl($qrData, $QR_SIZE, $QR_SIZE);
    $qr_image = @imagecreatefrompng($qr_url);
    if (!$qr_image) return false;

    // Crée l'image finale
    $final_img = imagecreatetruecolor($IMAGE_WIDTH, $IMAGE_HEIGHT);

    // Fond blanc
    $white = imagecolorallocate($final_img, 255, 255, 255);
    imagefilledrectangle($final_img, 0, 0, $IMAGE_WIDTH, $IMAGE_HEIGHT, $white);

    // Couleur du texte
    $black = imagecolorallocate($final_img, $TEXT_COLOR[0], $TEXT_COLOR[1], $TEXT_COLOR[2]);

    // Dessine le texte aligné à gauche
    if (file_exists($FONT_FILE)) {
        imagettftext($final_img, $FONT_SIZE, 0, $LEFT_MARGIN, $TEXT_TOP + $FONT_SIZE, $black, $FONT_FILE, $text);
    } else {
        imagestring($final_img, 5, $LEFT_MARGIN, $TEXT_TOP, $text, $black);
    }

    // Place le QR code aligné à gauche, sous le texte
    $qr_x = $LEFT_MARGIN;
    $qr_y = $TEXT_TOP + $FONT_SIZE + $QR_TOP_MARGIN;
    imagecopy($final_img, $qr_image, $qr_x, $qr_y, 0, 0, $QR_SIZE, $QR_SIZE);

    imagedestroy($qr_image);
    return $final_img;
}

// =======================
// TELECHARGEMENT TOUS LES QR
// =======================
function downloadAllQRCodes($pdo) {
    $participants = getAllParticipants($pdo);
    if (!$participants) exit('No participants found.');

    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'qrcodes_') . '.zip';
    if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) exit("Unable to create ZIP file.");

    foreach ($participants as $participant) {
        $qrData = $participant['qr_code'];
        if (empty($qrData)) continue;

        $text = $participant['nom'] . ' ' . $participant['prenom'];
        $final_img = generateQrWithText($qrData, $text);
        if (!$final_img) continue;

        ob_start();
        imagepng($final_img);
        $imageData = ob_get_clean();

        $zip->addFromString('qr_' . $participant['id'] . '.png', $imageData);
        imagedestroy($final_img);
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="qr_codes_all.zip"');
    readfile($zipFile);
    unlink($zipFile);
    exit;
}

// =======================
// TELECHARGEMENT INDIVIDUEL
// =======================
if (isset($_GET['download_all'])) {
    downloadAllQRCodes($pdo);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    exit('Missing ID');
}

$participant = getParticipantById($pdo, $id);
if (!$participant) {
    http_response_code(404);
    exit('Participant not found');
}

$text = $participant['nom'] . ' ' . $participant['prenom'];
$final_img = generateQrWithText($participant['qr_code'], $text);
if (!$final_img) {
    http_response_code(500);
    exit('Error generating QR code.');
}

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="qr_code_' . $participant['id'] . '.png"');
imagepng($final_img);
imagedestroy($final_img);
?>
