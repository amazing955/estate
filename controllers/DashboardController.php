<?php
// controllers/DashboardController.php
session_start();
require_once __DIR__ . '/../controllers/AuthController.php';

class DashboardController {
    public function index() {
        if (!AuthController::check()) {
            header('Location: /estate/views/login.php');
            exit;
        }
        $role = $_SESSION['role'];
        switch ($role) {
            case 'admin':
                header('Location: /estate/views/admin/dashboard.php');
                break;
            case 'owner':
                header('Location: /estate/views/owner/dashboard.php');
                break;
            case 'broker':
                header('Location: /estate/views/broker/dashboard.php');
                break;
            case 'client':
                header('Location: /estate/views/client/dashboard.php');
                break;
            default:
                echo 'Unauthorized';
        }
    }
}
