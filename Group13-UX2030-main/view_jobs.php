
<?php
require_once 'db_connection.php';

$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);

?>

<h1>All Jobs</h1>
<table>
    <tr>
        <th>Job ID</th>
        <th>Job Name</th>
        <th>Operator</th>
        <th>Machine</th>
        <th>Start Time</th>
        <th>End Time</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['job_id']; ?></td>
            <td><?php echo $row['job_name']; ?></td>
            <td>
                <?php
                $operator_id = $row['operator_id'];
                $sql_operator = "SELECT name FROM operators WHERE id = '$operator_id'";
                $result_operator = $conn->query($sql_operator);
                $operator_name = $result_operator->fetch_assoc()['name'];
                echo $operator_name;
                ?>
            </td>
            <td>
                <?php
                $machine_id = $row['machine_id'];
                $sql_machine = "SELECT machine_name FROM machines WHERE machine_id = '$machine_id'";
                $result_machine = $conn->query($sql_machine);
                $machine_name = $result_machine->fetch_assoc()['machine_name'];
                echo $machine_name;
                ?>
            </td>
            <td><?php echo $row['start_time']; ?></td>
            <td><?php echo $row['end_time']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>
