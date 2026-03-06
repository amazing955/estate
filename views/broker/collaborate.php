<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/CollaborationController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'broker') {
    header('Location: /estate/views/login.php');
    exit;
}

// ensure broker has been approved by admin
require_once __DIR__ . '/../../controllers/UserController.php';
$um = new UserController();
if (!$um->isBrokerApproved($_SESSION['user_id'])) {
    include __DIR__ . '/../header.php';
    echo '<div class="container mt-4"><div class="alert alert-warning">Your broker account is pending admin approval before you can collaborate with owners.</div></div>';
    include __DIR__ . '/../footer.php';
    exit;
}

$uc = new UserController();
$cc = new CollaborationController();

$owners = array_filter($uc->all(), fn($u)=>$u['role']==='owner');
$accepted = $cc->acceptedForBroker($_SESSION['user_id']);
$acceptedOwners = array_column($accepted, 'owner_id');

// pending requests initiated by this broker
$pendingRequests = $cc->pendingForBroker($_SESSION['user_id']);
$pendingOwners = array_column($pendingRequests, 'owner_id');
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Collaborate with Owners</h2>
<table class="table">
    <thead>
        <tr><th>Owner</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach($owners as $o): ?>
        <tr>
            <td><?=htmlspecialchars($o['username'])?></td>
            <td>
                <?php if(in_array($o['id'],$acceptedOwners)): ?>
                    <span class="badge bg-success">Accepted</span>
                <?php elseif(in_array($o['id'],$pendingOwners)): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php else: ?>
                    <a href="/estate/controllers/index.php?action=requestCollab&owner_id=<?=$o['id']?>" class="btn btn-sm btn-primary">Collaborate</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../footer.php'; ?>
