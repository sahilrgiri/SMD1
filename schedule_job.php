<?php
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validate scheduling conflicts
    $sql = "SELECT * FROM jobs WHERE start_date <= '$end_date' AND end_date >= '$start_date' AND job_id!= '$job_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        header("Location: schedule_job.php?error=Scheduling conflict detected");
        exit();
    }

    // Update job schedule
    $sql = "UPDATE jobs SET start_date = '$start_date', end_date = '$end_date' WHERE job_id = '$job_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: schedule_job.php?success=Job scheduled successfully");
    } else {
        header("Location: schedule_job.php?error=". $conn->error);
    }
    $conn->close();
}
?>

<!-- Schedule Job Form -->
<form action="schedule_job.php" method="post">
    <label for="job_id">Job ID:</label>
    <input type="number" id="job_id" name="job_id" required>

    <label for="start_date">Start Time:</label>
    <input type="datetime-local" id="start_date" name="start_date" required>

    <label for="end_date">End Time:</label>
    <input type="datetime-local" id="end_date" name="end_date" required>

    <button type="submit">Schedule Job</button>
</form>
