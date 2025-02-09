<?php


session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, role, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            switch ($row['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'manager':
                    header("Location: manager_dashboard.php");
                    break;
                case 'operator':
                    header("Location: operator_dashboard.php");
                    break;
                case 'auditor':
                    header("Location: auditor_dashboard.php");
                    break;
                default:
                    header("Location: login.php?error=invalid_role");
            }
        } else {
            header("Location: login.php?error=invalid_password");
        }
    } else {
        header("Location: login.php?error=user_not_found");
    }
}

$conn->close();
?>