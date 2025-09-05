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
    <!-- Sélecteur caméra -->
    <div style="margin-bottom:1rem; text-align:center; display:none;">
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
    <!-- Scanner -->
    <div id="qr-reader" style="
        width: 100%;
        height: 500px; /* tu peux augmenter ou diminuer selon ton besoin */
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        border: 4px solid #000000ff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        background: #000; /* fond noir si pas encore de caméra */
    ">
        <!-- Cadre carré -->
        <div id="scan-frame" style="
            position: absolute;
            top: 50%;
            left: 50%;
            width: 70%;
            aspect-ratio: 1 / 1;   /* carré parfait */
            transform: translate(-50%, -50%);
            border: 3px dashed #ffffff; /* blanc bien visible */
            border-radius: 15px;
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
    padding:3rem;                 /* plus d’espace intérieur */
    border-radius:20px;           /* coins arrondis */
    text-align:center;
    max-width:600px;              /* largeur max plus grande */
    width:97%;                    /* occupe presque tout l’écran sur mobile */
    box-shadow:0 12px 30px rgba(0,0,0,0.35);
    font-family: 'Segoe UI', sans-serif;
    font-size:1.4rem;             /* texte un peu plus grand */
}
.modal-content h2 {
    margin-bottom:1.5rem;
    font-size:1.9rem;             /* titre plus gros */
    color:#333;
}
.modal-content p {
    margin-bottom:1.5rem;
    font-size:1.3rem;
    color:#555;
}
.modal-content button {
    padding:1rem 2rem;           /* bouton plus large et haut */
    font-size:1.3rem;            /* texte bouton agrandi */
    border:none;
    border-radius:10px;
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
