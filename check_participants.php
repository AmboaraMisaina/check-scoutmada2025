<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div class="scanner-container" style="
    max-width: 500px;
    margin: 2rem auto;
    padding: 1rem;
    background: #1e1e1e;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    color: white;
    font-family: 'Segoe UI', sans-serif;
">

    <!-- Sélecteur caméra -->
    <div style="margin-bottom:1rem; text-align:center;">
        <label for="cameraSelect" style="font-weight:bold; margin-bottom:0.5rem; display:block;">📷 Choisir la caméra :</label>
        <select id="cameraSelect" style="
            width: 80%;
            max-width: 300px;
            padding:0.6rem;
            border-radius:12px;
            border:none;
            font-size:1rem;
            background:#333;
            color:white;
        ">
            <option value="">Chargement...</option>
        </select>
    </div>

    <!-- Scanner -->
    <div id="qr-reader" style="
        width: 100%;
        height: 400px;
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        border: 4px solid #38ef7d;
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
            border: 3px dashed #38ef7d;
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

<style>
.modal {
    display:none;
    position:fixed;
    top:0; left:0; width:100vw; height:100vh;
    background: rgba(0,0,0,0.5);
    z-index:9999;
    align-items:center;
    justify-content:center;
}

.modal-content {
    background:white;
    padding:2rem;
    border-radius:15px;
    text-align:center;
    max-width:350px;
    width:90%;
    box-shadow:0 8px 20px rgba(0,0,0,0.3);
    font-family: 'Segoe UI', sans-serif;
}

.modal-content h2 { margin-bottom:1rem; color:#333; }
.modal-content p { margin-bottom:1rem; color:#555; }
.modal-content button {
    padding:0.6rem 1.2rem;
    border:none;
    border-radius:8px;
    background:#38ef7d;
    color:white;
    font-weight:bold;
    cursor:pointer;
    width:100%;
}

.modal-content button:hover { background:#2ecc71; }

@keyframes pulse {
    0% { box-shadow: 0 0 10px #38ef7d; }
    50% { box-shadow: 0 0 20px #38ef7d; }
    100% { box-shadow: 0 0 10px #38ef7d; }
}
</style>

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

// Charger caméras et remplir le select
Html5Qrcode.getCameras().then(cameras => {
    const select = document.getElementById('cameraSelect');
    select.innerHTML = '';
    cameras.forEach(cam => {
        const option = document.createElement('option');
        option.value = cam.id;
        option.text = cam.label || `Caméra ${cam.id}`;
        select.appendChild(option);
    });

    if(cameras.length>0) startScanner(cameras[cameras.length-1].id);

    select.addEventListener('change', () => {
        if(html5QrcodeScanner) html5QrcodeScanner.stop().then(() => startScanner(select.value));
    });
}).catch(err => showErrorModal("Impossible d'accéder aux caméras : "+err));

function startScanner(cameraId) {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    const qrBoxSize = Math.min(350, document.getElementById('qr-reader').offsetWidth * 0.7); // 70% largeur, max 350px
    html5QrcodeScanner.start(
        cameraId,
        { fps: 10, qrbox: { width: qrBoxSize, height: qrBoxSize } },
        onScanSuccess
    ).catch(err => showErrorModal("Erreur caméra : " + err));
}
</script>
