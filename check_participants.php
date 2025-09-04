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

<!-- Modal succès -->
<div id="successModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center;">
        <h2 style="color:#38ef7d;">✔ Checked succès !</h2>
        <p>Le participant a bien été enregistré.</p>
        <button onclick="closeModal('successModal')" style="margin-top:1rem; padding:0.5rem 1rem; border:none; border-radius:5px; background:#38ef7d; color:white; cursor:pointer;">Fermer</button>
    </div>
</div>

<!-- Modal erreur -->
<div id="errorModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center;">
        <h2 style="color:#e74c3c;">✖ Erreur</h2>
        <p id="errorMsg" style="color:#333;"></p>
        <button onclick="closeModal('errorModal')" style="margin-top:1rem; padding:0.5rem 1rem; border:none; border-radius:5px; background:#e74c3c; color:white; cursor:pointer;">Fermer</button>
    </div>
</div>

<script>
const evenementId = <?= $evenement_id ?>;
let html5QrcodeScanner;

// Afficher modals
function showSuccessModal() { document.getElementById('successModal').style.display = "flex"; }
function showErrorModal(msg) { document.getElementById('errorMsg').innerText = msg; document.getElementById('errorModal').style.display = "flex"; }
function closeModal(modalId) { document.getElementById(modalId).style.display = "none"; window.location.href = "dashboard.php"; }

// Fonction de scan
function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('qr-result').innerText = "Lecture : " + decodedText;
    html5QrcodeScanner.clear();

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

    // Lancer automatiquement la première caméra
    if(cameras.length > 0) startScanner(cameras[0].id);

    // Changer caméra selon sélection
    select.addEventListener('change', () => {
        if(html5QrcodeScanner) html5QrcodeScanner.stop().then(() => startScanner(select.value));
    });
}).catch(err => { showErrorModal("Impossible d'accéder aux caméras : " + err); });

// Fonction de démarrage du scan
function startScanner(cameraId) {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    html5QrcodeScanner.start(
        cameraId,
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    );
}
</script>
