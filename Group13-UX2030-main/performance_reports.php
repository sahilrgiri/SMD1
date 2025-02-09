<?php
$page = 'performance_reports';
$page_title = 'Performance Reports';
$back_url = 'auditor_dashboard.php';
include 'templates/auditor_header.php';
require_once 'db_connection.php';

// Get date range if 
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Function to get machine performance metrics
function getMachinePerformance($conn, $start_date, $end_date) {
    $sql = "SELECT 
                m.machine_name,
                COUNT(*) as total_logs,
                AVG(ml.temperature) as avg_temp,
                AVG(ml.power_consumption) as avg_power,
                COUNT(CASE WHEN ml.operational_status = 'active' THEN 1 END) as active_time,
                COUNT(CASE WHEN ml.operational_status = 'maintenance' THEN 1 END) as maintenance_time,
                COUNT(CASE WHEN ml.operational_status = 'non-operational' THEN 1 END) as downtime
            FROM machines m
            LEFT JOIN machine_logs ml ON m.id = ml.machine_id
            WHERE ml.timestamp BETWEEN ? AND ?
            GROUP BY m.id, m.machine_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to get job completion statistics
function getJobStatistics($conn, $start_date, $end_date) {
    $sql = "SELECT 
                COUNT(*) as total_jobs,
                COUNT(CASE WHEN end_date IS NOT NULL THEN 1 END) as completed_jobs,
                COALESCE(AVG(UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(start_date)) / 3600, 0) as avg_duration
            FROM jobs
            WHERE start_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Calculate completion percentage
    $completion_percentage = $result['total_jobs'] > 0 ? $result['completed_jobs'] / $result['total_jobs'] * 100 : 0;
    
    return [
        'total_jobs' => $result['total_jobs'],
        'completed_jobs' => $result['completed_jobs'],
        'avg_duration' => $result['avg_duration'],
        'completion_percentage' => $completion_percentage,
    ];
}
// Function to get operator performance
function getOperatorPerformance($conn, $start_date, $end_date) {
    $sql = "SELECT 
                u.username,
                COUNT(j.job_id) as total_jobs,
                COUNT(CASE WHEN j.end_date IS NOT NULL THEN 1 END) as completed_jobs,
                AVG(TIMESTAMPDIFF(HOUR, j.start_date, IFNULL(j.end_date, NOW()))) as avg_job_duration
            FROM users u
            LEFT JOIN jobs j ON u.id = j.operator_id
            WHERE u.role = 'operator'
            AND (j.start_date BETWEEN ? AND ? OR j.start_date IS NULL)
            GROUP BY u.id, u.username";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get the data
$machine_performance = getMachinePerformance($conn, $start_date, $end_date);
$job_statistics = getJobStatistics($conn, $start_date, $end_date);
$operator_performance = getOperatorPerformance($conn, $start_date, $end_date);
?>

<div class="dashboard-content">
    <h1>Performance Reports</h1>

    <!-- Date Range Filter -->
    <div class="filter-section">
        <form method="GET" class="date-filter-form">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit" class="button">Apply Filter</button>
        </form>
    </div>

    <!-- Machine Performance Section -->
    <div class="dashboard-section">
        <h2>Machine Performance</h2>
        <div class="table-responsive">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Machine Name</th>
                        <th>Total Logs</th>
                        <th>Avg Temperature</th>
                        <th>Avg Power</th>
                        <th>Active Time</th>
                        <th>Maintenance Time</th>
                        <th>Downtime</th>
                        <th>Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($machine_performance as $machine): ?>
                        <?php 
                        $total_time = $machine['active_time'] + $machine['maintenance_time'] + $machine['downtime'];
                        $efficiency = $total_time > 0 ? ($machine['active_time'] / $total_time) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($machine['machine_name']); ?></td>
                            <td><?php echo $machine['total_logs']; ?></td>
                            <td><?php echo number_format($machine['avg_temp'], 2); ?>°C</td>
                            <td><?php echo number_format($machine['avg_power'], 2); ?> kW</td>
                            <td><?php echo $machine['active_time']; ?> hrs</td>
                            <td><?php echo $machine['maintenance_time']; ?> hrs</td>
                            <td><?php echo $machine['downtime']; ?> hrs</td>
                            <td><?php echo number_format($efficiency, 2); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Job Statistics Section -->
    <div class="dashboard-section">
        <h2>Job Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Jobs</h3>
                <p><?php echo $job_statistics['total_jobs']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completed Jobs</h3>
                <p><?php echo $job_statistics['completed_jobs']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Average Duration</h3>
                <p><?php echo $job_statistics['avg_duration'] !== null ? number_format($job_statistics['avg_duration'], 2) : 'N/A'; ?> hours</p>
            </div>
            <div class="stat-card">
                <h3>Completion Rate</h3>
                <p><?php echo $job_statistics['total_jobs'] > 0 ? 
                    number_format(($job_statistics['completed_jobs'] / $job_statistics['total_jobs']) * 100, 2) : 0; ?>%</p>
            </div>
        </div>
    </div>

    <!-- Operator Performance Section -->
    <div class="dashboard-section">
        <h2>Operator Performance</h2>
        <div class="table-responsive">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th>Total Jobs</th>
                        <th>Completed Jobs</th>
                        <th>Completion Rate</th>
                        <th>Avg Job Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($operator_performance as $operator): ?>
                        <?php 
                        $completion_rate = $operator['total_jobs'] > 0 ? 
                            ($operator['completed_jobs'] / $operator['total_jobs']) * 100 : 0;
                        ?>
                        
                        <tr>
                            <td><?php echo htmlspecialchars($operator['username']); ?></td>
                            <td><?php echo $operator['total_jobs']; ?></td>
                            <td><?php echo $operator['completed_jobs']; ?></td>
                            <td><?php echo number_format($completion_rate, 2); ?>%</td>
                            <td><?php echo number_format($operator['avg_job_duration'], 2); ?> hours</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="export-section">
        <form action="generate_report.php" method="post">
            <input type="hidden" name="report_type" value="performance">
            <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
            <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
            <button type="submit" class="button">Export as PDF</button>
        </form>
    </div>
</div>

<?php include 'templates/auditor_footer.php'; ?>
