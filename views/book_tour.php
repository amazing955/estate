<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/TourController.php';
require_once __DIR__ . '/../models/Tour.php';

if (!AuthController::check() || $_SESSION['role'] !== 'client') {
    header('Location: /estate/views/login.php');
    exit;
}

$tourCtrl = new TourController();
$message = '';
$property_id = $_GET['id'] ?? null;
if (!$property_id) {
    header('Location: /estate/views/client/dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['tour_date'];
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $msg = $_POST['message'] ?? '';
    if ($tourCtrl->requestTour($property_id, $date, $phone, $email, $msg)) {
        $message = 'Tour request submitted!';
    } else {
        $message = 'Failed to request tour.';
    }
}
?>
<?php include 'header.php'; ?>
<h2>Book Site Tour</h2>
<?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
<form method="post">
    <div class="form-group">
        <label>Preferred Date & Time</label>
        <input type="datetime-local" name="tour_date" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Phone Number</label>
        <input type="tel" name="phone" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Message (optional)</label>
        <textarea name="message" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Submit</button>
    <a href="client/dashboard.php" class="btn btn-secondary">Back</a>
</form>
<?php include 'footer.php'; ?>