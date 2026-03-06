<?php
// controllers/NotificationController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
    }

    public function getUserNotifications($user_id) {
        return $this->notificationModel->findByUser($user_id);
    }

    public function markRead($id) {
        return $this->notificationModel->markRead($id);
    }

    public function create($user_id, $message, $property_id = null) {
        return $this->notificationModel->create($user_id, $message, $property_id);
    }

    /**
     * Send a broadcast message to all users of a specific role.
     * @param string $category 'client', 'owner', 'broker', or 'all'
     * @param string $message The message to send
     * @return int Number of users who received the message
     */
    public function sendBroadcast($category, $message) {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $allUsers = $userModel->all();
        
        $recipients = [];
        if ($category === 'all') {
            $recipients = $allUsers;
        } else {
            $recipients = array_filter($allUsers, fn($u) => $u['role'] === $category);
        }
        
        $count = 0;
        foreach ($recipients as $user) {
            if ($this->create($user['id'], htmlspecialchars($message))) {
                $count++;
            }
        }
        return $count;
    }}