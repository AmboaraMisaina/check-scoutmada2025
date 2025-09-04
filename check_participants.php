<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div id="qr-reader" style="width:90%; max-width:400px; margin:auto; margin-top:2rem;"></div>
<div id="qr-result" style="margin-top:20px; font-weight:bold; text-align:center; color:#333;"></div>

<!-- Modal succès -->
<div id="successModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:90%;">
        <h2 style="color:#38ef7d;">✔ Checked succès !</h2>
        <p>Le participant a bien été enregistré.</p>
        <button onclick="closeModal('successModal')" style="margin-top:1rem; padding:0.7rem 1.2rem; border:none; border-radius:5px; background:#38ef7d; color:white; cursor:pointer; width:100%;">Fermer</button>
    </div>
</div>

<!-- Modal erreur -->
<div id="errorModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem 5%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:90%;">
        <h2 style="color:#e74c3c;">✖ Erreur</h2>
        <p id="errorMsg" style="color:#333;"></p>
        <button onclick="closeModal('errorModal')" style="margin-top:1rem; padding:0.7rem 1.2rem; border:none; border-radius:5px; background:#e74c3c; color:white; cursor:pointer; width:100%;">Fermer</button>
    </div>
</div>

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
    .catch(error => {
        showErrorModal("Erreur : " + error);
    });
}

html5QrcodeScanner = new Html5QrcodeScanner(
    "qr-reader", 
    { fps: 10, qrbox: { width: 250, height: 250 } }, 
    /* verbose= */ false
);
html5QrcodeScanner.render(onScanSuccess);
</script>