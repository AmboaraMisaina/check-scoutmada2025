<script src="https://unpkg.com/html5-qrcode"></script>

<?php
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 0;
?>

<div class="scanner-container">

    <!-- Sélecteur caméra -->
    <div class="camera-select-container">
        <label for="cameraSelect">📷 Choisir la caméra :</label>
        <select id="cameraSelect">
            <option value="">Chargement...</option>
        </select>
    </div>

    <!-- Scanner -->
    <div id="qr-reader">
        <div class="overlay"></div>
        <div id="scan-frame"></div>
    </div>

    <!-- Résultat -->
    <div id="qr-result"></div>
</div>

<!-- Modals succès / erreur -->
<div id="successModal" class="modal">
    <div class="modal-content success">
        <h2>✔ Checked succès !</h2>
        <p>Le participant a bien été enregistré.</p>
        <button onclick="closeModal('successModal')">Fermer</button>
    </div>
</div>

<div id="errorModal" class="modal">
    <div class="modal-content error">
        <h2>✖ Erreur</h2>
        <p id="errorMsg"></p>
        <button onclick="closeModal('errorModal')">Fermer</button>
    </div>
</div>

<style>
body { margin:0; font-family:'Segoe UI', sans-serif; background:#121212; color:white; }

.scanner-container {
    max-width: 480px;
    margin: 1rem auto;
    padding: 1rem;
    background: #1e1e1e;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    text-align: center;
}

/* Sélecteur caméra */
.camera-select-container { margin-bottom:1rem; }
.camera-select-container label { font-weight:bold; display:block; margin-bottom:0.5rem; }
.camera-select-container select {
    width: 90%;
    max-width: 300px;
    padding:0.6rem;
    border-radius:12px;
    border:none;
    font-size:1rem;
    background:#333;
    color:white;
}

/* Scanner */
#qr-reader {
    width: 100%;
    max-width: 400px;
    height: auto;
    aspect-ratio: 1/1; /* carré responsive */
    margin:0 auto;
    border-radius:20px;
    overflow:hidden;
    border:4px solid #38ef7d;
    position:relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
#qr-reader .overlay {
    position:absolute; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.4);
}
#scan-frame {
    position:absolute; top:50%; left:50%;
    width:70%; height:70%;
    transform: translate(-50%,-50%);
    border: 3px dashed #38ef7d;
    border-radius:15px;
    animation: pulse 1.5s infinite;
    box-sizing:border-box;
}

/* Résultat */
#qr-result { margin-top:1rem; font-weight:bold; font-size:1.2rem; color:#38ef7d; min-height:24px; word-break:break-all; }

/* Modals */
.modal {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100vw; height:100vh;
    background: rgba(0,0,0,0.5);
    z-index:9999;
    align-items:center;
    justify-content:center;
}
.modal-content {
    background:white;
    padding:2rem;
    border-radius:20px;
    text-align:center;
    max-width:90%;
    width:320px;
    box-shadow:0 8px 20px rgba(0,0,0,0.3);
}
.modal-content h2 { margin-bottom:1rem; font-size:1.3rem; }
.modal-content p { margin-bottom:1rem; font-size:1rem; }
.modal-content.success { border-top:4px solid #38ef7d; }
.modal-content.error { border-top:4px solid #e74c3c; }
.modal-content button {
    padding:0.7rem;
    border:none;
    border-radius:12px;
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

/* Responsive petits écrans */
@media(max-width:400px) {
    #qr-reader { max-width: 90%; aspect-ratio: 1/1; }
    .modal-content { width: 90%; padding:1.5rem; }
    #qr-result { font-size:1rem; }
}
</style>

<script>
const evenementId = <?= $evenement_id ?>;
let html5QrcodeScanner;

function showSuccessModal() { document.getElementById('successModal').style.display="flex"; }
function showErrorModal(msg) { document.getElementById('errorMsg').innerText=msg; document.getElementById('errorModal').style.display="flex"; }
function closeModal(modalId) { document.getElementById(modalId).style.display="none"; window.location.href="dashboard.php"; }

function onScanSuccess(decodedText, decodedResult){
    document.getElementById('qr-result').innerText = decodedText;
    html5QrcodeScanner.stop().then(()=>{
        fetch('verif_qr.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'qr_code='+encodeURIComponent(decodedText)+'&evenement_id='+encodeURIComponent(evenementId)
        }).then(r=>r.text())
          .then(data=>{ if(data.includes("Présence enregistrée")) showSuccessModal(); else showErrorModal(data); })
          .catch(err=>showErrorModal("Erreur : "+err));
    });
}

// Caméras
Html5Qrcode.getCameras().then(cameras=>{
    const select=document.getElementById('cameraSelect');
    select.innerHTML='';
    cameras.forEach(cam=>{
        const option=document.createElement('option');
        option.value=cam.id; option.text=cam.label||`Caméra ${cam.id}`;
        select.appendChild(option);
    });
    if(cameras.length>0) startScanner(cameras[cameras.length-1].id);

    select.addEventListener('change',()=>{ 
        if(html5QrcodeScanner) html5QrcodeScanner.stop().then(()=>startScanner(select.value));
    });
}).catch(err=>showErrorModal("Impossible d'accéder aux caméras : "+err));

function startScanner(cameraId){
    html5QrcodeScanner=new Html5Qrcode("qr-reader");
    html5QrcodeScanner.start(cameraId,{fps:10,qrbox:{width:250,height:250}},onScanSuccess)
    .catch(err=>showErrorModal("Erreur caméra : "+err));
}
</script>
