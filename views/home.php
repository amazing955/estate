<?php
require_once __DIR__ . '/../controllers/PropertyController.php';
require_once __DIR__ . '/../controllers/AdvertController.php';
require_once __DIR__ . '/../models/PropertyImage.php';
$propCtrl = new PropertyController();
$advCtrl = new AdvertController();
$adverts = $advCtrl->allActive();

// handle search
$filters = [];
$isSearching = false;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['search']) || !empty($_GET['location']) || !empty($_GET['type']) || !empty($_GET['min_price']) || !empty($_GET['max_price']))) {
    $isSearching = true;
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
<?php if (!empty($adverts)): ?>
    <div class="mb-4">
        <?php foreach($adverts as $a): ?>
            <?php if ($a['position'] === 'homepage'): ?>
                <a href="<?=htmlspecialchars($a['link'])?>"><img src="/estate/<?=htmlspecialchars($a['image_path'])?>" class="img-fluid" alt="<?=htmlspecialchars($a['title'])?>"></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<h2>Property Listings</h2>
<form class="form-inline mb-3" method="get">
    <input type="text" name="location" class="form-control mr-2" placeholder="Location" value="<?=htmlspecialchars($_GET['location'] ?? '')?>">
    <select name="type" class="form-control mr-2">
        <option value="">All Types</option>
        <option value="House" <?=(isset($_GET['type']) && $_GET['type']=='House') ? 'selected' : ''?>>House</option>
        <option value="Apartment" <?=(isset($_GET['type']) && $_GET['type']=='Apartment') ? 'selected' : ''?>>Apartment</option>
        <option value="Land" <?=(isset($_GET['type']) && $_GET['type']=='Land') ? 'selected' : ''?>>Land</option>
        <option value="Commercial" <?=(isset($_GET['type']) && $_GET['type']=='Commercial') ? 'selected' : ''?>>Commercial</option>
    </select>
    <input type="number" name="min_price" class="form-control mr-2" placeholder="Min Price" value="<?=htmlspecialchars($_GET['min_price'] ?? '')?>">
    <input type="number" name="max_price" class="form-control mr-2" placeholder="Max Price" value="<?=htmlspecialchars($_GET['max_price'] ?? '')?>">
    <button type="submit" name="search" class="btn btn-secondary">Search</button>
</form>
<div class="row home-listing">
    <div class="col-md-9">
        <div class="row home-listing">
        <?php foreach ($properties as $p): ?>
            <div class="col-md-4 mb-4">
                <div class="card property-card shadow-sm">
                    <?php
                    // fetch first image
                    $images = (new PropertyImage())->findByProperty($p['id']);
                    if ($images):
                    ?>
                        <img src="/estate/<?php echo $images[0]['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover;" />
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?=htmlspecialchars($p['title'])?></h5>                        <!-- Star Rating Display -->
                        <div class=\"mb-2\" style=\"color:#ffc107;font-size:0.9rem;\">
                        <?php 
                        $avg = round($p['avg_rating']);
                        for($i=0;$i<5;$i++): 
                            echo ($i < $avg) ? '★' : '☆';
                        endfor; 
                        ?>
                        <small class=\"text-muted\" style=\"color:#666;display:block;\"><?=$p['avg_rating']?>/5 (<?=$p['rating_count']?> ratings)</small>
                        </div>                        <p class="card-text"><?=substr(htmlspecialchars($p['description']),0,100)?>...</p>
                        <p class="card-text"><strong>Price:</strong> <?=htmlspecialchars($p['price'])?></p>
                        <a href="property_detail.php?id=<?=$p['id']?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-3">
        <!-- sidebar adverts -->
        <?php foreach($adverts as $a): ?>
            <?php if ($a['position'] === 'sidebar'): ?>
                <div class="mb-3">
                    <a href="<?=htmlspecialchars($a['link'])?>"><img src="/estate/<?=htmlspecialchars($a['image_path'])?>" class="img-fluid" alt="<?=htmlspecialchars($a['title'])?>"></a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer.php'; ?>