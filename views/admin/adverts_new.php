<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/AdvertController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
   header('Location: /estate/views/login.php');
   exit;
}

$advCtrl = new AdvertController();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_id'])) {
        if ($advCtrl->approve($_POST['approve_id'])) {
            $message = 'Advert approved!';
        }
    } elseif (isset($_POST['reject_id'])) {
        if ($advCtrl->reject($_POST['reject_id'])) {
            $message = 'Advert rejected and deleted.';
        }
    } elseif (isset($_POST['delete_id'])) {
        if ($advCtrl->reject($_POST['delete_id'])) {
            $message = 'Advert deleted.';
        }
    }
}

$pending = $advCtrl->getPending();
$approved = array_filter($advCtrl->all(), fn($a) => $a['is_approved'] == 1);
?>
<?php include __DIR__ . '/../header.php'; ?>

<div class="container mt-4">
<h2>Manage Adverts</h2>
<?php if ($message): ?>
    <div class="alert alert-info"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<!-- PENDING ADVERTS FOR APPROVAL -->
<div class="card shadow mb-4">
<div class="card-header bg-warning text-dark">
<h5>Pending Adverts (<?=count($pending)?>) - Awaiting Approval</h5>
</div>
<div class="card-body">
<?php if (empty($pending)): ?>
    <p class="text-muted">No pending adverts.</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="table table-hover">
    <thead>
    <tr>
        <th>Title</th>
        <th>Submitted By</th>
        <th>Position</th>
        <th>Expires</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($pending as $p): ?>
    <tr>
        <td><?=htmlspecialchars($p['title'])?></td>
        <td><?=htmlspecialchars($p['username'] ?? 'Unknown')?></td>
        <td><?=htmlspecialchars($p['position'])?></td>
        <td><?=htmlspecialchars($p['expiry_date'] ?? 'No expiry')?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="approve_id" value="<?=$p['id']?>">
                <button class="btn btn-sm btn-success">Approve</button>
            </form>
            <form method="post" style="display:inline;">
                <input type="hidden" name="reject_id" value="<?=$p['id']?>">
                <button class="btn btn-sm btn-danger" onclick="return confirm('Reject this advert?');">Reject</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
<?php endif; ?>
</div>
</div>

<!-- APPROVED ADVERTS -->
<div class="card shadow">
<div class="card-header bg-success text-white">
<h5>Approved Adverts (<?=count($approved)?>)</h5>
</div>
<div class="card-body">
<?php if (empty($approved)): ?>
    <p class="text-muted">No approved adverts.</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="table table-hover">
    <thead>
    <tr>
        <th>Title</th>
        <th>Submitted By</th>
        <th>Position</th>
        <th>Expires</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($approved as $a): ?>
    <tr>
        <td><?=htmlspecialchars($a['title'])?></td>
        <td><?=htmlspecialchars($a['username'] ?? 'Admin')?></td>
        <td><?=htmlspecialchars($a['position'])?></td>
        <td><?=htmlspecialchars($a['expiry_date'] ?? 'No expiry')?></td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?=$a['id']?>">
                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this advert?');">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
<?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
