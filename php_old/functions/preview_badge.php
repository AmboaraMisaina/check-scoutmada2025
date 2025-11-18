<?php
// filepath: c:\xampp\htdocs\check-scoutmada2025\preview_badge.php
$file = $_GET['file'] ?? '';
$pdfPath = __DIR__ . '/../temp_badges/' . basename($file);

if (!file_exists($pdfPath)) {
    echo "<h2 style='color:red;text-align:center;margin-top:3rem;'>Badge file not found.</h2>";
    exit;
}
$pdfUrl = '../temp_badges/' . rawurlencode($file);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Badges</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { background: #f7f7fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container {
            max-width: 900px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(80,80,120,0.10);
            padding: 1.5rem 1rem 2rem 1rem;
            text-align: center;
        }
        iframe {
            width: 100%;
            min-height: 80vh;
            border: 2px solid #eee;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            background: #fafafa;
        }
        .info {
            color: #888;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        .btn {
            padding: 0.8rem 2rem;
            background: #38ef7d;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Print your badges</h2>
        <div class="info">
            The badges are ready. The print dialog will open automatically.<br>
            If not, use the button below.
        </div>
        <iframe id="badgeFrame" style="display: none;" src="<?= htmlspecialchars($pdfUrl) ?>"></iframe>
        <br>
        <button class="btn" onclick="printIframe()">Print badges</button>
    </div>
    <script>
    function printIframe() {
        var iframe = document.getElementById('badgeFrame');
        if (iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    }
    // Lancer l'impression automatiquement sans attendre le chargement de l'iframe
    window.onload = function() {
      printIframe();
    };

    </script>
</body>
</html>