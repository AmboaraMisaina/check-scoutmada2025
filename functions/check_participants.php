<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

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
    <button id="quit-scan-btn" onclick="window.location.href='../checkin.php'">Quitter le scan</button>
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
    /* 🌟 Fond général */
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #4b6cb7, #182848);
        color: white;
        overflow-x: hidden;
    }

    /* Conteneur principal */
    .scanner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        /* centré verticalement */
        min-height: 100vh;
        padding: 1rem;
        backdrop-filter: blur(10px);
        box-sizing: border-box;
    }


    /* 📱 Scanner */
    #qr-reader {
        width: 90vw;
        max-width: 600px;
        aspect-ratio: 0.9 / 1;
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
        font-size: 100vw;
        width: 100vw;
        height: 100vw;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        animation: glow 1.5s infinite alternate;
        z-index: 10000;
        /* s'assurer que ça soit au-dessus de tout */
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
        font-size: 20vw;
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
        font-size: 20vw;
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
        }, 2000);
    }

    function showErrorModal(msg) {
        document.getElementById('errorModal').style.display = "flex";
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
        }, 500);
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
                })
                .catch(err => showErrorModal("Erreur : " + err));
        });
    }

    function startScanner(cameraId) {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        const scannerWidth = document.getElementById('qr-reader').clientWidth;
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
        ).catch(err => showErrorModal("Erreur caméra : " + err));
    }

    // Démarrage auto
    Html5Qrcode.getCameras().then(cameras => {
        if (cameras.length > 0) startScanner(cameras[cameras.length - 1].id);
        else showErrorModal("Aucune caméra détectée.");
    }).catch(err => showErrorModal("Impossible d'accéder aux caméras : " + err));

    // Fermer modals en cliquant dessus
    document.getElementById('successModal').addEventListener('click', e => {
        if (e.target === document.getElementById('successModal')) closeModal('successModal');
    });

    document.getElementById('errorModal').addEventListener('click', e => {
        if (e.target === document.getElementById('errorModal')) closeModal('errorModal');
    });
</script>