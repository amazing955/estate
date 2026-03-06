<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/InquiryController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}



$ic = new InquiryController();
$inquiries = $ic->all();
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>All Inquiries</h2>
<?php if(empty($inquiries)): ?>
    <p>No inquiries have been made.</p>
<?php else: ?>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Client</th><th>Property</th><th>Message</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($inquiries as $inq): ?>
            <tr>
                <td><?=$inq['id']?></td>
                <td><?=htmlspecialchars($inq['client_name'])?></td>
                <td><?=htmlspecialchars($inq['property_title'])?></td>
                <td><?=htmlspecialchars($inq['message'])?></td>
                <td>
                    <a href="user_activity.php?id=<?=urlencode($inq['client_id'])?>" class="btn btn-sm btn-info">Client</a>
                    <a href="inquiry_detail.php?id=<?=$inq['id']?>" class="btn btn-sm btn-primary">View</a>
                    <a href="/estate/controllers/index.php?action=deleteInquiry&id=<?=$inq['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove inquiry?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../footer.php'; ?>