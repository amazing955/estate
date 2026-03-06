<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: users.php');
    exit;
}

$uc = new UserController();
$user = $uc->findById($id);
$act = $uc->getActivity($id);
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Activity for <?=htmlspecialchars($user['username'])?></h2>

<h4>Properties Listed (<?=count($act['properties'])?>)</h4>
<?php if(empty($act['properties'])): ?>
    <p>No properties created by this user.</p>
<?php else: ?>
    <ul>
    <?php foreach($act['properties'] as $p): ?>
        <li><?=htmlspecialchars($p['title'])?> (<?=htmlspecialchars($p['status'])?>)</li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h4>Inquiries Made (<?=count($act['inquiries'])?>)</h4>
<?php if(empty($act['inquiries'])): ?>
    <p>No inquiries.</p>
<?php else: ?>
    <ul>
    <?php foreach($act['inquiries'] as $i): ?>
        <li>On "<?=htmlspecialchars($i['property_title'])?>" - "<?=htmlspecialchars($i['message'])?>"</li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h4>Saved Properties (<?=count($act['saved'])?>)</h4>
<?php if(empty($act['saved'])): ?>
    <p>None saved.</p>
<?php else: ?>
    <ul>
    <?php foreach($act['saved'] as $s): ?>
        <li><?=htmlspecialchars($s['title'])?> (<?=htmlspecialchars($s['location'])?>)</li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h4>Login/Logout History (<?=count($act['logs'])?>)</h4>
<?php if(empty($act['logs'])): ?>
    <p>No log records.</p>
<?php else: ?>
    <ul>
    <?php foreach($act['logs'] as $l): ?>
        <li><?=htmlspecialchars($l['action'])?> at <?=htmlspecialchars($l['created_at'])?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<a href="users.php" class="btn btn-secondary">Back to Users</a>

<?php include __DIR__ . '/../footer.php'; ?>