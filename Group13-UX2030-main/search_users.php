<?php

session_start();
include 'db_connection.php';

// Authentication check for admins only
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

$search = $conn->real_escape_string($_GET['search']);

// SQL query to search for users
$sql = "SELECT * FROM users WHERE username LIKE '%$search%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users</title>
    <link rel="stylesheet" type="text/css" href="styles/global.css">
    <link rel="stylesheet" type="text/css" href="styles/user_management.css">
</head>
<body>
    <h1>Search Results for "<?php echo $search; ?>"</h1>
    <?php if (isset($_GET['error'])) { ?>
        <div class="error"><?php echo $_GET['error']; ?></div>
    <?php } ?>
    <?php if (isset($_GET['success'])) { ?>
        <div class="success"><?php echo $_GET['success']; ?></div>
    <?php } ?>

    <form action="search_users.php" method="get">
        <input type="text" name="search" value="<?php echo $search; ?>" required>
        <button type="submit">Search</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['role']; ?></td>
            <td>
                <a class="button" href="edit_user.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                <a class="button" href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <a class="button" href="user_management.php">Back to User Management</a>

    <script src="scripts/edit_user.js"></script>
</body>
</html>
