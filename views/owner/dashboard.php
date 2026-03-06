<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/PropertyController.php';
require_once __DIR__ . '/../../controllers/InquiryController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'owner') {
    header('Location: /estate/views/login.php');
    exit;
}

$propCtrl = new PropertyController();
$inqCtrl = new InquiryController();
$notifCtrl = new NotificationController();

$properties = $propCtrl->listByOwner($_SESSION['user_id']);
$notifications = $notifCtrl->getUserNotifications($_SESSION['user_id']);

// collaboration requests
require_once __DIR__ . '/../../controllers/CollaborationController.php';
$cc = new CollaborationController();
$pendingCollabs = $cc->pendingForOwner($_SESSION['user_id']);

$totalProperties = count($properties);
$totalNotifications = count($notifications);
?>
<?php include __DIR__ . '/../header.php'; ?>

<div class="container-fluid dashboard-wrapper">

<div class="row mb-4">
<div class="col">
<h2 class="dashboard-title">Owner Dashboard</h2>
<p class="text-muted">Manage your property listings and view client interests.</p>
</div>

<div class="col text-end">
<a href="/estate/views/create_property.php" class="btn btn-primary btn-lg shadow">
➕ Add Property
</a>
</div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">

<div class="col-md-4">
<div class="card stat-card shadow">
<div class="card-body">
<h5>Total Listings</h5>
<h2><?=$totalProperties?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card shadow">
<div class="card-body">
<h5>Notifications</h5>
<h2><?=$totalNotifications?></h2>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card shadow">
<div class="card-body">
<h5>Account</h5>
<h2><?=htmlspecialchars($_SESSION['username'])?></h2>
</div>
</div>
</div>

</div>

<!-- Property Listings -->
<div class="card shadow mb-4">
<div class="card-header bg-dark text-white">
<h5>Your Property Listings</h5>
</div>

<div class="card-body">

<?php if(empty($properties)): ?>

<p>No properties yet.</p>

<?php else: ?>

<div class="row">

<?php foreach ($properties as $p): ?>

<div class="col-md-4 mb-4">

<div class="card property-card shadow-sm">

<?php
    // fetch first image for property if available
    require_once __DIR__ . '/../../models/PropertyImage.php';
    $imgModel = new PropertyImage();
    $imgs = $imgModel->findByProperty($p['id']);
    $thumb = '/estate/assets/images/default-property.png';
    if (!empty($imgs)) {
        $thumb = '/estate/' . $imgs[0]['image_path'];
    }
    echo '<img src="' . $thumb . '" class="card-img-top property-img">';
?>

<div class="card-body">

<h5 class="card-title"><?=htmlspecialchars($p['title'])?></h5>

<p class="text-muted">
<?=$p['location']?> | <?=$p['price']?> UGX
</p>

<div class="d-flex justify-content-between">

<a href="/estate/views/property_detail.php?id=<?=$p['id']?>" class="btn btn-outline-primary btn-sm">
View
</a>

<a href="/estate/views/edit_property.php?id=<?=$p['id']?>" class="btn btn-outline-warning btn-sm">
Edit
</a>

<a href="/estate/controllers/index.php?action=deleteProperty&id=<?=$p['id']?>"
class="btn btn-outline-danger btn-sm"
onclick="return confirm('Delete this property?');">
Delete
</a>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</div>


<?php if(!empty($pendingCollabs)): ?>
<!-- Collaboration Requests -->
<div class="card shadow mb-4">
    <div class="card-header bg-warning text-dark">
        <h5>Collaboration Requests</h5>
    </div>
    <div class="card-body">
        <ul class="list-group">
        <?php foreach($pendingCollabs as $req): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Broker <?=htmlspecialchars($req['broker_name'])?> wants to collaborate.
                <div>
                    <a href="/estate/controllers/index.php?action=respondCollab&id=<?=$req['id']?>&decision=accept" class="btn btn-sm btn-success">Accept</a>
                    <a href="/estate/controllers/index.php?action=respondCollab&id=<?=$req['id']?>&decision=reject" class="btn btn-sm btn-danger">Reject</a>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Notifications -->
<div class="card shadow">

<div class="card-header bg-primary text-white">
<h5>Notifications</h5>
</div>

<div class="card-body">

<?php if(empty($notifications)): ?>

<p>No notifications yet.</p>

<?php else: ?>

<ul class="list-group">

<?php foreach ($notifications as $n): ?>

<li class="list-group-item d-flex justify-content-between align-items-center">

<?= $n['message'] /* message may contain action links */ ?>

<div>
    <?php if(!$n['is_read']): ?>
        <a href="/estate/controllers/index.php?action=markNotificationRead&id=<?=$n['id']?>" class="btn btn-sm btn-success">Mark Read</a>
    <?php endif; ?>
    <?php if(!empty($n['property_id'])): ?>
        <a href="/estate/views/owner/track_sale.php?prop_id=<?=$n['property_id']?>" class="btn btn-sm btn-info">Track Sale</a>
    <?php endif; ?>
</div>

</li>

<?php endforeach; ?>

</ul>

<?php endif; ?>

</div>

</div>

</div>

<script>
// refresh owner dashboard every 5 seconds
setInterval(() => { window.location.reload(); }, 5000);
</script>

<?php include __DIR__ . '/../footer.php'; ?>