<?php
require 'functions.php';
checkAuthOrRedirect();

if ($_SESSION['role'] !== 'admin' &&  $_SESSION['role'] !== 'checkin') {
    include '../includes/header.php';
    ?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Forbidden</h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;">You do not have the necessary rights to access this page.</p>
            <a href="../checkin.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Back
            </a>
        </div>
    </div>
    <?php
    renderFooter();
    exit;
}
?>

<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;

    // Récupérer les infos de l'événement
    $evenement = null;
    if ($evenement_id) {
        $evenement = getEvenementById($pdo, $evenement_id);
    }
?>
<?php if ($evenement): ?>
    <div style="text-align:center; margin-top:2rem; margin-bottom:1.5rem;">
        <h2 style="margin-bottom:0.5rem; font-size:2.5rem; font-weight:bold; color:#fff;">
            <?= htmlspecialchars($evenement['titre']) ?>
        </h2>
        <div style="font-size:1.5rem; color:#f1f1f1; font-weight:500;">
            <?php
                if (!empty($evenement['date_evenement'])) {
                    $date = date('d/m/Y', strtotime($evenement['date_evenement']));
                    echo "Date : $date";
                }
                if (!empty($evenement['horaire_debut']) && !empty($evenement['horaire_fin'])) {
                    echo " &mdash; {$evenement['horaire_debut']} à {$evenement['horaire_fin']}";
                }
            ?>
        </div>
    </div>
<?php endif; ?>
<div id="participant-photo" style="margin: 1.5rem auto 0 auto; display: flex; justify-content: center;">
    <div id="photo-wrapper" style="width:97vw; max-width:750px; aspect-ratio:0.97/1; display:none; align-items:center; justify-content:center;">
        <img id="participant-img" src="" alt="Photo participant"
            style="width:100%; height:100%; object-fit:cover; border-radius:24px; border:4px solid #fff; display:none; background:#eee;">
    </div>
</div>
<div class="scanner-container">
    <!-- Scanner -->
    <div id="qr-reader">
        <!-- Overlay semi-transparent -->
        <div class="qr-overlay"></div>
        <!-- Cadre carré stylé -->
        <div id="scan-frame"></div>
    </div>

    <!-- Résultat -->
    <div id="qr-result"></div>
    <button id="quit-scan-btn" onclick="window.location.href='../checkin.php'">Quit scan</button>
</div>

<!-- Modals succès / erreur -->
<div id="successModal" class="modal">
    <div class="modal-content success">
        <span class="close-modal" onclick="closeModal('successModal')">&times;</span>
        <div class="modal-photo-bg" id="success-photo-bg"></div>
        <div class="success-icon">✓</div>
        <div class="status-text success-text">OK</div>
    </div>
</div>

<div id="errorModal" class="modal">
    <div class="modal-content error">
        <span class="close-modal" onclick="closeModal('errorModal')">&times;</span>
        <div class="modal-photo-bg" id="error-photo-bg"></div>
        <div class="error-icon">✗</div>
        <div class="status-text error-text">KO</div>
    </div>
</div>



<style>
    /* 🌟 Fond général */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #8e44ad, #d16ba5);
        color: white;
        overflow-x: hidden;
    }

    /* Conteneur principal */
    .scanner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1rem;
        backdrop-filter: blur(10px);
        box-sizing: border-box;
    }


    /* 📱 Scanner */
    #qr-reader {
        width: 97vw;
        max-width: 750px;
        aspect-ratio: 0.97 / 1;
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        margin: 1.5rem auto;
        /* centré horizontalement */
        background: rgba(34, 34, 34, 0.6);
        backdrop-filter: blur(12px);
    }


    /* Overlay semi-transparent */
    .qr-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.25);
        z-index: 1;
        pointer-events: none;
    }

    /* Cadre de scan stylé avec glow + pulse */
    #scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 70%;
        aspect-ratio: 1 / 1;
        transform: translate(-50%, -50%);
        border: 4px solid #00ff88;
        border-radius: 16px;
        box-shadow: 0 0 20px rgba(0, 255, 136, 0.7);
        animation: pulse 2s infinite;
        z-index: 2;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.6);
        }

        50% {
            box-shadow: 0 0 40px rgba(0, 255, 136, 1);
        }

        100% {
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.6);
        }
    }

    /* Résultat texte */
    #qr-result {
        margin-top: 1rem;
        text-align: center;
        font-weight: bold;
        font-size: 1.2rem;
        min-height: 28px;
        word-break: break-all;
        padding: 0 1rem;
    }

    /* Modals */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    /* Contenu modal */
    .modal-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        animation: popIn 0.5s ease forwards;
        position: relative;
    }

    @keyframes popIn {
        0% {
            transform: scale(0.6);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .success-icon,
    .error-icon {
        font-size: 30vw;
        width: 30vw;
        height: 30vw;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        animation: glow 1.5s infinite alternate;
        z-index: 10000;
    }

    @keyframes glow {
        from {
            transform: scale(1);
        }

        to {
            transform: scale(1.05);
        }
    }

    /* Succès */
    .success-icon {
        color: #00ff88;
        background: rgba(0, 255, 136, 0.15);
        border: 5px solid #00ff88;
        box-shadow: 0 0 40px rgba(0, 255, 136, 0.7);
    }

    .success-text {
        font-size: 10vw;
        font-weight: bold;
        color: #00ff88;
        letter-spacing: 3px;
    }

    /* Erreur */
    .error-icon {
        color: #ff4757;
        background: rgba(255, 71, 87, 0.15);
        border: 5px solid #ff4757;
        box-shadow: 0 0 40px rgba(255, 71, 87, 0.7);
    }

    .error-text {
        font-size: 10vw;
        font-weight: bold;
        color: #ff4757;
        letter-spacing: 3px;
    }

    /* 🔘 Bouton premium */
    #quit-scan-btn {
        background: linear-gradient(135deg, #ff6b6b, #c0392b);
        color: white;
        border: none;
        padding: 14px 32px;
        font-size: 16px;
        border-radius: 50px;
        cursor: pointer;
        margin-top: 20px;
        font-family: 'Segoe UI', sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
    }

    #quit-scan-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(231, 76, 60, 0.6);
    }

    .close-modal {
        position: absolute;
        top: 18px;
        right: 24px;
        font-size: 2.2rem;
        color: #fff;
        background: rgba(0,0,0,0.25);
        border-radius: 50%;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10001;
        transition: background 0.2s;
    }

    .close-modal:hover {
        background: rgba(0,0,0,0.45);
    }

    .modal-photo-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 0;
        opacity: 0.25;
        background: center center/cover no-repeat;
        border-radius: 24px;
        filter: blur(2px) grayscale(0.2);
    }

    .modal-content > .success-icon,
    .modal-content > .error-icon,
    .modal-content > .status-text {
        position: relative;
        z-index: 1;
    }
