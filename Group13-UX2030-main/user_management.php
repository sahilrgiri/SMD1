<?php
// Define page variables
$page = 'user_management';
$page_title = 'User Management';
$back_url = 'admin_dashboard.php';

include 'templates/admin_header.php';
require_once 'db_connection.php';

// Handle search query
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// SQL query to retrieve all users or search results
$sql = "SELECT id, username, name, email, role FROM users WHERE username LIKE '%$search_query%' OR name LIKE '%$search_query%'";
$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}
?>

<div class='dashboard-content'>
    <h1 class="page-title"><?php echo $page_title; ?></h1>

    <!-- Search Form -->
    <form action='user_management.php' method='get' class="search-form">
        <input type='text' name='search' placeholder='Search by username or name' value='<?php echo htmlspecialchars($search_query); ?>'>
        <button type='submit' class="button search-button">Search</button>
    </form>

    <!-- User Table -->
    <?php if ($result->num_rows > 0) { ?>
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['role']; ?></td>
                            <td class="action-buttons">
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="button edit-button">Edit</a>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <p class="no-results">No users found.</p>
    <?php } ?>

    <!-- Add New User Button -->
    <a href="add_user.php" class="button add-button">Add New User</a>
</div>

<?php include 'templates/admin_footer.php'; ?>