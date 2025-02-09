<?php
session_start();
require_once 'db_connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: machine_management.php?error=Invalid machine ID");
    exit();
}

// Fetch the machine details
$stmt = $conn->prepare("SELECT * FROM machines WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$machine = $result->fetch_assoc();
$stmt->close();

if (!$machine) {
    header("Location: machine_management.php?error=Machine not found");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $machine_name = $conn->real_escape_string($_POST['machine_name']);
    $operational_status = $conn->real_escape_string($_POST['operational_status']);
    $maintenance_log = $conn->real_escape_string($_POST['maintenance_log']);
    
    // If status changed to maintenance, add current date to maintenance log
    if ($operational_status === 'maintenance' && $machine['operational_status'] !== 'maintenance') {
        $date = date('Y-m-d H:i:s');
        $maintenance_log = "Maintenance started on $date\n" . $maintenance_log;
    }

    $sql = "UPDATE machines SET machine_name = ?, operational_status = ?, maintenance_log = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $machine_name, $operational_status, $maintenance_log, $id);
    
    if ($stmt->execute()) {
        header("Location: machine_management.php?success=Machine updated successfully");
        exit();
    } else {
        $error = "Error updating machine: " . $conn->error;
    }
}

$page = 'machine_management';
$page_title = 'Edit Machine';
$back_url = 'machine_management.php';
include 'templates/admin_header.php';
?>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    input[type="text"], 
    select, 
    textarea {
        width: 100%; /* Full width */
        max-width: 500px; /* Max width */
        height: 40px; /* Height for inputs and selects */
        font-size: 16px; /* Font size */
        padding: 10px; /* Padding */
        box-sizing: border-box; /* Include padding in width/height */
        border: 1px solid #ddd; /* Border for inputs and selects */
        border-radius: 4px; /* Rounded corners */
    }

    textarea {
        min-height: 100px; /* Minimum height for textarea */
        resize: vertical; /* Allow vertical resizing */
    }

    .form-text {
        font-size: 0.875em;
        color: #6c757d;
        margin-top: 5px;
    }

    input[type="text"]:focus, 
    select:focus, 
    textarea:focus {
        border-color: #09a225;
        outline: none;
        box-shadow: 0 0 0 2px rgba(9, 162, 37, 0.1);
    }
</style>

<div class="dashboard-content">
    <div class="dashboard-section">
        <h2>Edit Machine</h2>
        <?php if (isset($error)): ?>
            <div class="message-container error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="edit_machine.php?id=<?php echo $id; ?>" method="post" class="admin-form">
            <div class="form-group">
                <label for="machine_name">Machine Name:</label>
                <input type="text" id="machine_name" name="machine_name" 
                       value="<?php echo htmlspecialchars($machine['machine_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="operational_status">Operational Status:</label>
                <select name="operational_status" id="operational_status" required>
                    <option value="operational" <?php echo ($machine['operational_status'] === 'operational') ? 'selected' : ''; ?>>
                        Operational
                    </option>
                    <option value="non-operational" <?php echo ($machine['operational_status'] === 'non-operational') ? 'selected' : ''; ?>>
                        Non-Operational
                    </option>
                    <option value="maintenance" <?php echo ($machine['operational_status'] === 'maintenance') ? 'selected' : ''; ?>>
                        Maintenance
                    </option>
                    <option value="idle" <?php echo ($machine['operational_status'] === 'idle') ? 'selected' : ''; ?>>
                        Idle
                    </option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="maintenance_log">Maintenance Log:</label>
                <textarea name="maintenance_log" id="maintenance_log" rows="5"><?php echo htmlspecialchars($machine['maintenance_log'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="button">Update Machine</button>
                <button type="button" class="button" 
                        onclick="if(confirm('Are you sure you want to delete this machine?')) 
                                window.location.href='delete_machine.php?id=<?php echo $id; ?>'"
                        style="background-color: #dc3545;">Delete Machine</button>
                <a href="machine_management.php" class="button" style="background-color: #6c757d;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'templates/admin_footer.php'; ?>
