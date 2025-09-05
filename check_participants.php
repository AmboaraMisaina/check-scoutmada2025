<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div class="scanner-container" style="
    max-width: 500px;
    margin: 2rem auto;
    padding: 1rem;
    background: #6f6f6fff;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(94, 94, 94, 0.3);
    color: white;
    font-family: 'Segoe UI', sans-serif;
">

    <!-- Scanner -->
    <div id="qr-reader" style="
        width: 100%;
        height: 400px;
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        border: 4px solid #000000ff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    ">
        <!-- Overlay semi-transparent -->
        <div style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
        "></div>
        <!-- Cadre animé -->
        <div id="scan-frame" style="
            position: absolute;
            top: 50%;
            left: 50%;
            width: 70%;
            height: 70%;
            transform: translate(-50%, -50%);
            border: 3px dashed #ffffffff;
            border-radius: 15px;
            animation: pulse 1.5s infinite;
            box-sizing: border-box;
        "></div>
    </div>

    <!-- Résultat -->
    <div id="qr-result" style="
        margin-top:1rem;
        text-align:center;
        font-weight:bold;
        font-size:1.1rem;
        color:#38ef7d;
        min-height:24px;
    "></div>
</div>

<!-- Modals succès / erreur -->
<div id="successModal" class="modal">
    <div class="modal-content" style="border-top:4px solid #38ef7d;">
        <h2>✔ Checked succès !</h2>
        <p>Le participant a bien été enregistré.</p>
        <button onclick="closeModal('successModal')">Fermer</button>
    </div>
</div>

<div id="errorModal" class="modal">
    <div class="modal-content" style="border-top:4px solid #e74c3c;">
        <h2>✖ Erreur</h2>
        <p id="errorMsg"></p>
        <button onclick="closeModal('errorModal')">Fermer</button>
    </div>
</div>

<script>
const evenementId = <?= $evenement_id ?>;
let html5QrcodeScanner;

function showSuccessModal() { document.getElementById('successModal').style.display = "flex"; }
function showErrorModal(msg) { document.getElementById('errorMsg').innerText = msg; document.getElementById('errorModal').style.display = "flex"; }
function closeModal(modalId) { document.getElementById(modalId).style.display = "none"; window.location.href = "dashboard.php"; }

function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('qr-result').innerText = decodedText;
    html5QrcodeScanner.stop().then(() => {
        fetch('verif_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'qr_code=' + encodeURIComponent(decodedText) + '&evenement_id=' + encodeURIComponent(evenementId)
        })
        .then(r => r.text())
        .then(data => {
            if(data.includes("Présence enregistrée")) showSuccessModal();
            else showErrorModal(data);
        }).catch(err => showErrorModal("Erreur : " + err));
    });
}

// Utiliser directement la caméra arrière (si dispo)
Html5Qrcode.getCameras().then(cameras => {
    if (cameras && cameras.length) {
        let backCamera = cameras.find(cam => cam.label.toLowerCase().includes("back"));
        let cameraId = backCamera ? backCamera.id : cameras[cameras.length - 1].id;
        startScanner(cameraId);
    } else {
        showErrorModal("Aucune caméra détectée !");
    }
}).catch(err => showErrorModal("Impossible d'accéder aux caméras : " + err));

function startScanner(cameraId) {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    const qrBoxSize = Math.min(350, document.getElementById('qr-reader').offsetWidth * 0.7);
    html5QrcodeScanner.start(
        cameraId,
        { fps: 10, qrbox: { width: qrBoxSize, height: qrBoxSize } },
        onScanSuccess
    ).catch(err => showErrorModal("Erreur caméra : " + err));
}
</script>
