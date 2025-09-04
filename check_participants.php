<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;

require_once 'functions.php';
checkAuth();

renderHeader("Scanner QR");
?>

<div class="container">
    <div class="page-header">
        <h2>Scanner le QR Code</h2>
        <p>Ouvrez la caméra arrière pour enregistrer la présence</p>
    </div>

    <div class="card" style="text-align:center;">
        <div id="qr-reader" style="
            width: 90%;
            max-width: 400px;
            height: 400px;
            margin: 2rem auto;
            border: 3px solid #667eea;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        ">
            <!-- Cadre overlay -->
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
        <div id="qr-result" style="margin-top:20px; font-weight:bold; color:#333;"></div>
    </div>
</div>

<!-- Modal succès -->
<div id="successModal" class="card" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center; display:flex;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:90%;">
        <h2 style="color:#38ef7d;">✔ Présence enregistrée !</h2>
        <p>Le participant a bien été enregistré.</p>
        <button onclick="closeModal('successModal')" class="btn btn-success" style="width:100%; margin-top:1rem;">Fermer</button>
    </div>
</div>

<!-- Modal erreur -->
<div id="errorModal" class="card" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center; display:flex;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:90%;">
        <h2 style="color:#e74c3c;">✖ Erreur</h2>
        <p id="errorMsg" style="color:#333;"></p>
        <button onclick="closeModal('errorModal')" class="btn btn-danger" style="width:100%; margin-top:1rem;">Fermer</button>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const evenementId = <?= $evenement_id ?>;

function showSuccessModal() {
    document.getElementById('successModal').style.display = "flex";
}

function showErrorModal(msg) {
    document.getElementById('errorMsg').innerText = msg;
    document.getElementById('errorModal').style.display = "flex";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
    window.location.href = "dashboard.php";
}

let html5QrcodeScanner;

// Quand la page charge, chercher les caméras et lancer la caméra arrière
Html5Qrcode.getCameras().then(cameras => {
    let rearCamera = cameras.find(cam => cam.label.toLowerCase().includes("back")) || cameras[0];
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    html5QrcodeScanner.start(
        rearCamera.id, 
        { fps: 10, qrbox: { width: 250, height: 250 } },
        decodedText => {
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
            .catch(error => {
                showErrorModal("Erreur : " + error);
            });
        }
    );
}).catch(err => {
    showErrorModal("Impossible d'accéder à la caméra : " + err);
});
</script>

<?php renderFooter(); ?>
