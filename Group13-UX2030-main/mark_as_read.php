<?php
session_start();
require_once 'db_connection.php';
require_once 'Message.php';

use App\Message;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $message = new Message($conn);
    $success = $message->markAsRead($_POST['message_id'], $_SESSION['user_id']);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
