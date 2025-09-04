<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div class="container" style="max-width: 500px; margin: 2rem auto;">

    <!-- Sélecteur caméra -->
    <div style="margin-bottom:1rem; text-align:center;">
        <label for="cameraSelect" style="font-weight:bold; margin-bottom:0.5rem; display:block;">Choisir la caméra :</label>
        <select id="cameraSelect" style="width: 90%; max-width: 300px; padding:0.5rem; border-radius:8px; border:1px solid #ccc; font-size:1rem;">
            <option value="">Chargement...</option>
        </select>
    </div>

    <!-- Scanner -->
    <div id="qr-reader" style="
        width: 90%;
        max-width: 400px;
        height: 400px;
        margin: 0 auto;
        border: 3px solid #38ef7d;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    ">
        <div style="
            position: absolute;
            top: 50%;
            left: 50%;
            width: 70%;
            height: 70%;
            transform: translate(-50%, -50%);
            border: 2px dashed #fff;
            border-radius: 10px;
            pointer-events: none;
        "></div>
    </div>

    <div id="qr-result" style="margin-top:20px; font-weight:bold; text-align:center; color:#333;"></div>
</div>

<!-- Modals identiques à ton code -->

<script>
const evenementId = <?= $evenement_id ?>;
let html5QrcodeScanner;

// Afficher modals
function showSuccessModal() { document.getElementById('successModal').style.display = "flex"; }
function showErrorModal(msg) { document.getElementById('errorMsg').innerText = msg; document.getElementById('errorModal').style.display = "flex"; }
function closeModal(modalId) { document.getElementById(modalId).style.display = "none"; window.location.href = "dashboard.php"; }

// Callback de scan
function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('qr-result').innerText = "Lecture : " + decodedText;
    html5QrcodeScanner.stop().then(() => {
        fetch('verif_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'qr_code=' + encodeURIComponent(decodedText) + '&evenement_id=' + encodeURIComponent(evenementId)
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Présence enregistrée")) {
                showSuccessModal();
            } else {
                showErrorModal(data);
            }
        })
        .catch(error => { showErrorModal("Erreur : " + error); });
    });
}

// Charger les caméras et remplir le select
Html5Qrcode.getCameras().then(cameras => {
    const select = document.getElementById('cameraSelect');
    select.innerHTML = '';
    cameras.forEach(cam => {
        const option = document.createElement('option');
        option.value = cam.id;
        option.text = cam.label || `Caméra ${cam.id}`;
        select.appendChild(option);
    });

    // Lancer automatiquement la dernière caméra
    if(cameras.length > 0) startScanner(cameras[cameras.length - 1].id);

    // Changer caméra selon sélection
    select.addEventListener('change', () => {
        if(html5QrcodeScanner) html5QrcodeScanner.stop().then(() => startScanner(select.value));
    });
}).catch(err => { showErrorModal("Impossible d'accéder aux caméras : " + err); });

// Fonction de démarrage du scanner
function startScanner(cameraId) {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    html5QrcodeScanner.start(
        cameraId,
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    ).catch(err => { showErrorModal("Erreur caméra : " + err); });
}
</script>
