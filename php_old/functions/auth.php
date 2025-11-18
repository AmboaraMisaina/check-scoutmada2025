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

function getNavigation($role)
{
    switch ($role) {
        case 'admin':
            return [
                'checkin.php' => '✅ Check-in',
                'programmes.php' => '📅 Programs',
                'participants.php' => '👥 Participants',
                'add_user.php' => '➕ Add User',
            ];        
        case 'registration':
            return [
                'checkin.php' => '✅ Check-in',
                'participants.php' => '👥 Participants',
            ];
        case 'kit':
            return [
                'participants.php' => '👥 Participants',
            ];
        case 'checkin':
            return [
                'checkin.php' => '✅ Check-in',
                
            ];
        case 'print':
            return [
                'participants.php' => '👥 Participants',
                
            ];
        default:
            return [];
    }   
}

function renderFooter()
{
    ?>
    </body>

    </html>
<?php
}
?>