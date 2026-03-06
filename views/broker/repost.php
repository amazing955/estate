<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/PropertyController.php';
require_once __DIR__ . '/../../controllers/CollaborationController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'broker') {
    header('Location: /estate/views/login.php');
    exit;
}

$owner_id = $_GET['owner_id'] ?? null;
if (!$owner_id) {
    header('Location: dashboard.php');
    exit;
}

// ensure this owner is accepted collaborator for this broker
$cc = new CollaborationController();
$accepted = array_column($cc->acceptedForBroker($_SESSION['user_id']), 'owner_id');
if (!in_array($owner_id, $accepted)) {
    header('Location: dashboard.php');
    exit;
}

$propCtrl = new PropertyController();
$ownerProps = $propCtrl->listByOwner($owner_id);

?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Properties of Owner #<?=htmlspecialchars($owner_id)?></h2>
<?php if(empty($ownerProps)): ?>
<p>This owner has no properties.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Title</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach($ownerProps as $p): ?>
            <tr>
                <td><?=htmlspecialchars($p['title'])?></td>
                <td><?=htmlspecialchars($p['status'])?></td>
                <td>
                    <a href="/estate/controllers/index.php?action=repostProperty&prop_id=<?=$p['id']?>" class="btn btn-sm btn-secondary">Repost</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../footer.php'; ?>
