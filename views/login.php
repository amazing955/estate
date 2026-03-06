<?php
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if ($auth->login($email, $password)) {
        header('Location: /estate/controllers/dashboard.php');
        exit;
    } else {
        $message = 'Invalid credentials';
    }
}
?>
<?php include 'header.php'; ?>
<h2>Login</h2>
<?php if ($message): ?><div class="alert alert-danger"><?=htmlspecialchars($message)?></div><?php endif; ?>
<form method="post">
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
<?php include 'footer.php'; ?>