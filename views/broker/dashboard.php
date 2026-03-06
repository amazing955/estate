<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/PropertyController.php';
require_once __DIR__ . '/../../controllers/InquiryController.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'broker') {
    header('Location: /estate/views/login.php');
    exit;
}

$propCtrl = new PropertyController();
$inqCtrl = new InquiryController();
$notifCtrl = new NotificationController();
require_once __DIR__ . '/../../controllers/CollaborationController.php';
$cc = new CollaborationController();

$properties = $propCtrl->listByOwner($_SESSION['user_id']); // treat broker as owner for simplicity
$notifications = $notifCtrl->getUserNotifications($_SESSION['user_id']);
$totalProperties = count($properties);
$totalNotifications = count($notifications);
?>
<?php include __DIR__ . '/../header.php'; ?>
<h2>Broker Dashboard</h2>
<p class="text-muted">Manage your property listings and stay on top of client interactions.</p>
<p>
    <?php
    require_once __DIR__ . '/../../controllers/UserController.php';
    $um = new UserController();
    if ($um->isBrokerApproved($_SESSION['user_id'])): ?>
        <a href="/estate/views/broker/collaborate.php" class="btn btn-outline-primary btn-sm me-2">Collaborate with Owners</a>
    <?php else: ?>
        <span class="text-warning me-2">Waiting admin approval to collaborate</span>
    <?php endif; ?>
    <a href="/estate/views/create_property.php" class="btn btn-primary btn-lg mb-3"><strong>➕ Add Listing</strong></a>
</p>

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

<h4>Your Property Listings</h4>
<div class="row">
    <?php if(empty($properties)): ?>
        <p>No properties yet.</p>
    <?php else: ?>
        <?php foreach ($properties as $p): ?>
            <div class="col-md-4 mb-4">
                <div class="card property-card shadow-sm">
                    <?php
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
                        <p class="text-muted"><?=$p['location']?> | <?=$p['price']?> UGX</p>
                        <div class="d-flex justify-content-between">
                            <a href="/estate/views/property_detail.php?id=<?=$p['id']?>" class="btn btn-outline-primary btn-sm">View</a>
                            <a href="/estate/views/edit_property.php?id=<?=$p['id']?>" class="btn btn-outline-warning btn-sm">Edit</a>
                            <a href="/estate/controllers/index.php?action=deleteProperty&id=<?=$p['id']?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this property?');">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<h4>Collaborations</h4>
<ul class="list-group mb-4">
    <?php $accepted = $cc->acceptedForBroker($_SESSION['user_id']); ?>
    <?php if(empty($accepted)): ?>
        <li class="list-group-item">No active collaborations.</li>
    <?php else: ?>
        <?php foreach($accepted as $ac): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Owner <?=htmlspecialchars($ac['owner_name'])?>
                <a href="/estate/views/broker/repost.php?owner_id=<?=$ac['owner_id']?>" class="btn btn-sm btn-primary">View Properties</a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<h4>Notifications</h4>
<ul class="list-group">
    <?php foreach ($notifications as $n): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= $n['message'] /* allow embedded link */ ?>
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

<?php include __DIR__ . '/../footer.php'; ?>