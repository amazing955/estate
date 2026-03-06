<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function delete($id) {
        return $this->userModel->delete($id);
    }

    public function isBrokerApproved($id) {
        return $this->userModel->isBrokerApproved($id);
    }

    public function setBrokerApproved($id, $approved) {
        return $this->userModel->setBrokerApproved($id, $approved);
    }

    public function updateRole($id, $role) {
        return $this->userModel->updateRole($id, $role);
    }

    public function all() {
        return $this->userModel->all();
    }

    // get logs for a specific user or all users
    public function allLogs($user_id = null) {
        require_once __DIR__ . '/../models/UserLog.php';
        $logModel = new UserLog();
        if ($user_id) {
            return $logModel->findByUser($user_id);
        }
        return $logModel->all();
    }

    // system-wide logs
    public function systemLogs() {
        require_once __DIR__ . '/../models/SystemLog.php';
        $sys = new SystemLog();
        return $sys->all();
    }

    public function addSystemLog($type, $message) {
        require_once __DIR__ . '/../models/SystemLog.php';
        $sys = new SystemLog();
        return $sys->add($type, $message);
    }

    public function findById($id) {
        return $this->userModel->findById($id);
    }

    public function getActivity($user_id) {
        // gather properties they own, inquiries made, saved properties count
        require_once __DIR__ . '/../models/Property.php';
        require_once __DIR__ . '/../models/Inquiry.php';
        require_once __DIR__ . '/../models/SavedProperty.php';
        $prop = new Property();
        $inq = new Inquiry();
        $save = new SavedProperty();
        $activities = [];
        $activities['properties'] = $prop->findByOwner($user_id);
        $activities['inquiries'] = $inq->findByClient($user_id);
        $activities['saved'] = $save->findByClient($user_id);
        // include login/logout logs
        require_once __DIR__ . '/../models/UserLog.php';
        $logModel = new UserLog();
        $activities['logs'] = $logModel->findByUser($user_id);
        // include login/logout logs
        require_once __DIR__ . '/../models/UserLog.php';
        $logModel = new UserLog();
        $activities['logs'] = $logModel->findByUser($user_id);
        return $activities;
    }
}