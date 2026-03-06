<?php
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register($_POST);
    if ($result['status']) {
        header('Location: login.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>
<?php include 'header.php'; ?>
<h2>Register</h2>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
        <?php foreach ($errors as $err): ?>
            <li><?=htmlspecialchars($err)?></li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form method="post">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control" required>
            <option value="client">Client</option>
            <option value="owner">Estate Owner</option>
            <option value="broker">Broker / Agent</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
<?php include 'footer.php'; ?>