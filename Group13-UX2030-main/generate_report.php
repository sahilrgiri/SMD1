<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

require_once('tcpdf/tcpdf.php');
require_once('db_connection.php');

if (!isset($_POST['report_type'])) {
    header('Location: audit_reports.php');
    exit;
}

// Create PDF class with custom Header and Footer
class PDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'SMD System Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

try {
    // Create new PDF document
    $pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('SMD System');
    $pdf->SetAuthor('SMD Admin');
    $pdf->SetTitle('SMD Report - ' . ucfirst($_POST['report_type']));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Add a page
    $pdf->AddPage();

    // Get report content based on type
    $date_condition = '';
    if (isset($_POST['date_from']) && isset($_POST['date_to'])) {
        $date_from = $conn->real_escape_string($_POST['date_from']);
        $date_to = $conn->real_escape_string($_POST['date_to']);
        if (!empty($date_from) && !empty($date_to)) {
            $date_condition = " WHERE timestamp BETWEEN '$date_from' AND '$date_to'";
        }
    }

    function generateMachinesReport($conn, $date_condition) {
        $html = '<h1 style="color: #4a69bd;">Machines Report</h1>';
        $html .= '<h2 style="color: #666;">Generated on: ' . date('Y-m-d H:i:s') . '</h2>';
        
        $sql = "SELECT * FROM machines";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $html .= '<h3>Machine Status Overview</h3>';
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Machine ID</th>
                            <th>Machine Name</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['id']) . '</td>
                            <td>' . htmlspecialchars($row['machine_name']) . '</td>
                            <td>' . htmlspecialchars($row['operational_status']) . '</td>
                            <td>' . (isset($row['last_updated']) ? htmlspecialchars($row['last_updated']) : '-') . '</td>
                        </tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p>No machines found in the database.</p>';
        }
        
        return $html;
    }

    function generateUsersReport($conn) {
        $html = '<h1 style="color: #4a69bd;">Users Report</h1>';
        $html .= '<h2 style="color: #666;">Generated on: ' . date('Y-m-d H:i:s') . '</h2>';
        
        $sql = "SELECT id, username, role, email FROM users";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Email</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['id']) . '</td>
                            <td>' . htmlspecialchars($row['username']) . '</td>
                            <td>' . htmlspecialchars($row['role']) . '</td>
                            <td>' . htmlspecialchars($row['email']) . '</td>
                        </tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p>No users found in the database.</p>';
        }
        
        return $html;
    }

    function generateJobsReport($conn, $date_condition) {
        $html = '<h1 style="color: #4a69bd;">Jobs Report</h1>';
        $html .= '<h2 style="color: #666;">Generated on: ' . date('Y-m-d H:i:s') . '</h2>';
        
        $sql = "SELECT j.*, u.username as operator_name, m.machine_name 
                FROM jobs j 
                LEFT JOIN users u ON j.operator_id = u.id 
                LEFT JOIN machines m ON j.machine_id = m.id" . 
                ($date_condition ? str_replace('timestamp', 'start_date', $date_condition) : '');
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Job ID</th>
                            <th>Job Name</th>
                            <th>Operator</th>
                            <th>Machine</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['job_id']) . '</td>
                            <td>' . htmlspecialchars($row['job_name']) . '</td>
                            <td>' . htmlspecialchars($row['operator_name']) . '</td>
                            <td>' . htmlspecialchars($row['machine_name']) . '</td>
                            <td>' . htmlspecialchars($row['start_date']) . '</td>
                            <td>' . (isset($row['end_date']) ? htmlspecialchars($row['end_date']) : '-') . '</td>
                            <td>' . htmlspecialchars($row['status']) . '</td>
                        </tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p>No jobs found for the specified period.</p>';
        }
        
        return $html;
    }

    function generatePerformanceReport($conn, $start_date, $end_date) {
        $html = '<h1 style="color: #4a69bd;">Performance Report</h1>';
        $html .= '<h2 style="color: #666;">Period: ' . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) . '</h2>';
        
        // Machine Performance
        $sql = "SELECT m.machine_name, 
                COUNT(j.job_id) as total_jobs,
                AVG(TIMESTAMPDIFF(HOUR, j.start_date, COALESCE(j.end_date, NOW()))) as avg_job_duration
                FROM machines m
                LEFT JOIN jobs j ON m.id = j.machine_id
                WHERE j.start_date BETWEEN ? AND ?
                GROUP BY m.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $html .= '<h3>Machine Performance</h3>';
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Machine Name</th>
                            <th>Total Jobs</th>
                            <th>Average Job Duration (Hours)</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['machine_name']) . '</td>
                            <td>' . htmlspecialchars($row['total_jobs']) . '</td>
                            <td>' . number_format($row['avg_job_duration'], 2) . '</td>
                        </tr>';
            }
            $html .= '</table>';
        }

        // Operator Performance
        $sql = "SELECT u.username,
                COUNT(j.job_id) as total_jobs,
                COUNT(CASE WHEN j.status = 'completed' THEN 1 END) as completed_jobs
                FROM users u
                LEFT JOIN jobs j ON u.id = j.operator_id
                WHERE u.role = 'operator'
                AND j.start_date BETWEEN ? AND ?
                GROUP BY u.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $html .= '<h3>Operator Performance</h3>';
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Operator Name</th>
                            <th>Total Jobs</th>
                            <th>Completed Jobs</th>
                            <th>Completion Rate</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $completion_rate = ($row['total_jobs'] > 0) ? 
                    ($row['completed_jobs'] / $row['total_jobs'] * 100) : 0;
                
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['username']) . '</td>
                            <td>' . htmlspecialchars($row['total_jobs']) . '</td>
                            <td>' . htmlspecialchars($row['completed_jobs']) . '</td>
                            <td>' . number_format($completion_rate, 2) . '%</td>
                        </tr>';
            }
            $html .= '</table>';
        }
        
        return $html;
    }

    function generateFactoryPerformanceReport($conn, $start_date, $end_date) {
        $html = '<h1 style="color: #4a69bd;">Factory Performance Report</h1>';
        $html .= '<h2 style="color: #666;">Period: ' . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) . '</h2>';
        
        // Overall Statistics
        $sql = "SELECT 
                COUNT(*) as total_jobs,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_jobs,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as ongoing_jobs,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_jobs
                FROM jobs
                WHERE start_date BETWEEN ? AND ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $html .= '<h3>Overall Factory Performance</h3>';
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Total Jobs</th>
                            <th>Completed Jobs</th>
                            <th>Ongoing Jobs</th>
                            <th>Pending Jobs</th>
                            <th>Completion Rate</th>
                        </tr>';
            
            $completion_rate = ($row['total_jobs'] > 0) ? 
                ($row['completed_jobs'] / $row['total_jobs'] * 100) : 0;
            
            $html .= '<tr>
                        <td>' . htmlspecialchars($row['total_jobs']) . '</td>
                        <td>' . htmlspecialchars($row['completed_jobs']) . '</td>
                        <td>' . htmlspecialchars($row['ongoing_jobs']) . '</td>
                        <td>' . htmlspecialchars($row['pending_jobs']) . '</td>
                        <td>' . number_format($completion_rate, 2) . '%</td>
                    </tr>';
            $html .= '</table>';
        }

        // Machine Utilization
        $sql = "SELECT m.machine_name,
                COUNT(j.job_id) as total_jobs,
                SUM(CASE WHEN j.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
                AVG(TIMESTAMPDIFF(HOUR, j.start_date, COALESCE(j.end_date, NOW()))) as avg_job_duration
                FROM machines m
                LEFT JOIN jobs j ON m.id = j.machine_id
                WHERE j.start_date BETWEEN ? AND ?
                GROUP BY m.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $html .= '<h3>Machine Utilization</h3>';
            $html .= '<table border="1" cellpadding="5">
                        <tr style="background-color: #f5f5f5;">
                            <th>Machine Name</th>
                            <th>Total Jobs</th>
                            <th>Completed Jobs</th>
                            <th>Average Job Duration (Hours)</th>
                        </tr>';
            
            while ($row = $result->fetch_assoc()) {
                $html .= '<tr>
                            <td>' . htmlspecialchars($row['machine_name']) . '</td>
                            <td>' . htmlspecialchars($row['total_jobs']) . '</td>
                            <td>' . htmlspecialchars($row['completed_jobs']) . '</td>
                            <td>' . number_format($row['avg_job_duration'], 2) . '</td>
                        </tr>';
            }
            $html .= '</table>';
        }
        
        return $html;
    }

    // Generate report content based on type
    switch ($_POST['report_type']) {
        case 'machines':
            $content = generateMachinesReport($conn, $date_condition);
            break;
        case 'users':
            $content = generateUsersReport($conn);
            break;
        case 'jobs':
            $content = generateJobsReport($conn, $date_condition);
            break;
        case 'performance':
            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');
            $content = generatePerformanceReport($conn, $start_date, $end_date);
            break;    
        case 'factory_performance':
            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-7 days'));
            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');
            $content = generateFactoryPerformanceReport($conn, $start_date, $end_date);
            break;
        default:
            throw new Exception('Invalid report type specified');
    }

    // Write content to PDF
    $pdf->writeHTML($content, true, false, true, false, '');

    // Clear any previous output
    if (ob_get_length()) ob_clean();

    // Set response headers
    header('Content-Type: application/pdf');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

    // Output PDF
    $pdf->Output('smd_report_' . $_POST['report_type'] . '_' . date('Y-m-d') . '.pdf', 'I');
    
} catch (Exception $e) {
    // Log error
    error_log('PDF Generation Error: ' . $e->getMessage());
    
    // Clear output buffer
    if (ob_get_length()) ob_clean();
    
    // Return error message
    header('Content-Type: text/html; charset=utf-8');
    echo 'Error generating PDF report: ' . $e->getMessage();
}

exit;
?>
