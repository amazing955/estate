<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AdvertController.php';

if (!AuthController::check() || !in_array($_SESSION['role'], ['owner', 'broker'])) {
    header('Location: /estate/views/login.php');
    exit;
}

$advCtrl = new AdvertController();
$message = '';
$type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($advCtrl->create($_POST, $_FILES['image'])) {
        $message = 'Advert submitted successfully. Your advert will appear after admin approval.';
        $type = 'success';
    } else {
        $message = 'Failed to submit advert. Please try again.';
        $type = 'danger';
    }
}
?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container mt-4">
<h2>Create Advert</h2>
<p class="text-muted">Submit an advert for approval by the admin.</p>

<?php if ($message): ?>
    <div class="alert alert-<?=$type?>"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<div class="row">
<div class="col-md-8">
<div class="card shadow">
<div class="card-body">
<form method="post" enctype="multipart/form-data">
    <div class="form-group mb-3">
        <label><strong>Title/Name:</strong></label>
        <input type="text" name="title" class="form-control" placeholder="e.g., Featured Property Promotion" required>
    </div>

    <div class="form-group mb-3">
        <label><strong>Position:</strong></label>
        <select name="position" class="form-control" required>
            <option value="">-- Choose --</option>
            <option value="homepage">Homepage Banner</option>
            <option value="sidebar">Sidebar Advert</option>
            <option value="featured">Featured Property Advert</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label><strong>Link (optional):</strong></label>
        <input type="url" name="link" class="form-control" placeholder="https://example.com">
    </div>

    <div class="form-group mb-3">
        <label><strong>Telephone Number:</strong></label>
        <input type="tel" name="telephone" class="form-control" placeholder="+1 (555) 000-0000" required>
        <small class="form-text text-muted">Contact number for inquiries about this advert</small>
    </div>

    <div class="form-group mb-3">
        <label><strong>Expiry Date (optional):</strong></label>
        <input type="date" name="expiry_date" class="form-control">
    </div>

    <div class="form-group mb-3">
        <label><strong>Image:</strong></label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
        <small class="form-text text-muted">JPG, PNG, or GIF (recommended size: 800x400px)</small>
    </div>

    <button type="submit" class="btn btn-primary">Submit for Approval</button>
    <a href="<?=($_SESSION['role'] === 'owner' ? '/estate/views/owner/dashboard.php' : '/estate/views/broker/dashboard.php')?>" class="btn btn-secondary">Back to Dashboard</a>
</form>
</div>
</div>
</div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
