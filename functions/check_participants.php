<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div class="scanner-container">
    <!-- Scanner -->
    <div id="qr-reader">
        <!-- Overlay semi-transparent -->
        <div class="qr-overlay"></div>
        <!-- Cadre carré sans animation -->
        <div id="scan-frame"></div>
    </div>

    <!-- Résultat -->
    <div id="qr-result"></div>
    <button id="quit-scan-btn" onclick="window.location.href='../dashboard.php'">Quitter le scan</button>
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
    </div>
</div>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #6f6f6f;
        font-family: 'Segoe UI', sans-serif;
    }

    .scanner-container {
        width: 100vw;
        min-height: 100vh;
        margin: 0;
        padding: 0.5rem 0 2rem 0;
        background: #6f6f6f;
        color: white;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    #qr-reader {
        width: 94vw;
        max-width: 420px;
        aspect-ratio: 1 / 1;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid #000;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        margin: 1.2rem 0 0.7rem 0;
        background: #222;
    }

    .qr-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.32);
        z-index: 1;
        pointer-events: none;
    }

    #scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 70%;
        aspect-ratio: 1 / 1;
        transform: translate(-50%, -50%);
        border: 3px solid #38ef7d;
        border-radius: 0;
        box-sizing: border-box;
        pointer-events: none;
        z-index: 2;
    }

    #qr-result {
        margin-top: 1.2rem;
        text-align: center;
        font-weight: bold;
        font-size: 1.1rem;
        min-height: 24px;
        word-break: break-all;
        padding: 0 1rem;
    }

    #qr-result.success {
        color: #38ef7d;
    }

    #qr-result.error {
        color: #e74c3c;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, 0.2);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 200px;
        height: 200px;
        background: transparent;
        border-radius: 20px;
        text-align: center;
    }

    .success-icon,
    .error-icon {
        font-size: 80px;
        font-weight: bold;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 120px;
        height: 120px;
        border-radius: 50%;
    }

    .success-icon {
        color: #38ef7d;
        background: rgba(56, 239, 125, 0.1);
        border: 3px solid #38ef7d;
    }

    .error-icon {
        color: #e74c3c;
        background: rgba(231, 76, 60, 0.1);
        border: 3px solid #e74c3c;
    }

    .status-text {
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 2px;
    }

    .success-text {
        color: #38ef7d;
    }

    .error-text {
        color: #e74c3c;
    }

    #quit-scan-btn {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 20px;
        font-family: 'Segoe UI', sans-serif;
        transition: background 0.3s ease;
    }

    #quit-scan-btn:hover {
        background: #c0392b;
    }
</style>

<script>
    const evenementId = <?= $evenement_id ?>;
    let html5QrcodeScanner;

    function showSuccessModal() {
        document.getElementById('successModal').style.display = "flex";
        // Auto-fermer après 2 secondes
        setTimeout(() => {
            closeModal('successModal');
        }, 2000);
    }

    function showErrorModal(msg) {
        document.getElementById('errorModal').style.display = "flex";
        // Auto-fermer après 3 secondes
        setTimeout(() => {
            closeModal('errorModal');
        }, 3000);
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
        setTimeout(() => {
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
            });
        }, 500); // 0.5 seconde de délai
        document.getElementById('qr-result').innerText = "";
    }

    function onScanSuccess(decodedText, decodedResult) {
        document.getElementById('qr-result').innerText = decodedText;
        html5QrcodeScanner.stop().then(() => {
            fetch('verif_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'qr_code=' + encodeURIComponent(decodedText) + '&evenement_id=' + encodeURIComponent(evenementId)
                })
                .then(r => r.text())
                .then(data => {
                    if (data.includes("Présence enregistrée")) showSuccessModal();
                    else showErrorModal(data);
                }).catch(err => showErrorModal("Erreur : " + err));
        });
    }

    function startScanner(cameraId) {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        const qrBoxSize = Math.min(window.innerWidth * 0.66, 280); // 66% largeur écran, max 280px
        html5QrcodeScanner.start(
            cameraId, {
                fps: 10,
                qrbox: {
                    width: qrBoxSize,
                    height: qrBoxSize
                }
            },
            onScanSuccess
        ).catch(err => showErrorModal("Erreur caméra : " + err));
    }

    // Démarrage automatique sur la première caméra
    Html5Qrcode.getCameras().then(cameras => {
        if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
        else showErrorModal("Aucune caméra détectée.");
    }).catch(err => showErrorModal("Impossible d'accéder aux caméras : " + err));

    // Fermer les modals en cliquant sur l'arrière-plan
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal('successModal');
    });

    document.getElementById('errorModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal('errorModal');
    });
</script>