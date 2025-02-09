<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php?error=access_denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_name = $_POST['job_name'];
    $operator_id = $_POST['operator'];
    $machine_id = $_POST['machine'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $priority = $_POST['priority'];


    $sql = "INSERT INTO jobs (job_name, operator_id, machine_id, start_date, end_date, priority) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("siissi", $job_name, $operator_id, $machine_id, $start_date, $end_date, $priority);

        if ($stmt->execute()) {
            header("Location: manager_dashboard.php?success=job_added");
        } else {
            header("Location: add_job.php?error=failed_to_add_job");
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    header("Location: add_job.php?error=invalid_request");
}
?>
