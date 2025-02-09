<?php

session_start();
include 'db_connection.php';

// Authentication check for admins only
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

$search = $conn->real_escape_string($_GET['search']);

// SQL query to search for machines
$sql = "SELECT * FROM machines WHERE machine_name LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Machines</title>
    <link rel="stylesheet" type="text/css" href="styles/global.css">
    <link rel="stylesheet" type="text/css" href="styles/machine_management.css">
</head>
<body>
    <h1>Search Results for "<?php echo $search; ?>"</h1>
    <?php if (isset($_GET['error'])) { ?>
        <div class="error"><?php echo $_GET['error']; ?></div>
    <?php } ?>
    <?php if (isset($_GET['success'])) { ?>
        <div class="success"><?php echo $_GET['success']; ?></div>
    <?php } ?>

    <form action="search_machines.php" method="get">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" required>
        <button type="submit">Search</button>
    </form>

    <table>
        <tr>
            <th>Machine ID</th>
            <th>Machine Name</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['machine_id']; ?></td>
            <td><?php echo $row['machine_name']; ?></td>
            <td>
                <a class="button" href="edit_machine.php?machine_id=<?php echo $row['machine_id']; ?>">Edit</a> | 
                <a class="button" href="delete_machine.php?machine_id=<?php echo $row['machine_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <a class="button" href="machine_management.php">Back to Machine Management</a>

    <script src="scripts/edit_machine.js"></script>
</body>
</html>
