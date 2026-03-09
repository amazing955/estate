<?php
// controllers/CollaborationController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Collaboration.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';

class CollaborationController {
    private $collabModel;
    private $notifModel;
    private $userModel;

    public function __construct() {
        $this->collabModel = new Collaboration();
        $this->notifModel = new Notification();
        $this->userModel = new User();
    }

    public function request($owner_id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'broker') {
            return false;
        }
        $broker_id = $_SESSION['user_id'];
        $ok = $this->collabModel->request($broker_id, $owner_id);
        if ($ok) {
            // notify owner
            $broker = $this->userModel->findById($broker_id);
            $msg = "Broker {$broker['username']} wants to collaborate with you.";
            $this->notifModel->create($owner_id, $msg);
            // system log
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('collaboration', "Broker {$broker['username']} requested collaboration with owner {$owner_id}");
            // email broker about request success
            $role = $_SESSION['role'] ?? '';
            if (in_array($role, ['admin','owner','broker'])) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                $nc->sendEmail($broker_id,
                    'Dashboard Update – Collaboration Requested',
                    "Your collaboration request to owner ID {$owner_id} has been sent.");
            }
        }
        return $ok;
    }

    public function respond($id, $decision) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
            return false;
        }
        $collab = $this->collabModel->getById($id);
        if (!$collab || $collab['owner_id'] != $_SESSION['user_id']) {
            return false;
        }
        $status = $decision === 'accept' ? 'accepted' : 'rejected';
        $this->collabModel->respond($id, $status);
        // notify broker
        $owner = $this->userModel->findById($_SESSION['user_id']);
        $broker = $this->userModel->findById($collab['broker_id']);
        $msg = "Owner {$owner['username']} has {$status} your collaboration request.";
        $this->notifModel->create($broker['id'], $msg);
        // system log
        require_once __DIR__ . '/UserController.php';
        $uc = new UserController();
        $uc->addSystemLog('collaboration', "Owner {$owner['username']} {$status} request from broker {$broker['username']}");
        // email owner about the response action
        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['admin','owner','broker'])) {
            require_once __DIR__ . '/NotificationController.php';
            $nc = new NotificationController();
            $nc->sendEmail($_SESSION['user_id'],
                'Dashboard Update – Collaboration ' . ucfirst($status),
                "You have {$status} the collaboration request from broker {$broker['username']}.");
        }
        return true;
    }

    public function pendingForOwner($owner_id) {
        return $this->collabModel->findPendingForOwner($owner_id);
    }

    public function acceptedForBroker($broker_id) {
        return $this->collabModel->findAcceptedForBroker($broker_id);
    }

    public function pendingForBroker($broker_id) {
        return $this->collabModel->findPendingForBroker($broker_id);
    }
}
