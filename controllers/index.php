<?php
// controllers/index.php
// Simple router for controller actions
require_once 'AuthController.php';
require_once 'NotificationController.php';

$action = $_GET['action'] ?? null;
switch ($action) {
    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        header('Location: /estate/views/login.php');
        break;
    case 'markNotificationRead':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $nc = new NotificationController();
            $nc->markRead($id);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'deleteProperty':
        $id = $_GET['id'] ?? null;
        if ($id) {
            require_once 'PropertyController.php';
            $pc = new PropertyController();
            $pc->delete($id);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'saveProperty':
        $id = $_GET['id'] ?? null;
        if ($id && isset($_SESSION['user_id'])) {
            require_once 'PropertyController.php';
            $pc = new PropertyController();
            $pc->saveForClient($id, $_SESSION['user_id']);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'unsaveProperty':
        $id = $_GET['id'] ?? null;
        if ($id && isset($_SESSION['user_id'])) {
            require_once 'PropertyController.php';
            $pc = new PropertyController();
            $pc->unsaveForClient($id, $_SESSION['user_id']);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'deleteUser':
        $id = $_GET['id'] ?? null;
        if ($id) {
            require_once 'UserController.php';
            $uc = new UserController();
            $uc->delete($id);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'changeUserRole':
        $id = $_POST['id'] ?? null;
        $role = $_POST['role'] ?? null;
        if ($id && $role) {
            require_once 'UserController.php';
            $uc = new UserController();
            $uc->updateRole($id, $role);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'approveBroker':
        $id = $_GET['id'] ?? null;
        if ($id) {
            require_once 'UserController.php';
            $uc = new UserController();
            $uc->setBrokerApproved($id, true);
            // optional notification to broker
            require_once 'NotificationController.php';
            $nc = new NotificationController();
            $nc->create($id, 'Your broker account has been approved by admin.');
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'deleteInquiry':
        $id = $_GET['id'] ?? null;
        if ($id) {
            require_once 'InquiryController.php';
            $ic = new InquiryController();
            $ic->delete($id);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'requestCollab':
        $owner = $_GET['owner_id'] ?? null;
        if ($owner && isset($_SESSION['user_id'])) {
            require_once 'CollaborationController.php';
            $cc = new CollaborationController();
            $cc->request($owner);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'respondCollab':
        $id = $_GET['id'] ?? null;
        $decision = $_GET['decision'] ?? null;
        if ($id && $decision) {
            require_once 'CollaborationController.php';
            $cc = new CollaborationController();
            $cc->respond($id, $decision);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'repostProperty':
        $propId = $_GET['prop_id'] ?? null;
        if ($propId && isset($_SESSION['user_id'])) {
            require_once 'PropertyController.php';
            $pc = new PropertyController();
            $pc->repost($propId, $_SESSION['user_id']);
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'submitToOwner':
        $propId = $_GET['property_id'] ?? null;
        $type = $_GET['type'] ?? null; // 'inquiry' or 'tour'
        $inqId = $_GET['inq_id'] ?? null;
        $tourId = $_GET['tour_id'] ?? null;
        $tourDate = $_GET['tour_date'] ?? null;
        $clientName = $_GET['client'] ?? '';
        $msg = '';
        if ($propId && $type) {
            require_once 'PropertyController.php';
            $pc = new PropertyController();
            $prop = $pc->view($propId)['property'];
            $ownerId = $prop['original_owner_id'] ?? null;
            if ($ownerId) {
                if ($type === 'inquiry' && $inqId) {
                    // get inquiry details
                    require_once __DIR__ . '/InquiryController.php';
                    $ic = new InquiryController();
                    $inq = $ic->findById($inqId);
                    $msg = "Broker forwarded inquiry from " . htmlspecialchars($inq['client_name']) . " for " . htmlspecialchars($prop['title']) . ": " . htmlspecialchars($inq['message']);
                } elseif ($type === 'tour') {
                    require_once __DIR__ . '/TourController.php';
                    $tc = new TourController();
                    if ($tourId) {
                        $tour = $tc->findById($tourId);
                        $msg = "Broker forwarded tour request by " . htmlspecialchars($tour['username']) . " on " . htmlspecialchars($tour['tour_date']) . " for " . htmlspecialchars($prop['title']);
                    } elseif ($tourDate) {
                        $msg = "Broker forwarded tour request by " . htmlspecialchars($clientName) . " on " . htmlspecialchars($tourDate) . " for " . htmlspecialchars($prop['title']);
                    }
                }
                if ($msg) {
                    require_once 'NotificationController.php';
                    $nc = new NotificationController();
                    $nc->create($ownerId, $msg, $propId);
                }
            }
        }
        AuthController::updateActivity(); // Update session activity
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        break;
    case 'getMoreProperties':
        header('Content-Type: application/json');
        $offset = (int)($_GET['offset'] ?? 0);
        $getAll = isset($_GET['getAll']) && $_GET['getAll'] === 'true';
        require_once 'Property.php';
        $propModel = new Property();
        $allProperties = $propModel->all();
        
        if ($getAll) {
            // Load all remaining properties
            $moreProperties = array_slice($allProperties, $offset);
        } else {
            // Load batch of 5
            $limit = 5;
            $moreProperties = array_slice($allProperties, $offset, $limit);
        }
        
        $hasMore = count($allProperties) > ($offset + count($moreProperties));
        echo json_encode([
            'properties' => $moreProperties,
            'hasMore' => $hasMore
        ]);
        exit;
    case 'updateActivity':
        require_once 'AuthController.php';
        AuthController::updateActivity();
        exit;
    default:
        echo 'Invalid action';
}
