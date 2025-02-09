<?php
$page = 'factory_performance';
$page_title = 'Factory Performance';
$back_url = 'admin_dashboard.php';
$additionalStyles = '<link rel="stylesheet" href="styles/factory_performance.css">';
include 'templates/admin_header.php';
// Get date range from URL parameters or set defaults
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-365 days')); // Changed to show last year
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Function to get machine performance data
function getMachinePerformance($conn, $start_date, $end_date) {
    $sql = "SELECT 
        m.machine_name,
        AVG(NULLIF(ml.temperature, 0)) as avg_temp,
        MAX(NULLIF(ml.temperature, 0)) as max_temp,
        MIN(NULLIF(ml.temperature, 0)) as min_temp,
        AVG(NULLIF(ml.power_consumption, 0)) as avg_power,
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

// Function to get job statistics
function getJobStatistics($conn, $start_date, $end_date) {

    $sql = "SELECT 
        COUNT(*) as total_jobs,
        COUNT(CASE WHEN end_date IS NOT NULL THEN 1 END) as completed_jobs,
        COALESCE(AVG(NULLIF(TIMESTAMPDIFF(HOUR, start_date, IFNULL(end_date, NOW())), 0)), 0) as avg_duration
    FROM jobs";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get the performance data
$machine_performance = getMachinePerformance($conn, $start_date, $end_date);
$job_statistics = getJobStatistics($conn, $start_date, $end_date);
?>

<div class="dashboard-content">
    <h1>Factory Performance Analysis</h1>

    <!-- Date Range Filter -->
    <div class="dashboard-section">
        <h2>Date Range Selection</h2>
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

    <!-- Overall Statistics -->
    <div class="dashboard-section">
        <h2>Performance Overview</h2>
        <div class="stat-cards">
            <div class="stat-card">
                <h3>Total Jobs</h3>
                <p><?php echo $job_statistics['total_jobs']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completed Jobs</h3>
                <p><?php echo $job_statistics['completed_jobs']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Average Job Duration</h3>
                <p><?php echo number_format($job_statistics['avg_duration'], 1); ?> hours</p>
            </div>
        </div>
    </div>

    <!-- Detailed Machine Performance -->
    <div class="dashboard-section">
        <h2>Machine Performance Details</h2>
        <div class="table-responsive">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>Machine Name</th>
                        <th>Avg Temp (°C)</th>
                        <th>Max Temp (°C)</th>
                        <th>Min Temp (°C)</th>
                        <th>Avg Power (kW)</th>
                        <th>Active Time (hrs)</th>
                        <th>Maintenance (hrs)</th>
                        <th>Downtime (hrs)</th>
                        <th>Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($machine_performance as $machine): 
                        $total_time = $machine['active_time'] + $machine['maintenance_time'] + $machine['downtime'];
                        $efficiency = $total_time > 0 ? ($machine['active_time'] / $total_time) * 100 : 0;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($machine['machine_name']); ?></td>
                            <td><?php echo number_format(isset($machine['avg_temp']) ? $machine['avg_temp'] : 0, 1); ?></td>
                            <td><?php echo number_format(isset($machine['max_temp']) ? $machine['max_temp'] : 0, 1); ?></td>
                            <td><?php echo number_format(isset($machine['min_temp']) ? $machine['min_temp'] : 0, 1); ?></td>
                            <td><?php echo number_format(isset($machine['avg_power']) ? $machine['avg_power'] : 0, 1); ?></td>
                            <td><?php echo $machine['active_time']; ?></td>
                            <td><?php echo $machine['maintenance_time']; ?></td>
                            <td><?php echo $machine['downtime']; ?></td>
                            <td><?php echo number_format($efficiency, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Options -->
    <div class="dashboard-section">
        <h2>Export Report</h2>
        <form action="generate_report.php" method="post">
            <input type="hidden" name="report_type" value="factory_performance">
            <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
            <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
            <button type="submit" class="button">Export as PDF</button>
        </form>
    </div>
</div>

<?php include 'templates/admin_footer.php'; ?>
