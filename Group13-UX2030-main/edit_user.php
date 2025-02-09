<?php
$page = 'user_management';
$page_title = 'Edit User';
$back_url = 'user_management.php';
include 'templates/admin_header.php';

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id === 0) {
    header("Location: user_management.php?error=Invalid user ID");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"] ?? '');
    $email = $conn->real_escape_string($_POST["email"] ?? '');
    $phone_number = $conn->real_escape_string($_POST["phone_number"] ?? '');
    $role = $conn->real_escape_string($_POST["user_role"] ?? '');

    $sql = "UPDATE users SET username = ?, email = ?, phone_number = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $email, $phone_number, $role, $user_id);
    
    if ($stmt->execute()) {
        header("Location: user_management.php?success=User updated successfully");
        exit();
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: user_management.php?error=User not found");
    exit();
}

$user = $result->fetch_assoc();
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
        <h2>Edit User</h2>
        
        <?php if (isset($error)): ?>
            <div class="message-container error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post" class="admin-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone:</label>
                <input type="text" id="phone_number" name="phone_number" 
                       value="<?php echo htmlspecialchars($user['phone_number']); ?>">
            </div>
            <div class="form-group">
                <label for="user_role">Role:</label>
                <select name="user_role" id="user_role" required>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Factory Manager</option>
                    <option value="operator" <?php echo $user['role'] === 'operator' ? 'selected' : ''; ?>>Production Operator</option>
                    <option value="auditor" <?php echo $user['role'] === 'auditor' ? 'selected' : ''; ?>>Auditor</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Update User</button>
                <button type="button" class="button" 
                        onclick="if(confirm('Are you sure you want to delete this user?')) 
                                window.location.href='delete_user.php?id=<?php echo $user_id; ?>'"
                        style="background-color: #dc3545;">Delete User</button>
                <a href="user_management.php" class="button" style="background-color: #6c757d;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'templates/admin_footer.php'; ?>