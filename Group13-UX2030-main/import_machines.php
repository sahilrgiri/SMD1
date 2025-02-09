<?php

include 'db_connection.php';

// Get unique machine names from factory_log
$sql = "SELECT DISTINCT machine_name FROM factory_log";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $machine_name = $conn->real_escape_string($row['machine_name']);
        
        // Check if the machine already exists in the machines table
        $check_sql = "SELECT * FROM machines WHERE machine_name = '$machine_name'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows == 0) {
            // Insert the machine into the machines table
            $insert_sql = "INSERT INTO machines (machine_name) VALUES ('$machine_name')";
            if (!$conn->query($insert_sql)) {
                echo "Error inserting machine: " . $conn->error . "\n";
            }
        }
    }
    echo "Machine import completed.";
} else {
    echo "No machines found in factory_log.";
}

$conn->close();
?>
