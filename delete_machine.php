<?php
$page = 'machine_management';
$page_title = 'Delete Machine';
include 'templates/admin_header.php';

// Check if user has admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

// Get machine ID from URL
$machine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($machine_id === 0) {
    header("Location: machine_management.php?error=Invalid machine ID");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("DELETE FROM machine_logs WHERE machine_id = ?");
    $stmt->bind_param("i", $machine_id);
    $stmt->execute();
    
    
    $stmt = $conn->prepare("DELETE FROM machines WHERE id = ?");
    $stmt->bind_param("i", $machine_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    header("Location: machine_management.php?success=Machine and related logs deleted successfully");
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: machine_management.php?error=" . urlencode("Error deleting machine: " . $e->getMessage()));
}

exit();
?>
