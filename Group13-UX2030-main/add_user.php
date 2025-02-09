<?php
session_start();
require_once 'db_connection.php';

// Process form submission before any output
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';
    $email = $conn->real_escape_string($_POST["email"] ?? '');
    $phone_number = $conn->real_escape_string($_POST["phone_number"] ?? '');
    $role = $conn->real_escape_string($_POST["user_role"] ?? '');

    if (empty($username) || empty($password) || empty($email) || empty($role)) {
        $error = "All required fields must be filled out.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password, email, phone_number, role) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $password_hash, $email, $phone_number, $role);
        
        if ($stmt->execute()) {
            header("Location: user_management.php?success=User added successfully");
            exit();
        } else {
            $error = "Error adding user: " . $conn->error;
        }
    }
}

// Set page variables and include header
$page = 'user_management';
$page_title = 'Add User';
$back_url = 'user_management.php';
include 'templates/admin_header.php';
?>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    input[type="text"], 
    input[type="password"], 
    input[type="email"], 
    select {
        width: 100%;
        max-width: 300px; 
        height: 40px; 
        font-size: 16px; 
        padding: 10px; 
        box-sizing: border-box; 
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .form-actions .button {
        padding: 10px 20px; 
        font-size: 16px; 
    }
</style>

<div class="dashboard-content">
    <div class="dashboard-section">
        <h2>Add New User</h2>
        
        <?php if (isset($error)): ?>
            <div class="message-container error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="add_user.php" method="post" class="admin-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone:</label>
                <input type="text" id="phone_number" name="phone_number">
            </div>
            <div class="form-group">
                <label for="user_role">Role:</label>
                <select name="user_role" id="user_role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Factory Manager</option>
                    <option value="operator">Production Operator</option>
                    <option value="auditor">Auditor</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add User</button>
                <a href="user_management.php" class="button" style="background-color: #6c757d;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'templates/admin_footer.php'; ?>
