<?php
$page = 'auditor_dashboard';
$page_title = 'Auditor Dashboard';
include 'templates/auditor_header.php';
require_once 'message.php';

use App\Message;

$message = new Message($conn);

if (isset($_POST['send_message'])) {
    $senderId = $_SESSION['user_id'];
    $receiverId = $_POST['receiver_id'];
    $messageText = $_POST['message'];
    $message->sendMessage($senderId, $receiverId, $messageText);
}

$messages = $message->getMessages($_SESSION['user_id']);
echo "<!-- Debug: Number of messages: " . count($messages) . " -->";


// Pagination for audit logs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of logs per page
$offset = ($page - 1) * $limit;

// Check if audit_logs table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'audit_logs'")->num_rows > 0;

if ($table_exists) {
    // SQL query to retrieve paginated audit logs
    $sql_logs = "SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT $limit OFFSET $offset";
    $result_logs = $conn->query($sql_logs);

    // Get total number of logs for pagination
    $total_logs = $conn->query("SELECT COUNT(*) as count FROM audit_logs")->fetch_assoc()['count'];
    $total_pages = ceil($total_logs / $limit);
} else {
    $result_logs = null;
    $total_logs = 0;
    $total_pages = 0;
}

?>

<head>
<link rel="stylesheet" type="text/css" href="styles/auditor_dashboard.css">
<script src="scripts/auditor_dashboard.js"></script>
</head>

<div class="dashboard-content">
    <h1>Welcome, Auditor <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>Recent Audit Logs</h2>
            <?php if ($table_exists && $result_logs && $result_logs->num_rows > 0): ?>
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_logs->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo htmlspecialchars($row['details']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php echo ($page == $i) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                
                <a href="audit_reports.php" class="button">View All Logs</a>
            <?php else: ?>
                <p>No audit logs available at this time.</p>
            <?php endif; ?>
            <a href="audit_reports.php" class="button">Generate Report</a>
        </div>
        <div class="dashboard-section">
            <h2>Performance Reports</h2>
            <a href="performance_reports.php" class="button">Generate Report</a>
        </div>
        <div class="dashboard-section">
            <h2>System Statistics</h2>
            <ul>
                <li>Total Logs: <?php echo $total_logs; ?></li>
                <?php if ($table_exists): ?>
                    <li>Users Audited: <?php echo $conn->query("SELECT COUNT(DISTINCT username) FROM audit_logs")->fetch_assoc()['COUNT(DISTINCT username)']; ?></li>
                    <li>Actions Recorded: <?php echo $conn->query("SELECT COUNT(DISTINCT action) FROM audit_logs")->fetch_assoc()['COUNT(DISTINCT action)']; ?></li>
                <?php else: ?>
                    <li>Users Audited: 0</li>
                    <li>Actions Recorded: 0</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>


    <div class="dashboard-section messaging-container">
    <h2>Messaging</h2>
    <div class="messaging-pane">
        <div class="send-message-form">
            <h3>Send Message</h3>
            <form action="" method="post">
                <div class="form-group">
                    <label for="receiver_id">Recipient:</label>
                    <select name="receiver_id" id="receiver_id" required>
                        <option value="">Select Recipient</option>
                        <?php
                        $sql = "SELECT id, username FROM users WHERE role IN ('admin', 'manager')";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['username']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="machine_id">Regarding Machine (optional):</label>
                    <select name="machine_id" id="machine_id">
                        <option value="">Select Machine</option>
                        <?php
                        $sql = "SELECT id, machine_name FROM machines";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['machine_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="job_id">Regarding Job (optional):</label>
                    <select name="job_id" id="job_id">
                        <option value="">Select Job</option>
                        <?php
                        $sql = "SELECT job_id, job_name FROM jobs";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"" . $row['job_id'] . "\">" . htmlspecialchars($row['job_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea name="message" id="message" required placeholder="Type your message here..."></textarea>
                </div>
                <button type="submit" name="send_message" class="button">Send Message</button>
            </form>
        </div>

        <div class="inbox">
            <h3>Inbox</h3>
            <?php
            if (empty($messages)) {
                echo "<p>No messages in your inbox.</p>";
            } else {
                foreach ($messages as $msg) {
                    ?>
                    <div class="message-container">
                        <div class="message-header">
                            <span class="message-sender">From: <?php echo htmlspecialchars($msg['sender_name']); ?></span>
                            <span class="message-timestamp"><?php echo htmlspecialchars($msg['sent_at']); ?></span>
                        </div>
                        <div class="message-body">
                            <?php echo htmlspecialchars($msg['message']); ?>
                        </div>
                        <?php if (isset($msg['machine_name'])): ?>
                            <div class="message-details">
                                Regarding Machine: <?php echo htmlspecialchars($msg['machine_name']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($msg['job_name'])): ?>
                            <div class="message-details">
                                Regarding Job: <?php echo htmlspecialchars($msg['job_name']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="message-footer">
                            <?php if ($msg['read_at'] === null): ?>
                                <button class="button mark-as-read-button" data-message-id="<?php echo $msg['id']; ?>">Mark as Read</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>







</div>



<?php include 'templates/auditor_footer.php'; ?>
