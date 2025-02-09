<?php
session_start();
require_once 'db_connection.php';
require_once 'Notification.php';

use App\Notification\Notification;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $machineId = (int)$_POST['id'];
    $status = $_POST['status'];
    
    // Validate status
    if (!in_array($status, ['operational', 'non-operational'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE machines SET operational_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $machineId);
    
    if ($stmt->execute()) {
        // Create notification
        $notification = new Notification($conn);
        $message = "Machine #$machineId status changed to $status";
        $notificationId = $notification->createNotification('machine_status', $message);
        $notification->addRecipient($notificationId, $_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'status' => $status,
            'message' => 'Status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Database error: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid request'
    ]);
}
