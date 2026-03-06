<?php
// controllers/InquiryController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Inquiry.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Property.php';

class InquiryController {
    private $inquiryModel;
    private $notificationModel;
    private $propertyModel;

    public function __construct() {
        $this->inquiryModel = new Inquiry();
        $this->notificationModel = new Notification();
        $this->propertyModel = new Property();
    }

    public function makeInquiry($property_id, $message) {
        $client_id = $_SESSION['user_id'];
        $success = $this->inquiryModel->create($property_id, $client_id, $message);
        if (!$success) return false;
        // log system event
        require_once __DIR__ . '/UserController.php';
        $uc = new UserController();
        $uc->addSystemLog('inquiry', "User {$client_id} inquired about property {$property_id}");

        // notify owner and admin
        $property = $this->propertyModel->findById($property_id);
        $owner_id = $property['owner_id'];
        $client = htmlspecialchars($_SESSION['username']);
        $msg = "Client {$client} is interested in your property: " . htmlspecialchars($property['title']);
        // if this property was reposted, include submit-to-owner link for broker
        if (!empty($property['original_owner_id']) && $property['original_owner_id'] != $owner_id) {
            $link = "/estate/controllers/index.php?action=submitToOwner&type=inquiry&property_id={$property_id}&inq_id=" . $this->inquiryModel->getLastInsertId();
            $msg .= " <a href='{$link}' class='btn btn-sm btn-secondary'>Submit to Owner</a>";
        }
        $this->notificationModel->create($owner_id, $msg, $property_id);
        // admin notification
        $this->notificationModel->create(1, "{$msg} (owner id {$owner_id})", $property_id);
        return true;
    }

    public function listByProperty($property_id) {
        return $this->inquiryModel->findByProperty($property_id);
    }

    public function listByClient($client_id) {
        return $this->inquiryModel->findByClient($client_id);
    }

    public function all() {
        return $this->inquiryModel->all();
    }

    public function delete($id) {
        return $this->inquiryModel->deleteById($id);
    }

    public function findById($id) {
        return $this->inquiryModel->findById($id);
    }
}
