<?php
require_once __DIR__ . '/../controllers/PropertyController.php';
require_once __DIR__ . '/../controllers/InquiryController.php';
require_once __DIR__ . '/../models/Rating.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$propCtrl = new PropertyController();
$inqCtrl = new InquiryController();
$ratingModel = new Rating();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: home.php');
    exit;
}
$info = $propCtrl->view($id);
$property = $info['property'];
$images = $info['images'];
$videos = $info['videos'];

// if property isn't available and the visitor is not the owner, hide it from clients
if ($property && $property['status'] !== 'Available') {
    $isOwner = AuthController::check() && $_SESSION['user_id'] === $property['owner_id'];
    if (!$isOwner) {
        // simple message and halt further rendering
        include 'header.php';
        echo '<div class="container mt-4"><div class="alert alert-warning">This property is no longer available.</div></div>';
        include 'footer.php';
        exit;
    }
}

// Get rating info
$ratingInfo = $ratingModel->getAverageRating($id);
$allRatings = $ratingModel->findByProperty($id);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquire'])) {
    $msg = $_POST['message'] ?? '';
    if ($inqCtrl->makeInquiry($id, $msg)) {
        $message = 'Inquiry sent successfully';
    } else {
        $message = 'Failed to send inquiry';
    }
}

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
<?php include 'header.php'; ?>
<h2><?=htmlspecialchars($property['title'])?></h2>
<div class="row">
    <div class="col-md-8">
        <?php if ($images): ?>
            <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $img): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : ''?>">
                            <img src="/estate/<?=htmlspecialchars($img['image_path'])?>" class="d-block w-100" style="height:400px; object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if(count($images) > 1): ?>
                <a class="carousel-control-prev" href="#propertyCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#propertyCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($videos): ?>
            <h4 class="mt-3">Video</h4>
            <video width="100%" controls>
                <source src="/estate/<?=htmlspecialchars($videos[0]['video_path'])?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        <?php endif; ?>
        <p class="mt-3"><?=nl2br(htmlspecialchars($property['description']))?></p>
        <p><strong>Price:</strong> <?=htmlspecialchars($property['price'])?></p>
        <p><strong>Type:</strong> <?=htmlspecialchars($property['type'])?></p>
        <p><strong>Location:</strong> <?=htmlspecialchars($property['location'])?></p>
        <p><strong>Status:</strong> <?=htmlspecialchars($property['status'])?></p>
    </div>
    <div class="col-md-4">
        <?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
        <?php if (AuthController::check() && $_SESSION['role']=='client'): ?>
            <?php
                $isSaved = (new PropertyController())->isSaved($id, $_SESSION['user_id']);
            ?>
            <?php if ($isSaved): ?>
                <a href="/estate/controllers/index.php?action=unsaveProperty&id=<?=$id?>" class="btn btn-warning mb-2">Unsave Property</a>
            <?php else: ?>
                <a href="/estate/controllers/index.php?action=saveProperty&id=<?=$id?>" class="btn btn-outline-primary mb-2">Save Property</a>
            <?php endif; ?>
            <a href="/estate/views/book_tour.php?id=<?=$id?>" class="btn btn-info mb-2">Book Tour</a>
            <h4>I'm Interested</h4>
            <form method="post">
                <div class="form-group">
                    <label>Message (optional):</label>
                    <textarea name="message" class="form-control"></textarea>
                </div>
                <button type="submit" name="inquire" class="btn btn-success">Send Inquiry</button>
            </form>
        <?php else: ?>
            <p><a href="/estate/views/login.php" class="btn btn-primary">Login as Client to enquire</a></p>
        <?php endif; ?>

        <!-- RATINGS SECTION -->
        <hr>
        <h4>Ratings & Reviews</h4>
        <div class="mb-3">
            <div style="color:#ffc107;font-size:1.5rem;margin-bottom:5px;">
            <?php 
            $avg = round($ratingInfo['avg_rating'] ?? 0);
            for($i=0;$i<5;$i++): 
                echo ($i < $avg) ? '★' : '☆';
            endfor; 
            ?>
            </div>
            <small class="text-muted"><?=($ratingInfo['avg_rating'] ? round($ratingInfo['avg_rating'], 1) : 0)?>/5 (<?=($ratingInfo['rating_count'] ?? 0)?> ratings)</small>
        </div>

        <!-- Rate Property Form -->
        <?php if(AuthController::check() && $_SESSION['role']=='client' && !$propCtrl->hasUserRated($id)): ?>
        <form method="post" class="mb-3" style="border-top:1px solid #ccc;padding-top:10px;">
            <input type="hidden" name="rating_property_id" value="<?=$id?>">
            <label style="font-size:0.9rem;"><strong>Rate this property:</strong></label>
            <div class="rating-input" style="display:flex;gap:5px;cursor:pointer;font-size:1.8rem;margin-bottom:10px;">
            <?php for($i=1;$i<=5;$i++): ?>
                <input type="radio" name="rating_stars" value="<?=$i?>" id="star_<?=$i?>" style="display:none;">
                <label for="star_<?=$i?>" style="cursor:pointer;color:#ddd;margin:0;">★</label>
            <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Submit Rating</button>
        </form>
        <script>
            document.querySelectorAll('#star_1, #star_2, #star_3, #star_4, #star_5').forEach(radio => {
                radio.addEventListener('change', function() {
                    const val = this.value;
                    for(let i=1;i<=5;i++) {
                        const label = document.querySelector('label[for=\"star_' + i + '\"]');
                        label.style.color = (i <= val) ? '#ffc107' : '#ddd';
                    }
                });
            });
        </script>
        <?php endif; ?>

        <!-- Display All Ratings -->
        <?php if(!empty($allRatings)): ?>
        <div style="margin-top:15px;border-top:1px solid #ccc;padding-top:15px;">
            <h5>Reviews</h5>
            <?php foreach($allRatings as $r): ?>
            <div style="margin-bottom:10px;padding:10px;background:#f9f9f9;border-radius:5px;">
                <strong><?=htmlspecialchars($r['username']);?></strong>
                <div style="color:#ffc107;font-size:0.9rem;"><?php for($i=0;$i<5;$i++): echo ($i < $r['rating']) ? '★' : '☆'; endfor; ?></div>
                <?php if(!empty($r['comment'])): ?>
                <p style="font-size:0.9rem;margin-top:5px;"><?=htmlspecialchars($r['comment']);?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>