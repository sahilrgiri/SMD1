<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: user_management.php?success=User deleted successfully");
    } else {
        header("Location: user_management.php?error=Error deleting user: " . $conn->error);
    }

    $stmt->close();
} else {
    header("Location: user_management.php");
}

$conn->close();
?>