</style>

<script>
    const evenementId = <?= $evenement_id ?>;
    let html5QrcodeScanner;
    let lastPhotoPath = null;

    function showSuccessModal(photoPath) {
        if (photoPath) {
            document.getElementById('success-photo-bg').style.backgroundImage = `url('${photoPath}')`;
        } else {
            document.getElementById('success-photo-bg').style.backgroundImage = '';
        }
        document.getElementById('successModal').style.display = "flex";
        // Pas d'auto-close, l'utilisateur ferme avec la croix
    }

    function showErrorModal(msg, photoPath) {
        if (photoPath) {
            document.getElementById('error-photo-bg').style.backgroundImage = `url('${photoPath}')`;
        } else {
            document.getElementById('error-photo-bg').style.backgroundImage = '';
        }
        document.getElementById('errorModal').style.display = "flex";
        // Pas d'auto-close, l'utilisateur ferme avec la croix
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
        setTimeout(() => {
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
            });
        }, 500);
        document.getElementById('qr-result').innerText = "";
        // Cache la photo du participant
        document.getElementById('participant-img').style.display = 'none';
        document.getElementById('photo-wrapper').style.display = 'none';
    }

    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById('qr-result').innerText = decodedText;
        html5QrcodeScanner.stop().then(() => {
            fetch('verif_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'qr_code=' + encodeURIComponent(decodedText) + '&evenement_id=' + encodeURIComponent(evenementId)
            })
            .then(r => r.json())
            .then(data => {
                let photoPath = data.photo_path ? "../" + data.photo_path : "";
                lastPhotoPath = photoPath;
                if (photoPath) {
                    const img = document.getElementById('participant-img');
                    const bloc = document.getElementById('photo-wrapper');
                    img.src = photoPath;
                    img.style.display = 'block';
                    bloc.style.display = 'flex';
                }
                if (data.success) {
                    showSuccessModal(photoPath);
                } else {
                    showErrorModal(data.message || " Error", photoPath);
                }
            })
            .catch(err => showErrorModal("Error: " + err, lastPhotoPath));
        });
    }

    function startScanner(cameraId) {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        const scannerWidth = document.getElementById('qr-reader').clientWidth;
        const bloc = document.getElementById('photo-wrapper');
        bloc.style.display = 'none';
        const qrBoxSize = scannerWidth * 0.7;

        html5QrcodeScanner.start(
            cameraId, {
                fps: 10,
                qrbox: {
                    width: qrBoxSize,
                    height: qrBoxSize
                } // carré
            },
            onScanSuccess
        ).catch(err => showErrorModal("Camera error: " + err));
    }

    // Démarrage auto
    Html5Qrcode.getCameras().then(cameras => {
        if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
        else showErrorModal("No camera detected.");
    }).catch(err => showErrorModal("Unable to access cameras: " + err));

    // Fermer modals en cliquant dessus
    document.getElementById('successModal').addEventListener('click', e => {
        if (e.target === document.getElementById('successModal')) closeModal('successModal');
    });

    document.getElementById('errorModal').addEventListener('click', e => {
        if (e.target === document.getElementById('errorModal')) closeModal('errorModal');
    });
</script>