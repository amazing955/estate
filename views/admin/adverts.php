<?php
//require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/AdvertController.php';

//if (!AuthController::isAdmin()) {
 //   header('Location: /estate/views/login.php');
   // exit;
//}

$advCtrl = new AdvertController();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // deleting an advert
        if ($advCtrl->delete($_POST['delete_id'])) {
            $message = 'Advert deleted';
        } else {
            $message = 'Failed to delete advert';
        }
    } else {
        if ($advCtrl->create($_POST, $_FILES['image'])) {
            $message = 'Advert created successfully';
        } else {
            $message = 'Failed to create advert';
        }
    }
}
$adverts = $advCtrl->allActive();
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Manage Adverts</h2>
<?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Title</label>
        <input name="title" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Link</label>
        <input name="link" class="form-control">
    </div>
    <div class="form-group">
        <label>Position</label>
        <select name="position" class="form-control" required>
            <option value="homepage">Homepage banner</option>
            <option value="sidebar">Sidebar advert</option>
            <option value="featured">Featured property advert</option>
        </select>
    </div>
    <div class="form-group">
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control">
    </div>
    <div class="form-group">
        <label>Image</label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
    </div>
    <button class="btn btn-primary" type="submit">Save Advert</button>
</form>
<h4>Active Adverts</h4>
<ul>
<?php foreach($adverts as $a): ?>
    <li class="d-flex justify-content-between align-items-center">
        <span><?=htmlspecialchars($a['title'])?> (<?=htmlspecialchars($a['position'])?>) expires <?=htmlspecialchars($a['expiry_date'])?></span>
        <form method="post" style="margin:0;">
            <input type="hidden" name="delete_id" value="<?=htmlspecialchars($a['id'])?>">
            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this advert?');">Delete</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>
<?php include __DIR__ . '/../footer.php'; ?>