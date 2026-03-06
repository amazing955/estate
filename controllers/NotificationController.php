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
}
