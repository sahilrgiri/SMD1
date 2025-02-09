<?php
// add_job.php
session_start();
require_once 'db_connection.php';

// Authentication check for factory managers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Fetch operators and machines for the dropdown lists
$sql_operators = "SELECT id, username FROM users WHERE role = 'operator'";
$result_operators = $conn->query($sql_operators);

$sql_machines = "SELECT id, machine_name FROM machines";
$result_machines = $conn->query($sql_machines);
$page='add_job.php';
$page_title = 'Add Job';
include 'templates/manager_header.php';
?>


<div class="dashboard-content">
    <h1>Add New Job</h1>

    <form action="process_add_job.php" method="post" class="add-job-form">
        <label for="job_name">Job Name:</label>
        <input type="text" name="job_name" required>

        <label for="operator">Operator:</label>
        <select name="operator" required>
    <option value="">Select Operator</option>
    <?php while($row = $result_operators->fetch_assoc()) {?>
    <option value="<?php echo $row['id'];?>"><?php echo htmlspecialchars($row['username']);?></option>
    <?php }?>
</select>


        <label for="machine">Machine:</label>
        <select name="machine" required>
            <option value="">Select Machine</option>
            <?php while($row = $result_machines->fetch_assoc()) {?>
            <option value="<?php echo $row['id'];?>"><?php echo htmlspecialchars($row['machine_name']);?></option>
            <?php }?>
        </select>

        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" required>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" required>

        <label for="priority">Priority:</label>
        <input type="number" name="priority" min="1" max="10" required>

        <button type="submit" class="button">Add Job</button>
    </form>
</div>


