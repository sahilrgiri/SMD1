<?php
session_start();
require_once 'db_connection.php';
require_once 'Message.php';

use App\Message;

// Authentication check for managers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message_text = $_POST['message'];
    
    $message = new Message($conn);
    if ($message->sendMessage($_SESSION['user_id'], $receiver_id, $message_text)) {
        $success_message = "Message sent successfully!";
    } else {
        $error_message = "Failed to send message.";
    }
}

$message = new Message($conn);
$managerId = $_SESSION['user_id'];
$messages = $message->getMessages($managerId);
// Pagination Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$page_title = 'Manager Dashboard';
include 'templates/manager_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <style>
        .dashboard-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .message-form {
            margin-top: 20px;
        }

        .message-form select,
        .message-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .message-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .message-form button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message-form button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .dashboard-section {
                overflow-x: auto;
            }
        }
        .message-form-container {
            margin-bottom: 20px;
        }

        .messages-display {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .messages-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .message-item {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }

        .message-item.sent {
            background-color: #e3effd;
            margin-left: 20px;
        }

        .message-item.received {
            background-color: #f5f5f5;
            margin-right: 20px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #666;
        }

        .message-content {
            word-break: break-word;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .message-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
            resize: vertical;
        }

        .message-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .message-form button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-content">
        <h1>Welcome, Manager <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

        <!-- Factory Performance Overview -->
        <div class="dashboard-section">
            <h2>Factory Performance Overview</h2>
            <?php
            $performance_sql = "SELECT 
                machine_name, 
                AVG(temperature) AS avg_temp, 
                AVG(power_consumption) AS avg_power,
                COUNT(CASE WHEN operational_status = 'active' THEN 1 END) AS active_count,
                COUNT(CASE WHEN operational_status = 'maintenance' THEN 1 END) AS maintenance_count,
                COUNT(CASE WHEN operational_status = 'idle' THEN 1 END) AS idle_count
                FROM factory_log
                GROUP BY machine_name
                LIMIT $limit OFFSET $offset";
            
            $result = $conn->query($performance_sql);

            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Machine</th>
                        <th>Avg Temp</th>
                        <th>Avg Power</th>
                        <th>Active</th>
                        <th>Maintenance</th>
                        <th>Idle</th>
                    </tr>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["machine_name"]) . "</td>";
                    echo "<td>" . round($row["avg_temp"], 2) . "°C</td>";
                    echo "<td>" . round($row["avg_power"], 2) . " kW</td>";
                    echo "<td>" . $row["active_count"] . "</td>";
                    echo "<td>" . $row["maintenance_count"] . "</td>";
                    echo "<td>" . $row["idle_count"] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No machine data available.</p>";
            }
            ?>
        </div>

        <!-- All Jobs Section -->
        <div class="dashboard-section">
            <h2>All Jobs</h2>
            <?php
            $jobs_sql = "SELECT 
                jobs.job_name,
                machines.machine_name,
                users.username AS operator_name,
                jobs.start_date,
                jobs.end_date,
                jobs.priority
                FROM jobs
                JOIN machines ON jobs.machine_id = machines.id
                JOIN users ON jobs.operator_id = users.id
                ORDER BY jobs.priority ASC, jobs.start_date DESC";

            $jobs_result = $conn->query($jobs_sql);

            if ($jobs_result && $jobs_result->num_rows > 0) {
                echo "<table>";
                echo "<tr>
                        <th>Job Name</th>
                        <th>Machine</th>
                        <th>Operator</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Priority</th>
                    </tr>";
                while($row = $jobs_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["job_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["machine_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["operator_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["start_date"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["end_date"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["priority"]) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No jobs available.</p>";
            }
            ?>
        </div>

    
    
       <div class="dashboard-section">
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
    </div>
</body>
</html>