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

        // handle file upload
        if ($file && $file['tmp_name']) {
            $allowed = ['jpg','jpeg','png','gif'];
            $folder = __DIR__ . '/../uploads/images/';
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) return false;
            $filename = uniqid() . ".{$ext}";
            if (move_uploaded_file($file['tmp_name'], $folder . $filename)) {
                $path = 'uploads/images/' . $filename;
                $res = $this->advertModel->create($title, $path, $link, $position, $expiry);
                if ($res) {
                    require_once __DIR__ . '/UserController.php';
                    $uc = new UserController();
                    $uc->addSystemLog('advert', "User {$_SESSION['user_id']} created advert {$title}");
                }
                return $res;
            }
        }
        return false;
    }

    public function allActive() {
        return $this->advertModel->allActive();
    }

    public function delete($id) {
        $res = $this->advertModel->delete($id);
        if ($res) {
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('advert', "User {$_SESSION['user_id']} deleted advert {$id}");
        }
        return $res;
    }
}
