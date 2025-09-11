<?php
// filepath: c:\xampp\htdocs\check-scoutmada2025\download_badge.php
$file = $_GET['file'] ?? '';
$pdfPath = __DIR__ . '/../temp_badges/' . basename($file);

if (!file_exists($pdfPath)) {
    echo "<h2 style='color:red;text-align:center;margin-top:3rem;'>Badge file not found.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Badges</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { background: #f7f7fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container {
            max-width: 420px;
            margin: 4rem auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(80,80,120,0.10);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .icon {
            font-size: 3.5rem;
            color: #38ef7d;
            margin-bottom: 1.2rem;
        }
        h2 { margin-bottom: 0.5rem; color: #222; }
        p { color: #555; margin-bottom: 2rem; }
        .btn-download {
            display: inline-block;
            padding: 1rem 2.2rem;
            background: linear-gradient(90deg,#38ef7d 0%,#11998e 100%);
            color: #fff;
            font-size: 1.15rem;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(56,239,125,0.10);
            transition: background 0.2s;
        }
        .btn-download:hover {
            background: linear-gradient(90deg,#11998e 0%,#38ef7d 100%);
        }
        .info {
            margin-top: 1.5rem;
            color: #888;
            font-size: 0.98rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🎫</div>
        <h2>Your badges are ready!</h2>
        <p>You can now download and print the generated badges PDF.</p>
        <a class="btn-download" href="<?= '../temp_badges/' . htmlspecialchars($file) ?>" download>⬇️ Download Badges PDF</a>
        <div class="info">
            If the PDF does not open on your device, try with a computer.<br>
            <span style="font-size:0.9em;">(File: <?= htmlspecialchars($file) ?>)</span>
        </div>
    </div>
</body>
</html>