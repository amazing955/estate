<?php
// controllers/AuthController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserLog.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register($data) {
        // validate input
        $errors = [];
        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }
        if (!in_array($data['role'], ['owner','broker','client'])) {
            $errors[] = 'Invalid role';
        }

        if (!empty($this->userModel->findByEmail($data['email']))) {
            $errors[] = 'Email already exists';
        }

        if (!empty($errors)) {
            return ['status' => false, 'errors' => $errors];
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $success = $this->userModel->create($data['username'], $data['email'], $hash, $data['role']);
        if ($success) {
            // log the registration
            $newId = $this->userModel->getLastInsertId();
            $log = new UserLog();
            $log->add($newId, 'register');
            return ['status' => true];
        }
        return ['status' => false, 'errors' => ['Registration failed']];
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            // set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time(); // Track session start time
            if (!empty($user['profile_pic'])) {
                $_SESSION['profile_pic'] = $user['profile_pic'];
            }
            // record login
            $log = new UserLog();
            $log->add($user['id'], 'login');
            return true;
        }
        return false;
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $log = new UserLog();
            $log->add($_SESSION['user_id'], 'logout');
        }
        session_unset();
        session_destroy();
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (self::check()) {
            return ['id' => $_SESSION['user_id'], 'role' => $_SESSION['role'], 'username' => $_SESSION['username']];
        }
        return null;
    }

    public static function isAdmin() {
        return self::check() && $_SESSION['role'] === 'admin';
    }

    /**
     * Check if session has timed out (3 minutes = 180 seconds)
     */
    public static function checkSessionTimeout() {
        if (!self::check()) {
            return false;
        }

        $timeout = 180; // 3 minutes in seconds
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            // Session has expired
            self::forceLogout();
            return false;
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Force logout and redirect to timeout page
     */
    public static function forceLogout() {
        if (isset($_SESSION['user_id'])) {
            $log = new UserLog();
            $log->add($_SESSION['user_id'], 'timeout_logout');
        }
        session_unset();
        session_destroy();
        header('Location: /estate/views/timeout.php');
        exit;
    }

    /**
     * Update session activity (call this on user interactions)
     */
    public static function updateActivity() {
        if (self::check()) {
            $_SESSION['last_activity'] = time();
        }
    }
}
