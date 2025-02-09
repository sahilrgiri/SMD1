<?php
session_start();
include 'db_connection.php';

// Authentication check for factory managers and admins
if (!isset($_SESSION['user_id']) || (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'admin'))) {
    header("Location: login.php?error=access_denied");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_name = $conn->real_escape_string($_POST['job_name']);
    $operator_id = (int)$_POST['operator_id'];
    $machine_id = (int)$_POST['machine_id'];
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);

    $stmt = $conn->prepare("INSERT INTO jobs (job_name, operator_id, machine_id, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiii", $job_name, $operator_id, $machine_id, $start_time, $end_time);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=Job assigned successfully");
    } else {
        header("Location: assign_job.php?error=Error assigning job: " . $conn->error);
    }
    $stmt->close();
    $conn->close();
}

// SQL query to retrieve all operators
$sql_operators = "SELECT * FROM operators";
$result_operators = $conn->query($sql_operators);

// SQL query to retrieve all machines
$sql_machines = "SELECT * FROM machines";
$result_machines = $conn->query($sql_machines);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Job</title>
    <link rel="stylesheet" type="text/css" href="styles/global.css">
    <link rel="stylesheet" type="text/css" href="styles/assign_job.css">
</head>
<body>
    <h1>Assign Job</h1>
    <form action="assign_job.php" method="post">
        <label for="job_name">Job Name:</label>
        <input type="text" id="job_name" name="job_name" required>
        <label for="operator_id">Operator:</label>
        <select name="operator_id" required>
            <option value="">Select Operator</option>
            <?php while($row = $result_operators->fetch_assoc()) {?>
            <option value="<?php echo $row['id'];?>"><?php echo $row['name'];?></option>
            <?php }?>
        </select>
        <label for="machine_id">Machine:</label>
        <select name="machine_id" required>
            <option value="">Select Machine</option>
            <?php while($row = $result_machines->fetch_assoc()) {?>
            <option value="<?php echo $row['machine_id'];?>"><?php echo $row['machine_name'];?></option>
            <?php }?>
        </select>
        <label for="start_time">Start Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" required>
        <label for="end_time">End Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" required>
        <button type="submit" class="button">Assign Job</button>
    </form>
    <a href="admin_dashboard.php" class="button">Back to Dashboard</a>
    <script src="scripts/assign_job.js"></script>
</body>
</html>
