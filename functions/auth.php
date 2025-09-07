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
        'dashboard.php' => '📊 Chekin',
        'participants.php' => '👥 Participants',
        'dashboard.php' => '📅 Programmes',
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