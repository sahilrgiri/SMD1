<?php
$page = 'audit_reports';
$page_title = 'Audit Reports';
$back_url = 'auditor_dashboard.php';
include 'templates/auditor_header.php';
?>

<div class="dashboard-content">
    <h1>Generate Audit Reports</h1>
    
    <div class="dashboard-section">
        <h2>Select Report Type</h2>
        <form action="generate_report.php" method="post" class="report-form">
            <div class="form-group">
                <label for="report_type">Report Type:</label>
                <select name="report_type" id="report_type" required>
                    <option value="">Select Report Type</option>
                    <option value="machines">Machines Report</option>
                    <option value="users">Users Report</option>
                    <option value="jobs">Jobs Report</option>
                    <option value="messages">Messages Report</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">From Date:</label>
                <input type="date" name="date_from" id="date_from">
            </div>
            
            <div class="form-group">
                <label for="date_to">To Date:</label>
                <input type="date" name="date_to" id="date_to">
            </div>
            
            <button type="submit" name="generate_report" class="button">Generate PDF Report</button>
        </form>
    </div>
</div>

<?php include 'templates/auditor_footer.php'; ?>
