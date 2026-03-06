<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Rating.php';

if (!AuthController::check() || $_SESSION['role'] !== 'client') {
    header('Location: /estate/views/login.php');
    exit;
}

$ratingModel = new Rating();
$message = '';
$property_id = $_GET['id'] ?? null;
if (!$property_id) {
    header('Location: /estate/views/client/dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rate = intval($_POST['rating']);
    $comment = $_POST['comment'] ?? '';
    if ($ratingModel->add($property_id, $_SESSION['user_id'], $rate, $comment)) {
        $message = 'Thank you for your rating!';
    } else {
        $message = 'Failed to submit rating.';
    }
}
?>
<?php include 'header.php'; ?>
<h2>Rate Property</h2>
<?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
<form method="post">
    <div class="form-group">
        <label>Rating (1-5)</label>
        <select name="rating" class="form-control" required>
            <?php for ($i=1; $i<=5; $i++): ?>
            <option value="<?=$i?>"><?=$i?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Comment (optional)</label>
        <textarea name="comment" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
    <a href="client/dashboard.php" class="btn btn-secondary">Back</a>
</form>
<?php include 'footer.php'; ?>