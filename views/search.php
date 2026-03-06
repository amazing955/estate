<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PropertyController.php';
require_once __DIR__ . '/../models/PropertyImage.php';
require_once __DIR__ . '/../models/SavedProperty.php';

$propCtrl = new PropertyController();
$savedModel = new SavedProperty();

$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters['location'] = $_GET['location'] ?? '';
    $filters['type'] = $_GET['type'] ?? '';
    $filters['min_price'] = $_GET['min_price'] ?? '';
    $filters['max_price'] = $_GET['price'] ?? $_GET['max_price'] ?? '';
    $properties = $propCtrl->search($filters);
} else {
    $properties = [];
}

// handle rating submission
$ratingMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_property_id'])) {
    $prop_id = (int)$_POST['rating_property_id'];
    $rating = (int)$_POST['rating_stars'];
    if ($rating >= 1 && $rating <= 5) {
        if ($propCtrl->submitRating($prop_id, $rating)) {
            $ratingMessage = 'Rating submitted!';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Search Properties</h2>

    <!-- Search Form -->
    <form method="GET" class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="location" class="form-control" placeholder="Location" value="<?=htmlspecialchars($filters['location'] ?? '')?>">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="House" <?=(isset($_GET['type']) && $_GET['type']=='House') ? 'selected' : ''?>>House</option>
                    <option value="Apartment" <?=(isset($_GET['type']) && $_GET['type']=='Apartment') ? 'selected' : ''?>>Apartment</option>
                    <option value="Land" <?=(isset($_GET['type']) && $_GET['type']=='Land') ? 'selected' : ''?>>Land</option>
                    <option value="Commercial" <?=(isset($_GET['type']) && $_GET['type']=='Commercial') ? 'selected' : ''?>>Commercial</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="min_price" class="form-control" placeholder="Min Price" value="<?=htmlspecialchars($filters['min_price'] ?? '')?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="max_price" class="form-control" placeholder="Max Price" value="<?=htmlspecialchars($filters['max_price'] ?? '')?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    <?php if($ratingMessage): ?><div class="alert alert-success"><?=htmlspecialchars($ratingMessage)?></div><?php endif; ?>

    <!-- Results -->
    <div class="row">
        <?php if(!empty($properties)): ?>
            <?php foreach ($properties as $p): ?>
                <div class="col-md-4 mb-4">
                    <div class="card property-card shadow-sm">
                        <?php
                        $images = (new PropertyImage())->findByProperty($p['id']);
                        if ($images):
                        ?>
                            <img src="/estate/<?php echo $images[0]['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover;" />
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?=htmlspecialchars($p['title'])?></h5>
                            
                            <!-- Star Rating Display -->
                            <div class="mb-2" style="color:#ffc107;font-size:0.9rem;">
                            <?php 
                            $avg = round($p['avg_rating']);
                            for($i=0;$i<5;$i++): 
                                echo ($i < $avg) ? '★' : '☆';
                            endfor; 
                            ?>
                            <small class="text-muted" style="color:#666;display:block;"><?=$p['avg_rating']?>/5 (<?=$p['rating_count']?> ratings)</small>
                            </div>

                            <p class="card-text"><small><?=htmlspecialchars($p['location'])?></small></p>
                            <p class="card-text"><strong>Price:</strong> <?=htmlspecialchars($p['price'])?></p>

                            <div class="btn-group w-100 mb-2" role="group">
                                <a href="property_detail.php?id=<?=$p['id']?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php if(AuthController::check()): ?>
                                    <?php if($savedModel->isSaved($p['id'], $_SESSION['user_id'])): ?>
                                        <a href="/estate/controllers/index.php?action=unsaveProperty&id=<?=$p['id']?>" class="btn btn-sm btn-warning">Unsave</a>
                                    <?php else: ?>
                                        <a href="/estate/controllers/index.php?action=saveProperty&id=<?=$p['id']?>" class="btn btn-sm btn-outline-success">Save</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if(AuthController::check() && $_SESSION['role']==='client'): ?>
                                    <a href="/estate/views/book_tour.php?id=<?=$p['id']?>" class="btn btn-sm btn-info">Tour</a>
                                <?php endif; ?>
                            </div>

                            <!-- Rate Property Form -->
                            <?php if(AuthController::check() && $_SESSION['role']=='client' && !$propCtrl->hasUserRated($p['id'])): ?>
                            <form method="post" class="mt-2" style="border-top:1px solid #ccc;padding-top:10px;">
                                <input type="hidden" name="rating_property_id" value="<?=$p['id']?>">
                                <label style="font-size:0.8rem;">Rate:</label>
                                <div class="rating-input" style="display:flex;gap:3px;cursor:pointer;">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <input type="radio" name="rating_stars" value="<?=$i?>" id="star_s<?=$p['id']?>_<?=$i?>" style="display:none;">
                                    <label for="star_s<?=$p['id']?>_<?=$i?>" style="cursor:pointer;font-size:1.2rem;color:#ddd;margin:0;">★</label>
                                <?php endfor; ?>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mt-2" style="padding:0.25rem 0.5rem;font-size:0.8rem;">Submit</button>
                            </form>
                            <script>
                                document.querySelectorAll('#star_s<?=$p['id']?>_1, #star_s<?=$p['id']?>_2, #star_s<?=$p['id']?>_3, #star_s<?=$p['id']?>_4, #star_s<?=$p['id']?>_5').forEach(radio => {
                                    radio.addEventListener('change', function() {
                                        const val = this.value;
                                        for(let i=1;i<=5;i++) {
                                            const label = document.querySelector('label[for=\"star_s<?=$p['id']?>_' + i + '\"]');
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
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No properties found matching your search criteria.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
