<?php
// controllers/ProfileController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/User.php';

class ProfileController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /estate/views/login.php');
            exit;
        }
        $user = $this->userModel->findById($_SESSION['user_id']);
        return $user;
    }

    public function update($data, $file) {
        $id = $_SESSION['user_id'];
        $updates = [];
        if (!empty($data['username'])) $updates['username'] = $data['username'];
        if (!empty($data['email'])) $updates['email'] = $data['email'];

        // handle picture
        if ($file && $file['tmp_name']) {
            $folder = __DIR__ . '/../uploads/images/';
            if (!is_dir($folder)) mkdir($folder,0755,true);
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','gif'])) {
                $filename = 'profile_'.uniqid().".$ext";
                if (move_uploaded_file($file['tmp_name'], $folder.$filename)) {
                    $updates['profile_pic'] = 'uploads/images/'.$filename;
                }
            }
        }
        if (!empty($updates)) {
            $success = $this->userModel->updateProfile($id, $updates);
            if ($success) {
                // update session values if changed
                if (isset($updates['profile_pic'])) {
                    $_SESSION['profile_pic'] = $updates['profile_pic'];
                }
                if (isset($updates['username'])) {
                    $_SESSION['username'] = $updates['username'];
                }
                if (isset($updates['email'])) {
                    $_SESSION['email'] = $updates['email'];
                }
            }
            return $success;
        }
        return false;
    }
}
