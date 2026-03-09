<?php
// controllers/AdvertController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Advert.php';

class AdvertController {
    private $advertModel;

    public function __construct() {
        $this->advertModel = new Advert();
    }

    public function create($data, $file) {
        $title = $data['title'];
        $link = $data['link'];
        $position = $data['position'];
        $expiry = $data['expiry_date'] ?: null;
        $telephone = $data['telephone'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;

        // handle file upload
        if ($file && $file['tmp_name']) {
            $allowed = ['jpg','jpeg','png','gif'];
            $folder = __DIR__ . '/../uploads/images/';
            if (!is_dir($folder)) mkdir($folder, 0755, true);
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) return false;
            $filename = uniqid() . ".{$ext}";
            if (move_uploaded_file($file['tmp_name'], $folder . $filename)) {
                $path = 'uploads/images/' . $filename;
                $res = $this->advertModel->create($title, $path, $link, $position, $expiry, $user_id, $telephone);
                if ($res) {
                    require_once __DIR__ . '/UserController.php';
                    $uc = new UserController();
                    $uc->addSystemLog('advert', "User {$user_id} submitted advert '{$title}' for approval");
                    // email actor if appropriate
                    $role = $_SESSION['role'] ?? '';
                    if (in_array($role, ['admin','owner','broker'])) {
                        require_once __DIR__ . '/NotificationController.php';
                        $nc = new NotificationController();
                        $nc->sendEmail($user_id,
                            'Dashboard Update – Advert Submitted',
                            "Your advert titled '{$title}' has been submitted successfully.");
                    }
                }
                return $res;
            }
        }
        return false;
    }

    public function approve($id, $expiry_date = null) {
        $res = $this->advertModel->approve($id, $_SESSION['user_id'] ?? null, $expiry_date);
        if ($res) {
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('advert', "Admin {$_SESSION['user_id']} approved advert {$id}");
            // notify admin by email as well
            $role = $_SESSION['role'] ?? '';
            if (in_array($role, ['admin','owner','broker'])) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                $nc->sendEmail($_SESSION['user_id'],
                    'Dashboard Update – Advert Approved',
                    "You have approved advert ID {$id}.");
            }
        }
        return $res;
    }

    public function reject($id) {
        $res = $this->advertModel->reject($id);
        if ($res) {
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('advert', "Admin {$_SESSION['user_id']} rejected advert {$id}");
            // also email
            $role = $_SESSION['role'] ?? '';
            if (in_array($role, ['admin','owner','broker'])) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                $nc->sendEmail($_SESSION['user_id'],
                    'Dashboard Update – Advert Rejected',
                    "You have rejected advert ID {$id}.");
            }
        }
        return $res;
    }

    public function getPending() {
        return $this->advertModel->getPending();
    }

    public function all() {
        return $this->advertModel->all();
    }

    public function allActive() {
        return $this->advertModel->allActive();
    }

}

