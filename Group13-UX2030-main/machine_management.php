<?php

session_start();
require_once 'db_connection.php';
require_once 'Notification.php';
use App\Notification\Notification;

$page = 'machine_management';
$page_title = 'Machine Management';
$back_url = 'admin_dashboard.php';

// Handle all status updates before including header
if (isset($_GET['action']) && isset($_GET['id'])) {
    $machineId = $_GET['id'];
    $newStatus = $_GET['action'];
    
    if (in_array($newStatus, ['operational', 'non-operational'])) {
        $sql = "UPDATE machines SET operational_status = ?, error_code = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $errorCode = $newStatus === 'non-operational' ? 'E001' : NULL;
        $stmt->bind_param("ssi", $newStatus, $errorCode, $machineId);
        $stmt->execute();

        $notification = new Notification($conn);
        $notificationId = $notification->createNotification(
            'machine_status', 
            "Machine $machineId is now $newStatus"
        );
        $notification->addRecipient($notificationId, $_SESSION['user_id']);

        // Redirect to remove the action from the URL
        header("Location: machine_management.php");
        exit();
    }
}

include 'templates/admin_header.php';

// Set the number of machines to display per page
$limit = 15;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $limit;

// Search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Get total number of machines for pagination
$total_sql = "SELECT COUNT(*) as total FROM machines WHERE machine_name LIKE ?";
$stmt = $conn->prepare($total_sql);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$total_machines = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_machines / $limit);

// Get machines for current page
$sql = "SELECT * FROM machines WHERE machine_name LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<head>
  
    <script src="scripts/machine_management.js"></script>
    <link rel="stylesheet" type="text/css" href="styles/machine_management.css">
</head>

<div class="dashboard-content">
    <h1>Machine Management</h1>
    
    <div class="dashboard-section">
        <div class="actions-container">
            <a href="add_machine.php" class="button">Add Machine</a>
            <form action="machine_management.php" method="get" class="search-form">
                <input type="text" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search machines...">
                <button type="submit" class="button">Search</button>
            </form>
        </div>

       <div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Machine Name</th>
                <th>Status</th>
                <th>Power (kW)</th>
                <th>Error Code</th>
                <th>Maintenance Log</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['machine_name'] ?? ''); ?></td>
                    <td class="status-cell <?php echo htmlspecialchars($row['operational_status'] ?? ''); ?>">
                        <?php echo htmlspecialchars($row['operational_status'] ?? ''); ?>
                    </td>
                    <td>
                        <?php 
                            echo isset($row['power_consumption']) 
                                ? number_format($row['power_consumption'], 2) 
                                : '-'; 
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['error_code'] ?? '-'); ?></td>
                    <td class="maintenance-log">
                        <?php 
                            if (!empty($row['maintenance_log'])) {
                                echo '<div class="log-content">' . 
                                     htmlspecialchars($row['maintenance_log']) . 
                                     '</div>';
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td class="actions">
                        <a href="edit_machine.php?id=<?php echo $row['id'] ?? ''; ?>" 
                           class="button edit-button">Edit</a>
                        <?php if (($row['operational_status'] ?? '') == 'operational') { ?>
                            <button class="button toggle-status" 
                                    data-id="<?php echo $row['id'] ?? ''; ?>"
                                    data-action="non-operational">
                                Mark Non-Operational
                            </button>
                        <?php } else { ?>
                            <button class="button toggle-status operational" 
                                    data-id="<?php echo $row['id'] ?? ''; ?>"
                                    data-action="operational">
                                Mark Operational
                            </button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusButtons = document.querySelectorAll('.toggle-status');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const machineId = this.getAttribute('data-id');
            const action = this.getAttribute('data-action');
            let message;
            
            if (action === 'non-operational') {
                message = 'Machine marked as non-operational. Refresh page to see current status.';
            } else {
                message = 'Machine marked as operational. Refresh page to see current status.';
            }
            
            // Show confirmation popup
            if (confirm(message)) {
                // Redirect to update status
                window.location.href = `machine_management.php?action=${action}&id=${machineId}`;
            }
        });
    });
});
</script>

<?php include 'templates/admin_footer.php'; ?>
