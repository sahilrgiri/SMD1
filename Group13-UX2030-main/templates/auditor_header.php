<?php
// auditor_header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';
require_once 'Notification.php';

use App\Notification\Notification;

// Check if the user is logged in and has auditor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'auditor') {
    header("Location: login.php?error=access_denied");
    exit();
}

$notification = new Notification($conn);
$unreadNotifications = $notification->getUnreadNotifications($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - SMD Auditor' : 'SMD Auditor'; ?></title>
    <link rel="stylesheet" href="styles/auditor_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h1>SMD Auditor</h1>
            <nav>
                <ul>
                    <li><a href="auditor_dashboard.php" <?php echo ($page == 'auditor_dashboard') ? 'class="active"' : ''; ?>>Dashboard</a></li>
                    <li><a href="audit_reports.php" <?php echo ($page == 'audit_logs') ? 'class="active"' : ''; ?>>Audit Logs</a></li>
                    <li><a href="performance_reports.php" <?php echo ($page == 'performance_reports') ? 'class="active"' : ''; ?>>Performance Reports</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-bar">
                <div class="back-button-container">
                    <?php if (isset($back_url)): ?>
                        <a href="<?php echo $back_url; ?>" class="button back-button">Back</a>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <img src="images/profile_pic.png" alt="Profile Picture">
                    <span><?php echo $_SESSION['username']; ?></span>
                    <ul class="profile-dropdown">
                        <li><a href="profile_info.php">Profile Info</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="notification-area">
                    <div class="notification-icon" id="notificationIcon">
                        <span class="notification-count"><?php echo count($unreadNotifications); ?></span>
                        Notifications
                    </div>
                    <ul class="notification-list" id="notificationList">
                        <?php foreach ($unreadNotifications as $notification) { ?>
                            <li><a href="#"><?php echo $notification['message']; ?> (<span class="notification-time"><?php echo $notification['created_at']; ?></span>)</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </header>
