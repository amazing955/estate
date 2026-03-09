<?php
// controllers/TourController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Property.php';

class TourController {
    private $tourModel;
    private $notificationModel;
    private $propertyModel;

    public function __construct() {
        $this->tourModel = new Tour();
        $this->notificationModel = new Notification();
        $this->propertyModel = new Property();
    }

    public function requestTour($property_id, $date, $phone, $email, $message='') {
        $client_id = $_SESSION['user_id'];
        $success = $this->tourModel->add($property_id, $client_id, $date, $phone, $email, $message);
        if (!$success) return false;
        // system log
        require_once __DIR__ . '/UserController.php';
        $uc = new UserController();
        $uc->addSystemLog('tour', "User {$client_id} requested tour for property {$property_id} on {$date}");

        // notify owner and admin with contact details
        $property = $this->propertyModel->findById($property_id);
        $owner_id = $property['owner_id'];
        $clientName = $_SESSION['username'];
        $msg = "Tour request by " . htmlspecialchars($clientName) . " on {$date}. Contact: " . htmlspecialchars($phone) . ", " . htmlspecialchars($email) . ".";
        if(!empty($message)) {
            $msg .= " Message: " . htmlspecialchars($message);
        }
        // include submit-to-owner if reposted
        if (!empty($property['original_owner_id']) && $property['original_owner_id'] != $owner_id) {
            $link = "/estate/controllers/index.php?action=submitToOwner&type=tour&property_id={$property_id}&tour_id=" . $this->tourModel->getLastInsertId();
            $msg .= " <a href='{$link}' class='btn btn-sm btn-secondary'>Submit to Owner</a>";
        }
        $this->notificationModel->create($owner_id, $msg, $property_id);
        // email owner
        require_once __DIR__ . '/NotificationController.php';
        $nc = new NotificationController();
        $nc->sendEmail($owner_id, 'New Tour Request', $msg);
        // send copy to all admins (query via User model)
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $admins = array_filter($userModel->all(), fn($u)=>$u['role']==='admin');
        foreach ($admins as $adm) {
            $this->notificationModel->create($adm['id'], "[Admin copy] " . $msg, $property_id);
            $nc->sendEmail($adm['id'], 'New Tour Request (admin copy)', "[Admin copy] " . $msg);
        }
        return true;
    }

    public function listByProperty($property_id) {
        return $this->tourModel->findByProperty($property_id);
    }

    public function findById($id) {
        return $this->tourModel->findById($id);
    }
}