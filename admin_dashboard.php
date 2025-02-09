
<?php
$page = 'admin_dashboard';
$page_title = 'Admin Dashboard';
include 'templates/admin_header.php';
require_once 'message.php';

use App\Message;

$conn = new mysqli("localhost", "root", "root", "smd_database");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Functions
$message = new Message($conn);

if (isset($_POST['send_message'])) {
    $senderId = $_SESSION['user_id'];
    $receiverId = $_POST['receiver_id'];
    $messageText = $_POST['message'];
    $machineId = !empty($_POST['machine_id']) ? $_POST['machine_id'] : null;
    $jobId = !empty($_POST['job_id']) ? $_POST['job_id'] : null;
    $message->sendMessage($senderId, $receiverId, $messageText, $machineId, $jobId);
}

$messages = $message->getMessages($_SESSION['user_id']);

function formatTimeAgo($seconds) {
    if ($seconds < 60) {
        return "Just now";
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($seconds < 86400) {
        $hours = floor($seconds / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($seconds / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}
?>

<head>
<link rel="stylesheet" type="text/css" href="styles/admin_dashboard.css">
<script src="scripts/admin_dashboard.js"></script>
</head>

<div class="dashboard-content">
    <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>Manage User Accounts</h2>
            <a href="user_management.php" class="button">Manage Users</a>
        </div>
        <div class="dashboard-section">
            <h2>Manage Machines</h2>
            <a href="machine_management.php" class="button">Manage Machines</a>
        </div>
        <div class="dashboard-section">
            <h2>Factory Performance</h2>
            <a href="factory_performance.php" class="button">View Detailed Performance</a>
        </div>

        <div class="dashboard-section">
            <h2>System Statistics</h2>
            <ul>
                <li>Number of Users: <?php echo $conn->query("SELECT COUNT(*) FROM users")->fetch_assoc()['COUNT(*)']; ?></li>
                <li>Number of Machines: <?php echo $conn->query("SELECT COUNT(*) FROM machines")->fetch_assoc()['COUNT(*)']; ?></li>
                <li>Number of Jobs: <?php echo $conn->query("SELECT COUNT(*) FROM jobs")->fetch_assoc()['COUNT(*)']; ?></li>
            </ul>
        </div>
    </div>

<!-- Factory Performance Section -->
<div class="factory-performance-container">
    <h2>Factory Performance Overview</h2>
    
    <div class="performance-grid">
        <!-- Machine Status Overview -->
        <div class="performance-section">
            <h3>Machine Status Overview</h3>
            <div class="status-cards">
                <?php
                $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN operational_status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN operational_status = 'maintenance' THEN 1 END) as maintenance,
                    COUNT(CASE WHEN operational_status = 'idle' THEN 1 END) as idle,
                    COUNT(CASE WHEN operational_status = 'non-operational' THEN 1 END) as non_operational,
                    COUNT(CASE WHEN operational_status = 'operational' THEN 1 END) as operational
                    FROM machines";
                $result = $conn->query($sql);
                $status = $result->fetch_assoc();
                ?>
                <div class="status-card active">
                    <h4>Active</h4>
                    <span class="count"><?php echo $status['active']; ?></span>
                    <span class="percentage"><?php echo $status['total'] > 0 ? round(($status['active']/$status['total'])*100) : 0; ?>%</span>
                </div>
                <div class="status-card maintenance">
                    <h4>Maintenance</h4>
                    <span class="count"><?php echo $status['maintenance']; ?></span>
                    <span class="percentage"><?php echo $status['total'] > 0 ? round(($status['maintenance']/$status['total'])*100) : 0; ?>%</span>
                </div>
                <div class="status-card idle">
                    <h4>Idle</h4>
                    <span class="count"><?php echo $status['idle']; ?></span>
                    <span class="percentage"><?php echo $status['total'] > 0 ? round(($status['idle']/$status['total'])*100) : 0; ?>%</span>
                </div>
                <div class="status-card non-operational">
                    <h4>(Maintenance) Non-operational</h4>
                    <span class="count"><?php echo $status['non_operational']; ?></span>
                    <span class="percentage"><?php echo $status['total'] > 0 ? round(($status['non_operational']/$status['total'])*100) : 0; ?>%</span>
                </div>
                <div class="status-card operational">
                    <h4>(Maintenance) Operational</h4>
                    <span class="count"><?php echo $status['operational']; ?></span>
                    <span class="percentage"><?php echo $status['total'] > 0 ? round(($status['operational']/$status['total'])*100) : 0; ?>%</span>
                </div>
            </div>
        </div>

        <!-- Recent Machine Activity -->
        <div class="performance-section">
            <h3>Recent Machine Activity</h3>
            <div class="activity-list">
                <?php
                $sql = "SELECT m.machine_name, ml.*, 
                        TIME_TO_SEC(TIMEDIFF(NOW(), ml.timestamp)) as seconds_ago
                        FROM machine_logs ml
                        JOIN machines m ON ml.machine_id = m.id
                        ORDER BY ml.timestamp DESC
                        LIMIT 5";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $timeAgo = formatTimeAgo($row['seconds_ago']);
                    ?>
                    <div class="activity-item">
                        <div class="activity-header">
                            <span class="machine-name"><?php echo htmlspecialchars($row['machine_name']); ?></span>
                            <span class="activity-time"><?php echo $timeAgo; ?></span>
                        </div>
                        <div class="activity-details">
                            <span class="temp">Temp: <?php echo round($row['temperature'], 1); ?>°C</span>
                            <span class="power">Power: <?php echo round($row['power_consumption'], 1); ?> kW</span>
                            <span class="status <?php echo $row['operational_status']; ?>">
                                <?php echo ucfirst($row['operational_status']); ?>
                            </span>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="performance-section">
            <h3>Performance Metrics (30-Day Overview)</h3>
            <div class="metrics-grid">
                <?php
                // Get the latest log date as reference point
                $sql = "SELECT MAX(timestamp) as latest_date FROM machine_logs";
                $result = $conn->query($sql);
                $latest_date = $result->fetch_assoc()['latest_date'];
                
                // Calculate average temperature
                $sql = "SELECT AVG(temperature) as avg_temp 
                       FROM machine_logs 
                       WHERE temperature > 0
                       AND timestamp >= DATE_SUB('$latest_date', INTERVAL 30 DAY)";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $avgTemp = $row['avg_temp'];

                // Calculate average power consumption
                $sql = "SELECT AVG(power_consumption) as avg_power 
                       FROM machine_logs 
                       WHERE power_consumption > 0
                       AND timestamp >= DATE_SUB('$latest_date', INTERVAL 30 DAY)";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $avgPower = $row['avg_power'];

                // Calculate efficiency
                $sql = "SELECT 
                        (COUNT(CASE WHEN operational_status IN ('active', 'operational') THEN 1 END) * 100.0) / 
                        NULLIF(COUNT(*), 0) as efficiency
                        FROM machine_logs 
                        WHERE timestamp >= DATE_SUB('$latest_date', INTERVAL 30 DAY)";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $efficiency = $row['efficiency'];
                ?>
                <div class="metric-card">
                    <h4>Avg Temperature</h4>
                    <span class="value"><?php echo $avgTemp ? number_format($avgTemp, 1) : '0.0'; ?>°C</span>
                    <span class="period">Last 30 days</span>
                </div>
                <div class="metric-card">
                    <h4>Avg Power</h4>
                    <span class="value"><?php echo $avgPower ? number_format($avgPower, 1) : '0.0'; ?> kW</span>
                    <span class="period">Last 30 days</span>
                </div>
                <div class="metric-card">
                    <h4>Efficiency</h4>
                    <span class="value"><?php echo $efficiency ? number_format($efficiency, 1) : '0.0'; ?>%</span>
                    <span class="period">Last 30 days</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Messaging Section -->
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
                        $sql = "SELECT id, username FROM users WHERE role IN ('manager', 'operator', 'auditor')";
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

<script src="scripts/admin_dashboard.js"></script>
</div>

<?php include 'templates/admin_footer.php'; ?>

