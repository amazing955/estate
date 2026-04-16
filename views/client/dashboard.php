<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Inquiry.php';
require_once __DIR__ . '/../../controllers/PropertyController.php';
require_once __DIR__ . '/../../models/SavedProperty.php';
require_once __DIR__ . '/../../models/PropertyImage.php';
require_once __DIR__ . '/../../controllers/AdvertController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'client') {
    header('Location: /estate/views/login.php');
    exit;
}

// Check for session timeout
if (!AuthController::checkSessionTimeout()) {
    // This will redirect to timeout page if session expired
}

$propCtrl = new PropertyController();
$allProperties = $propCtrl->listAll();

$inq = new Inquiry();
$inquiries = $inq->findByClient($_SESSION['user_id']);

$savedModel = new SavedProperty();
$saved = $savedModel->findByClient($_SESSION['user_id']);

$advCtrl = new AdvertController();
$activeAds = $advCtrl->allActive();
// collect all homepage adverts for popup rotation
$popupAds = [];
foreach ($activeAds as $ad) {
    if ($ad['position'] === 'homepage') {
        $popupAds[] = $ad;
    }
}

$totalListings = count($allProperties);
$totalSaved = count($saved);
$totalInquiries = count($inquiries);

// handle rating submission
$ratingMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_property_id'])) {
    $prop_id = (int)$_POST['rating_property_id'];
    $rating = (int)$_POST['rating_stars'];
    if ($rating >= 1 && $rating <= 5) {
        if ($propCtrl->submitRating($prop_id, $rating)) {
            $ratingMessage = 'Rating submitted!';
            // refresh to see updated rating
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Client Dashboard</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
body{
background:#f4f6f9;
}

.property-card{
border:none;
border-radius:12px;
overflow:hidden;
transition:0.3s;
}

.property-card:hover{
transform:translateY(-5px);
box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

.property-img{
height:220px;
object-fit:cover;
}

.sidebar{
background:white;
padding:20px;
border-radius:12px;
box-shadow:0 3px 10px rgba(0,0,0,0.05);
}
/* assets/css/style.css */
.hero {
    background: linear-gradient(rgba(0, 0, 0, 0.6),rgba(0, 0, 0, 0.6)),
                url('/estate/assets/images/hero.jpg') center/cover no-repeat;
    background-size: cover;
    padding: 80px 0;
    color: white;
    text-align: center;
}
.stat-card{
border:none;
border-radius:12px;
background:linear-gradient(135deg,#0062E6,#33AEFF);
color:white;
text-align:center;
}

</style>
</head>

<body>

<!-- HERO SEARCH -->
<section class="hero">
<div class="container">
<h1>Find Your Dream Property</h1>

<form method="GET" action="/estate/views/search.php" class="row g-2 justify-content-center mt-4">

<div class="col-md-3">
<input type="text" name="location" class="form-control" placeholder="Location">
</div>

<div class="col-md-3">
<select name="type" class="form-control">
<option value="">Property Type</option>
<option>House</option>
<option>Apartment</option>
<option>Land</option>
<option>Commercial</option>
</select>
</div>

<div class="col-md-2">
<input type="number" name="price" class="form-control" placeholder="Max Price">
</div>

<div class="col-md-2">
<button class="btn btn-warning w-100">Search</button>

</div>

<div class="col-md-2">
    <a href="/estate/views/login.php" class="btn btn-warning w-100">Logout</a>
</div>
</form>
</div>
</section>

<div class="container mt-5">

<!-- STATS -->
<div class="row mb-4">

<div class="col-md-4">

</div>

<div class="col-md-4">
<!--<div class="card stat-card p-3">
<h5>Saved Properties</h5>
<h2>
    <?=$totalSaved?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card p-3">
<h5>Your Inquiries</h5>
<h2><?=$totalInquiries?></h2>
</div>-->
</div>

</div>

<div class="row">

<!-- PROPERTY LISTINGS -->
<div class="col-lg-8">

<h4 class="mb-3">Latest Properties</h4>

<div class="row">

<?php foreach ($allProperties as $p): ?>

<div class="col-md-6 mb-4">

<div class="card property-card">

<?php
$imgs = (new PropertyImage())->findByProperty($p['id']);
$thumb = '/estate/assets/images/default-property.png';
if (!empty($imgs)) {
$thumb = '/estate/' . $imgs[0]['image_path'];
}
?>

<img src="<?=$thumb?>" class="card-img-top property-img">

<div class="card-body">

<h5><?=htmlspecialchars($p['title'])?></h5>

<p class="text-muted"><?=$p['location']?></p>

<!-- Star Rating Display -->
<div class="mb-2">
    <div class="stars-display" style="color:#ffc107;font-size:1rem;">
    <?php 
    $avg = round($p['avg_rating']);
    for($i=0;$i<5;$i++): 
        echo ($i < $avg) ? '★' : '☆';
    endfor; 
    ?>
    </div>
    <small class="text-muted"><?=$p['avg_rating']?>/5 (<?=$p['rating_count']?> ratings)</small>
</div>

<h6 class="text-primary"><?=$p['price']?> UGX</h6>

<div class="d-flex flex-wrap gap-1 mb-2">

<a href="/estate/views/property_detail.php?id=<?=$p['id']?>" class="btn btn-sm btn-outline-primary">
View
</a>

<?php if($savedModel->isSaved($p['id'], $_SESSION['user_id'])): ?>
<a href="/estate/controllers/index.php?action=unsaveProperty&id=<?=$p['id']?>" class="btn btn-sm btn-warning">
Unsave
</a>
<?php else: ?>
<a href="/estate/controllers/index.php?action=saveProperty&id=<?=$p['id']?>" class="btn btn-sm btn-outline-success">
Save
</a>
<?php endif; ?>

</div>

<!-- Rate Property Form -->
<?php if(!$propCtrl->hasUserRated($p['id'])): ?>
<form method="post" class="mt-2" style="border-top:1px solid #ccc;padding-top:10px;">
    <input type="hidden" name="rating_property_id" value="<?=$p['id']?>">
    <label style="font-size:0.85rem;">Rate this property:</label>
    <div class="rating-input" style="display:flex;gap:3px;cursor:pointer;">
    <?php for($i=1;$i<=5;$i++): ?>
        <input type="radio" name="rating_stars" value="<?=$i?>" id="star<?=$p['id']?>_<?=$i?>" style="display:none;">
        <label for="star<?=$p['id']?>_<?=$i?>" style="cursor:pointer;font-size:1.5rem;color:#ddd;margin:0;">★</label>
    <?php endfor; ?>
    </div>
    <button type="submit" class="btn btn-sm btn-primary mt-2" style="padding:0.25rem 0.5rem;font-size:0.8rem;">Submit Rating</button>
</form>
<script>
    document.querySelectorAll('#star<?=$p['id']?>_1, #star<?=$p['id']?>_2, #star<?=$p['id']?>_3, #star<?=$p['id']?>_4, #star<?=$p['id']?>_5').forEach(radio => {
        radio.addEventListener('change', function() {
            const val = this.value;
            for(let i=1;i<=5;i++) {
                const label = document.querySelector('label[for="star<?=$p['id']?>_' + i + '"]');
                label.style.color = (i <= val) ? '#ffc107' : '#ddd';
            }
        });
    });
</script>
<?php endif; ?>

</div>
</div>

</div>

<?php endforeach; ?>

</div>
</div>

<!-- SIDEBAR -->
<div class="col-lg-4">

<div class="sidebar mb-4">

<h5>Quick Stats</h5>

<ul class="list-group">
<li class="list-group-item d-flex justify-content-between">
Listings <span><?=$totalListings?></span>
</li>
<li class="list-group-item d-flex justify-content-between">
Saved <span><?=$totalSaved?></span>
</li>
<li class="list-group-item d-flex justify-content-between">
Inquiries <span><?=$totalInquiries?></span>
</li>
</ul>

</div>

<div class="sidebar">

<h5>Sponsored Adverts</h5>

<?php if(!empty($popupAds)): ?>
    <ul class="list-unstyled">
    <?php foreach($popupAds as $ad): ?>
        <li class="mb-3 text-center">
            <a href="<?=htmlspecialchars($ad['link']?:'#')?>" target="_blank">
                <img src="/estate/<?=htmlspecialchars($ad['image_path'])?>" class="img-fluid" style="max-height:100px;" alt="<?=htmlspecialchars($ad['title'])?>">
            </a>
            <div><?=htmlspecialchars($ad['title'])?></div>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No active adverts</p>
<?php endif; ?>

</div>

</div>

</div>

</div>

<!-- ADVERT POPUP -->
<?php if(!empty($popupAds)): ?>
<div class="modal fade" id="adModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title"></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">
<a href="#" target="_blank">
<img src="" class="img-fluid" alt="">
</a>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const adverts = <?=json_encode(array_values($popupAds))?>;
let adIndex = 0;
if(adverts.length){
    const modalEl = document.getElementById('adModal');
    const myModal = new bootstrap.Modal(modalEl);
    const titleEl = modalEl.querySelector('.modal-title');
    const linkEl = modalEl.querySelector('.modal-body a');
    const imgEl = linkEl.querySelector('img');
    function showNextAd(){
        if(adIndex >= adverts.length){
            // stop further showing
            return;
        }
        const ad = adverts[adIndex++];
        titleEl.textContent = ad.title;
        linkEl.href = ad.link || '#';
        imgEl.src = '/estate/' + ad.image_path;
        imgEl.alt = ad.title;
        myModal.show();
    }
    // show first immediately
    showNextAd();
    // then every 2 minutes until exhausted
    const intervalId = setInterval(()=>{
        showNextAd();
        if(adIndex >= adverts.length){
            clearInterval(intervalId);
        }
    }, 120000);
}

// Session activity tracking for timeout
let activityTimeout;
function resetActivityTimeout() {
    clearTimeout(activityTimeout);
    activityTimeout = setTimeout(() => {
        window.location.href = '/estate/views/timeout.php';
    }, 180000); // 3 minutes = 180000 milliseconds
}

function updateSessionActivity() {
    fetch('/estate/controllers/index.php?action=updateActivity', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'update_activity=1'
    }).catch(error => {
        console.error('Error updating session activity:', error);
    });
}

// Track user activity
['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
    document.addEventListener(event, () => {
        resetActivityTimeout();
        updateSessionActivity();
    }, true);
});

// Initialize activity timeout
resetActivityTimeout();
</script>
<?php endif; ?>

</body>
</html>