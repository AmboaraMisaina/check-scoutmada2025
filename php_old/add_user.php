<?php
require_once 'functions/functions.php';
checkAuthOrRedirect();
if ($_SESSION['role'] !== 'admin') {
    include 'includes/header.php';
    ?>
    <div style="display:flex; align-items:center; justify-content:center; height:100vh; background:#f9f9f9;">
        <div style="background:white; padding:2rem 3rem; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;">
            <h2 style="color:#e74c3c; margin-bottom:1rem;">🚫 Forbidden</h2>
            <p style="font-size:1.1rem; margin-bottom:1.5rem;">You do not have the necessary rights to access this page.</p>
            <a href="checkin.php" style="padding:0.7rem 1.2rem; background:#3498db; color:white; border-radius:5px; text-decoration:none; font-weight:bold;">
                ⬅ Back
            </a>
        </div>
    </div>
    <?php
    renderFooter();
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if (!$username || !$password || !in_array($role, ['admin', 'checkin','registration', 'kit'])) {
        $message = '<div class="alert alert-error" style="background:#fdeaea; color:#e74c3c; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">Please fill all fields correctly.</div>';
    } else {
        if (createAdmin($pdo, $username, $password, $role)) {
            $message = '<div class="alert alert-success" style="background:#eafaf1; color:#27ae60; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">New user added successfully!</div>';
        } else {
            $message = '<div class="alert alert-error" style="background:#fdeaea; color:#e74c3c; padding:0.8rem 1rem; border-radius:6px; margin-bottom:1rem;">Error while adding user.</div>';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width:420px; margin:2rem auto;">
    <div class="page-header" style="margin-bottom:2rem;">
        <h2 style="margin-bottom:0.5rem;">Add Administrator</h2>
        <p style="color:#555;">Fill in the information below</p>
    </div>

    <div class="card" style="padding:2rem; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
        <?= $message ?>
        <form method="post" style="display:flex;flex-direction:column;gap:1.2rem;">
            <div class="form-group">
                <label for="username" style="display:block; margin-bottom:0.4rem; font-weight:500;">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>
            <div class="form-group">
                <label for="password" style="display:block; margin-bottom:0.4rem; font-weight:500;">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
            </div>
            <div class="form-group">
                <label for="role" style="display:block; margin-bottom:0.4rem; font-weight:500;">Role</label>
                <select id="role" name="role" required
                    style="width:100%; padding:0.6rem; border-radius:7px; border:1px solid #ccc; font-size:1rem;">
                    <option value="admin">Admin</option>
                    <option value="checkin">Checkin</option>
                    <option value="registration">Registration</option>
                    <option value="kit">Kit</option>
                </select>
            </div>
            <button type="submit" class="btn"
                style="width:100%; padding:0.8rem; background:#38ef7d; color:white; border:none; border-radius:8px; font-weight:bold; font-size:1.1rem; margin-top:1rem;">
                Add
            </button>
        </form>
    </div>
</div>
<?php renderFooter(); ?>