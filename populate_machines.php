<?php
include 'db_connection.php';


$sql = "ALTER TABLE machines ADD COLUMN IF NOT EXISTS machine_id INT AUTO_INCREMENT PRIMARY KEY";
$conn->query($sql);

// Fetch all unique machine names from factory_log
$sql = "SELECT DISTINCT machine_name FROM factory_log";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $machine_name = $conn->real_escape_string($row['machine_name']);

    // Check if the machine already exists in the machines table
    $check_sql = "SELECT machine_id FROM machines WHERE machine_name = '$machine_name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows == 0) {
        // Insert the machine into the machines table
        $insert_sql = "INSERT INTO machines (machine_name) VALUES ('$machine_name')";
        if (!$conn->query($insert_sql)) {
            echo "Error inserting machine: " . $conn->error . "\n";
        }
    }
}

$conn->close();
echo "Machine ID update completed.";
?>
