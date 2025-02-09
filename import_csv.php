<?php

include 'db_connection.php';

$file = fopen("factory_logs.csv", "r");

fgetcsv($file);

while (($data = fgetcsv($file)) !== FALSE) {
    $timestamp = DateTime::createFromFormat('d/m/Y H:i', $data[0])->format('Y-m-d H:i:s');
    $machine_name = $conn->real_escape_string($data[1]);
    $temperature = floatval($data[2]);
    $pressure = floatval($data[3]);
    $vibration = floatval($data[4]);
    $humidity = floatval($data[5]);
    $power_consumption = floatval($data[6]);
    $operational_status = $conn->real_escape_string($data[7]);
    $error_code = $conn->real_escape_string($data[8]);
    $production_count = intval($data[9]);
    $maintenance_log = $conn->real_escape_string($data[10]);
    $speed = floatval($data[11]);

    $sql = "INSERT INTO factory_log (timestamp, machine_name, temperature, pressure, vibration, humidity, power_consumption, operational_status, error_code, production_count, maintenance_log, speed) 
            VALUES ('$timestamp', '$machine_name', $temperature, $pressure, $vibration, $humidity, $power_consumption, '$operational_status', '$error_code', $production_count, '$maintenance_log', $speed)";

    if (!$conn->query($sql)) {
        echo "Error importing row: " . $conn->error . "\n";
    }
}

fclose($file);
echo "CSV import completed.";

$conn->close();
?>
