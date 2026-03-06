<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PropertyController.php';

if (!AuthController::check() || !in_array($_SESSION['role'], ['owner','broker'])) {
    header('Location: /estate/views/login.php');
    exit;
}

$propCtrl = new PropertyController();
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /estate/views/home.php');
    exit;
}
$info = $propCtrl->view($id);
$property = $info['property'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'price' => $_POST['price'],
        'type' => $_POST['type'],
        'location' => $_POST['location'],
        'status' => $_POST['status'],
    ];
    $propCtrl->update($id, $data, $_FILES);
    header('Location: ' . ($_SESSION['role']==='owner' ? '/estate/views/owner/dashboard.php' : '/estate/views/broker/dashboard.php'));
    exit;
}
?>
<?php include 'header.php'; ?>
<h2>Edit Property</h2>
<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($property['title'])?>" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required><?=htmlspecialchars($property['description'])?></textarea>
    </div>
    <div class="form-group">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?=htmlspecialchars($property['price'])?>" required>
    </div>
    <div class="form-group">
        <label>Type</label>
        <select name="type" class="form-control" required>
            <option value="House" <?= $property['type']=='House'?'selected':''?>>House</option>
            <option value="Apartment" <?= $property['type']=='Apartment'?'selected':''?>>Apartment</option>
            <option value="Land" <?= $property['type']=='Land'?'selected':''?>>Land</option>
            <option value="Commercial" <?= $property['type']=='Commercial'?'selected':''?>>Commercial</option>
        </select>
    </div>
    <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" class="form-control" value="<?=htmlspecialchars($property['location'])?>" required>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Available" <?= $property['status']=='Available'?'selected':''?>>Available</option>
            <option value="Reserved" <?= $property['status']=='Reserved'?'selected':''?>>Reserved</option>
            <option value="Sold" <?= $property['status']=='Sold'?'selected':''?>>Sold</option>
        </select>
    </div>
    <div class="form-group">
        <label>Additional Images (optional)</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>
    <div class="form-group">
        <label>Additional Video (optional)</label>
        <input type="file" name="video" class="form-control" accept="video/*">
    </div>
    <button class="btn btn-primary" type="submit">Update</button>
</form>
<?php include 'footer.php'; ?>