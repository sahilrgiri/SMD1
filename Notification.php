<?php
namespace App\Notification;
use mysqli_sql_exception;

class Notification {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createNotification($type, $message) {
        $sql = "INSERT INTO notifications (type, message) VALUES (?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $type, $message);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function addRecipient($notificationId, $userId) {
        $sql = "INSERT INTO notification_recipients (notification_id, user_id) VALUES (?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
    }

    public function getUnreadNotifications($userId) {
        try {
            $sql = "SELECT nr.notification_id, n.* 
                    FROM notification_recipients nr
                    JOIN notifications n ON nr.notification_id = n.id
                    WHERE nr.user_id = ? AND nr.read_at IS NULL";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            // Log the error and return an empty array or throw a custom exception
            error_log("Database error: ". $e->getMessage());
            return [];
        }
    }
}
