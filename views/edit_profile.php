<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProfileController.php';

if (!AuthController::check()) {
    header('Location: /estate/views/login.php');
    exit;
}

$profile = new ProfileController();
$user = $profile->edit();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($profile->update($_POST, $_FILES['profile_pic'])) {
        header('Location: edit_profile.php?success=1');
        exit;
    } else {
        $errors[] = 'Failed to update profile';
    }
}
?>
<?php include 'header.php'; ?>
<h2>Edit Profile</h2>
<?php if (!empty($errors)): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo htmlspecialchars($e).'<br>';?></div><?php endif; ?>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Profile updated</div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="<?=htmlspecialchars($user['username'])?>">
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>">
    </div>
    <div class="form-group">
        <label>Profile Picture</label>
        <input type="file" name="profile_pic" class="form-control" accept="image/*">
        <?php if(!empty($user['profile_pic'])): ?>
            <img src="/estate/<?=htmlspecialchars($user['profile_pic'])?>" style="max-width:100px;margin-top:10px;" />
        <?php endif; ?>
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
</form>
<?php include 'footer.php'; ?>