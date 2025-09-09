<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth()
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getNavigation()
{
    return [
        'checkin.php' => '✅ Check-in',
        'programmes.php' => '📅 Programs',
        'participants.php' => '👥 Participants',
        'add_user.php' => '➕ Add User',
    ];
}

function renderFooter()
{
    ?>
    </body>

    </html>
<?php
}
?>