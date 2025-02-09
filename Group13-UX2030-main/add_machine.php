<?php
$page = 'machine_management';
$page_title = 'Add Machine';
$back_url = 'machine_management.php';
include 'templates/admin_header.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $machine_name = $conn->real_escape_string($_POST['machine_name']);

    $sql = "INSERT INTO machines (machine_name) VALUES ('$machine_name')";
    if ($conn->query($sql) === TRUE) {
        header("Location: machine_management.php?success=Machine added successfully");
        exit();
    } else {
        $error = "Error adding machine: " . $conn->error;
    }
}
?>

<style>
    .form-group {
        margin-bottom: 15px;
    }

    input[type="text"], 
    select {
        width: 100%;
        max-width: 500px; /* Max width */
        height: 40px; /* Height */
        font-size: 16px; /* Font size */
        padding: 10px; /* Padding */
        box-sizing: border-box; 
        border: 1px solid #ddd; 
        border-radius: 5px; /* Rounded corners */
    }

    input[type="text"]:focus {
        border-color: #09a225; /* Border color on focus */
        outline: none; /* Remove outline */
        box-shadow: 0 0 0 2px rgba(9, 162, 37, 0.1); /* Shadow effect */
    }

    .form-actions .button {
        padding: 10px 20px; /* Button padding */
        font-size: 16px; /* Button font size */
    }
</style>

<div class="dashboard-content">
    <div class="dashboard-section">
        <h2>Add Machine</h2>
        <?php if (isset($error)): ?>
            <div class="message-container error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="add_machine.php" method="post" class="admin-form">
            <div class="form-group">
                <label for="machine_name">Machine Name:</label>
                <input type="text" id="machine_name" name="machine_name" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Add Machine</button>
                <a href="machine_management.php" class="button" style="background-color: #6c757d;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'templates/admin_footer.php'; ?>