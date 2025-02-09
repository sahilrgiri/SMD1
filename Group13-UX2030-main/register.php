
<?php

session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $conn->real_escape_string($_POST['role']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);

    // Validation
    if ($password !== $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    }

    // Check for existing username
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        header("Location: register.php?error=Username already exists");
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (username, password, role, email, phone_number) VALUES ('$username', '$password_hash', '$role', '$email', '$phone_number')";
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?success=User registered successfully");
    } else {
        header("Location: register.php?error=Error registering user: " . $conn->error);
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMD User Registration</title>
    <link rel="stylesheet" type="text/css" href="styles/global.css">
    <link rel="stylesheet" type="text/css" href="styles/register.css">
</head>
<body>
    <form action="register.php" method="post">
        <h2>SMD User Registration</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone_number" placeholder="Phone Number" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="operator">Operator</option>
            <option value="auditor">Auditor</option>
        </select>
        <button type="submit">Register</button>
    </form>
    <script src="scripts/register.js"></script>
</body>
</html>
