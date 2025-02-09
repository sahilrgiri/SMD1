<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operator_id = $_POST['operator'];
    $assigned_machines = implode(',', $_POST['machine']);
    $assigned_jobs = implode(',', $_POST['job']);

    // Update users table with assigned machines and jobs
    $sql = "UPDATE users SET assigned_machines = '$assigned_machines', assigned_jobs = '$assigned_jobs' WHERE id = '$operator_id'";
    if ($conn->query($sql) === TRUE) {
        foreach ($_POST['job'] as $job_id) {
            $sql = "UPDATE jobs SET assigned_operator_id = '$operator_id' WHERE job_id = '$job_id'";
            $conn->query($sql);
        }
        header("Location: manager_dashboard.php?success=Assignment successful");
    } else {
        header("Location: assign_role.php?error=" . $conn->error);
    }
    $conn->close();
}
?>
