<?php
namespace App;
require_once 'db_connection.php';

class Message {
    private $conn;
    const MAX_MESSAGE_LENGTH = 65535; // MySQL text field limit

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function sendMessage($senderId, $receiverId, $messageText, $machineId = null, $jobId = null) {
        // Validate message length
        if (strlen($messageText) > self::MAX_MESSAGE_LENGTH) {
            throw new \Exception('Message exceeds maximum length of ' . self::MAX_MESSAGE_LENGTH . ' characters');
        }

        if (empty(trim($messageText))) {
            throw new \Exception('Message cannot be empty');
        }

        $stmt = $this->conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, machine_id, job_id, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new \Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("iisii", $senderId, $receiverId, $messageText, $machineId, $jobId);
        
        if (!$stmt->execute()) {
            throw new \Exception('Failed to send message: ' . $stmt->error);
        }

        return true;
    }

    public function getMessages($userId) {
        $stmt = $this->conn->prepare("SELECT m.*, u.username as sender_name, mc.machine_name, j.job_name 
                                      FROM messages m 
                                      JOIN users u ON m.sender_id = u.id 
                                      LEFT JOIN machines mc ON m.machine_id = mc.id
                                      LEFT JOIN jobs j ON m.job_id = j.job_id
                                      WHERE m.receiver_id = ? 
                                      ORDER BY m.sent_at DESC");
        if (!$stmt) {
            throw new \Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);
        
        if (!$stmt->execute()) {
            throw new \Exception('Failed to fetch messages: ' . $stmt->error);
        }

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function markAsRead($messageId, $userId) {
        $stmt = $this->conn->prepare("UPDATE messages SET read_at = NOW() WHERE id = ? AND receiver_id = ? AND read_at IS NULL");
        if (!$stmt) {
            throw new \Exception('Failed to prepare statement: ' . $this->conn->error);
        }

        $stmt->bind_param("ii", $messageId, $userId);
        
        if (!$stmt->execute()) {
            throw new \Exception('Failed to mark message as read: ' . $stmt->error);
        }

        return true;
    }
}

?>