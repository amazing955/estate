<?php
require_once __DIR__ . '/../../controllers/AuthController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? null;
    $msg = $_POST['message'] ?? '';
    
    if ($category && trim($msg)) {
        require_once __DIR__ . '/../../controllers/NotificationController.php';
        $nc = new NotificationController();
        $sent = $nc->sendBroadcast($category, $msg);
        $message = $sent ? "Message sent to {$category}." : "Failed to send message.";
    } else {
        $message = 'Please select a category and enter a message.';
    }
}
?>
<?php include __DIR__ . '/../header.php'; ?>

<div class="container-fluid">
<h2>Send Message to Users</h2>
<p class="text-muted">Send a broadcast message to a specific category of users.</p>

<?php if ($message): ?>
    <div class="alert alert-info"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<div class="row">
<div class="col-md-8">
<div class="card shadow">
<div class="card-body">
<form method="post">
    <div class="form-group mb-3">
        <label><strong>Select Category:</strong></label>
        <select name="category" class="form-control" required>
            <option value="">-- Choose --</option>
            <option value="client">Clients</option>
            <option value="owner">Owners</option>
            <option value="broker">Brokers</option>
            <option value="all">All Users</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label><strong>Message:</strong></label>
        <textarea name="message" class="form-control" rows="6" placeholder="Enter your message..." required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Send Message</button>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</form>
</div>
</div>
</div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
