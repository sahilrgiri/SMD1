<?php
session_start();
include 'db_connection.php';

// Verify session variables and admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role']!== 'admin') {
    header("Location: login.php?error=session_expired");
    exit();
}

// Retrieve user ID from query string
$user_id = $_GET['user_id'];

// Confirm deletion
if (isset($_GET['confirm'])) {
    $sql = "DELETE FROM users WHERE id = '$user_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php?success=User deleted successfully");
    } else {
        header("Location: admin_dashboard.php?error=". $conn->error);
    }
    $conn->close();
}
?>

<!-- Delete user profile confirmation -->
<h1>Delete User Profile</h1>
<p>Are you sure you want to delete the user profile with ID <?php echo $user_id;?>?</p>
<a href="delete_profile.php?user_id=<?php echo $user_id;?>&confirm=true">Confirm Deletion</a>
