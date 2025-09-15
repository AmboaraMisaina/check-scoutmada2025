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
    <div id="photo-wrapper" style="width:97vw; max-width:750px; aspect-ratio:0.97/1; display:none; align-items:center; justify-content:center; position:relative;">
        <img id="participant-img" src="" alt="Photo participant"
            style="width:100%; height:100%; object-fit:cover; border-radius:24px; border:4px solid #fff; display:none; background:#eee;">
        <button onclick="closePhoto()" style="position:absolute; top:10px; right:10px; background:#ff4757; color:white; border:none; border-radius:50%; width:80px; height:80px; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer; box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            ✕
        </button>
        <p id="participant-name"></p>
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
        <div class="success-icon">✓</div>
        <div class="status-text success-text">OK</div>
    </div>
</div>

<div id="errorModal" class="modal">
    <div class="modal-content error">
        <div class="error-icon">✗</div>
        <div class="status-text error-text">KO</div>
        <div class="status-text message-text" id="message-text"></div>
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

    #participant-name {
        color: #000;
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

    .message-text {
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
</style>

<script>
    const evenementId = <?= $evenement_id ?>;
    let html5QrcodeScanner;

    function showSuccessModal() {
        document.getElementById('successModal').style.display = "flex";
        setTimeout(() => {
            closeModal('successModal');
        }, 3000);
    }

    function showErrorModal(msg) {
        document.getElementById('errorModal').style.display = "flex";
        document.getElementById('message-text').innerText = msg;
        setTimeout(() => {
            closeModal('errorModal');
        }, 3000);
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
        document.getElementById('qr-result').innerText = "";
    }

    function closePhoto() {
        const bloc = document.getElementById('photo-wrapper');
        const img = document.getElementById('participant-img');
        img.src = "";
        img.style.display = 'none';
        bloc.style.display = 'none';
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById('qr-result').innerText = decodedText;
        html5QrcodeScanner.stop().then(() => {
            fetch('verif_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'qr_code=' + encodeURIComponent(decodedText) + '&evenement_id=' + encodeURIComponent(evenementId)
            })
            .then(r => r.json()) // ← on renvoie JSON maintenant
            .then(data => {
                if (data.success) {
                    // afficher la photo
                    // if (data.photo_path) {
                        const img = document.getElementById('participant-img');
                        const participantName = document.getElementById('participant-name');
                        const bloc = document.getElementById('photo-wrapper');
                        participantName.innerText = data.message || "";
                        img.src = "../" + data.photo_path;
                        img.style.display = 'block';
                        bloc.style.display = 'flex';
                    // }
                    showSuccessModal();
                } else {
                    // if (data.photo_path) {
                        const img = document.getElementById('participant-img');
                        const bloc = document.getElementById('photo-wrapper');
                        img.src = "../" + data.photo_path;
                        img.style.display = 'block';
                        bloc.style.display = 'flex';
                    // }
                    showErrorModal(data.message || " Error");
                }
            })
            .catch(err => showErrorModal("Error: " + err));
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