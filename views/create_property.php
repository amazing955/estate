<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PropertyController.php';

if (!AuthController::check() || !in_array($_SESSION['role'], ['owner','broker'])) {
    header('Location: /estate/views/login.php');
    exit;
}

$propCtrl = new PropertyController();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $files = $_FILES;
    $result = $propCtrl->create($data, $files);
    if ($result['status']) {
        header('Location: ' . ($_SESSION['role']==='owner' ? '/estate/views/owner/dashboard.php' : '/estate/views/broker/dashboard.php'));
        exit;
    } else {
        $errors = array_merge($errors, $result['errors']);
        if (empty($result['errors'])) {
            $errors[] = 'Failed to create property';
        }
    }
}
?>
<?php include 'header.php'; ?>
<h2>Add Property</h2>
<?php if ($errors): ?><div class="alert alert-danger"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>';?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Type</label>
        <select name="type" class="form-control" required>
            <option value="House">House</option>
            <option value="Apartment">Apartment</option>
            <option value="Land">Land</option>
            <option value="Commercial">Commercial</option>
        </select>
    </div>
    <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Available">Available</option>
            <option value="Reserved">Reserved</option>
            <option value="Sold">Sold</option>
        </select>
    </div>
    <div class="form-group">
        <label>Images (multiple)</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>
    <div class="form-group">
        <label>Video (optional)</label>
        <input type="file" name="video" class="form-control" accept="video/*">
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php include 'footer.php'; ?>