<?php
require_once 'db_connection.php';

$sql = "SELECT COUNT(*) as total_jobs, 
               MIN(start_date) as earliest_job, 
               MAX(start_date) as latest_job 
        FROM jobs";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo "Total Jobs: " . $row['total_jobs'] . "\n";
echo "Earliest Job Date: " . $row['earliest_job'] . "\n";
echo "Latest Job Date: " . $row['latest_job'] . "\n";
?>
