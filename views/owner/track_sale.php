<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Property.php';
require_once __DIR__ . '/../../models/Sale.php';

if (!AuthController::check() || !in_array($_SESSION['role'], ['owner','broker'])) {
    header('Location: /estate/views/login.php');
    exit;
} // both owner and broker can track sales for their listings
$propModel = new Property();
$saleModel = new Sale();
$prop_id = $_GET['prop_id'] ?? null;
if (!$prop_id) {
    header('Location: dashboard.php');
    exit;
}
$property = $propModel->findById($prop_id);
if (!$property) {
    // invalid id or record removed
    header('Location: dashboard.php');
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    // log each intent
    require_once __DIR__ . '/../../controllers/UserController.php';
    $uc = new UserController();
    require_once __DIR__ . '/../../controllers/PropertyController.php';
    $pc = new PropertyController();

    if ($action === 'reserve') {
        $pc->changeStatus($prop_id, 'Reserved');
        $message = 'Property marked as reserved.';
        $uc->addSystemLog('property', "User {$_SESSION['user_id']} reserved property {$prop_id}");
    } elseif ($action === 'sold') {
        $price = $_POST['sold_price'] ?? $property['price'];
        $pc->changeStatus($prop_id, 'Sold');
        // record sale (buyer unknown here - use NULL)
        $saleModel->record($prop_id, null, $property['owner_id'], $price);
        $message = 'Property marked as sold. Any broker reposts have been removed.';
        $uc->addSystemLog('property', "User {$_SESSION['user_id']} sold property {$prop_id} for {$price}");
    } elseif ($action === 'unreserve') {
        $pc->changeStatus($prop_id, 'Available');
        $message = 'Property is now available again.';
        $uc->addSystemLog('property', "User {$_SESSION['user_id']} unreserved property {$prop_id}");
    }
}
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Track Sale for "<?=htmlspecialchars($property['title'])?>"</h2>
<p><strong>Current status:</strong> <?=htmlspecialchars($property['status'])?></p>
<?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
<form method="post" class="mb-3">
    <?php if($property['status'] !== 'Reserved'): ?>
        <button type="submit" name="action" value="reserve" class="btn btn-warning">Mark Reserved</button>
    <?php else: ?>
        <button type="submit" name="action" value="unreserve" class="btn btn-secondary">Unreserve</button>
    <?php endif; ?>
    <button type="submit" name="action" value="sold" class="btn btn-success">Mark Sold</button>
    <div class="form-group mt-2" id="sold-details" style="display:none;">
        <label>Sold Price</label>
        <input type="number" name="sold_price" class="form-control" value="<?=htmlspecialchars($property['price'])?>">
    </div>
</form>
<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
<script>
document.querySelector('button[value="sold"]').addEventListener('click', function(){
    document.getElementById('sold-details').style.display='block';
});
</script>
<?php include __DIR__ . '/../footer.php'; ?>