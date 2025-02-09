<?php
require_once 'db_connection.php';
require_once 'Notification.php';

use App\Notification\Notification;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['machine_id'], $_POST['status'])) {
    $machineId = $_POST['machine_id'];
    $status = $_POST['status'];
    
    // Update machine status
    $stmt = $conn->prepare("UPDATE machines SET operational_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $machineId);
    
    if ($stmt->execute()) {
        // Create notification
        $notification = new Notification($conn);
        $message = "Machine #$machineId status changed to $status";
        $notificationId = $notification->createNotification('machine_status', $message);
        $notification->addRecipient($notificationId, $_SESSION['user_id']);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
