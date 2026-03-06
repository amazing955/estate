<?php
require_once __DIR__ . '/../controllers/PropertyController.php';
require_once __DIR__ . '/../controllers/AdvertController.php';
require_once __DIR__ . '/../models/PropertyImage.php';

$propCtrl = new PropertyController();
$advCtrl = new AdvertController();
$adverts = $advCtrl->allActive();

/* SEARCH */
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $filters['location'] = $_GET['location'] ?? '';
    $filters['type'] = $_GET['type'] ?? '';
    $filters['min_price'] = $_GET['min_price'] ?? '';
    $filters['max_price'] = $_GET['max_price'] ?? '';
    $properties = $propCtrl->search($filters);
} else {
    $properties = $propCtrl->listAll();
}
?>

<?php include 'header.php'; ?>

<style>

.hero{
height:420px;
background:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.6)),
url('/estate/assets/hero.jpg');
background-size:cover;
background-position:center;
display:flex;
align-items:center;
justify-content:center;
color:white;
text-align:center;
}

.hero h1{
font-size:48px;
font-weight:700;
}

.search-box{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 10px 25px rgba(0,0,0,0.2);
margin-top:-60px;
}

.property-card{
transition:0.3s;
border:none;
border-radius:10px;
overflow:hidden;
}

.property-card:hover{
transform:translateY(-5px);
box-shadow:0 15px 30px rgba(0,0,0,0.2);
}

.property-card img{
height:220px;
object-fit:cover;
}

.price{
font-size:20px;
font-weight:bold;
color:#0d6efd;
}

.rating{
color:#ffc107;
}

.section-title{
font-weight:700;
margin:40px 0 20px;
}

.advert-banner img{
border-radius:8px;
}

.advert-floating{
border-radius:10px;
overflow:hidden;
transition:0.3s;
}

.advert-floating:hover{
transform:scale(1.02);
box-shadow:0 15px 30px rgba(0,0,0,0.25);
}

</style>


<!-- HERO -->

<div class="hero">
<div>
<h1>Find Your Dream Property</h1>
<p>Search houses, apartments, land and commercial property</p>
</div>
</div>


<!-- SEARCH -->

<div class="container">

<div class="search-box">

<form class="row g-2" method="GET">

<div class="col-md-3">
<input type="text" name="location" class="form-control" placeholder="Location">
</div>

<div class="col-md-3">
<select name="type" class="form-control">
<option value="">Property Type</option>
<option value="House">House</option>
<option value="Apartment">Apartment</option>
<option value="Land">Land</option>
<option value="Commercial">Commercial</option>
</select>
</div>

<div class="col-md-2">
<input type="number" name="min_price" class="form-control" placeholder="Min Price">
</div>

<div class="col-md-2">
<input type="number" name="max_price" class="form-control" placeholder="Max Price">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100" name="search">Search</button>
</div>

</form>

</div>

</div>



<div class="container">


<!-- HOMEPAGE ADVERT -->

<?php foreach($adverts as $a): ?>
<?php if($a['position'] === 'homepage'): ?>

<div class="advert-banner my-4">

<a href="<?=htmlspecialchars($a['link'])?>">
<img src="/estate/<?=htmlspecialchars($a['image_path'])?>" class="img-fluid">
</a>

</div>

<?php endif; ?>
<?php endforeach; ?>


<h3 class="section-title">Latest Properties</h3>


<div class="row">

<div class="col-md-9">

<div class="row">

<?php
$count = 0;

$sidebarAds = array_filter($adverts, fn($a)=>$a['position']=='sidebar');
$sidebarAds = array_values($sidebarAds);

$adIndex = 0;

foreach ($properties as $p):

$count++;
?>

<div class="col-md-4 mb-4">

<div class="card property-card shadow-sm">

<?php
$images = (new PropertyImage())->findByProperty($p['id']);

if($images):
?>

<img src="/estate/<?php echo $images[0]['image_path']; ?>">

<?php else: ?>

<img src="/estate/assets/no-image.jpg">

<?php endif; ?>

<div class="card-body">

<h5><?=htmlspecialchars($p['title'])?></h5>


<div class="rating mb-1">

<?php
$avg = round($p['avg_rating']);

for($i=0;$i<5;$i++){
echo ($i<$avg) ? "★" : "☆";
}
?>

</div>


<p class="text-muted">

<?=substr(htmlspecialchars($p['description']),0,80)?>...

</p>


<div class="price">

UGX <?=number_format($p['price'])?>

</div>


<a href="property_detail.php?id=<?=$p['id']?>" class="btn btn-outline-primary mt-2 w-100">

View Details

</a>


</div>

</div>

</div>



<?php

/* SHOW ADVERT AFTER 2 ROWS (6 PROPERTIES) */

if($count % 6 == 0 && isset($sidebarAds[$adIndex])):

$ad = $sidebarAds[$adIndex];

$adIndex++;

?>

<div class="col-12 mb-4">

<div class="card advert-floating shadow-sm">

<a href="<?=htmlspecialchars($ad['link'])?>">

<img src="/estate/<?=htmlspecialchars($ad['image_path'])?>" class="img-fluid w-100">

</a>

</div>

</div>

<?php endif; ?>


<?php endforeach; ?>


</div>

</div>



<!-- SIDEBAR -->

<div class="col-md-3">

<h5>Sponsored</h5>

<?php foreach($adverts as $a): ?>

<?php if ($a['position'] === 'sidebar'): ?>

<div class="mb-3">

<a href="<?=htmlspecialchars($a['link'])?>">

<img src="/estate/<?=htmlspecialchars($a['image_path'])?>" class="img-fluid rounded">

</a>

</div>

<?php endif; ?>

<?php endforeach; ?>

</div>

</div>

</div>


<?php include 'footer.php'; ?>