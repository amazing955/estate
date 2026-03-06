<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/InquiryController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: inquiries.php');
    exit;
}
$ic = new InquiryController();
$inq = $ic->findById($id);
if (!$inq) {
    header('Location: inquiries.php');
    exit;
}
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Inquiry #<?=$inq['id']?></h2>
<p><strong>Client:</strong> <?=htmlspecialchars($inq['client_name'])?></p>
<p><strong>Property:</strong> <?=htmlspecialchars($inq['property_title'])?> (<?=htmlspecialchars($inq['location'])?> - <?=htmlspecialchars($inq['price'])?> UGX)</p>
<p><strong>Message:</strong></p>
<p><?=nl2br(htmlspecialchars($inq['message']))?></p>
<p><strong>Created At:</strong> <?=htmlspecialchars($inq['created_at'])?></p>

<a href="inquiries.php" class="btn btn-secondary">Back</a>
<?php include __DIR__ . '/../footer.php'; ?>