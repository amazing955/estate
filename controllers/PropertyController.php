<?php
// controllers/PropertyController.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/PropertyImage.php';
require_once __DIR__ . '/../models/PropertyVideo.php';
require_once __DIR__ . '/../models/Rating.php';

class PropertyController {
    private $propertyModel;
    private $imageModel;
    private $videoModel;
    private $saveModel;
    private $ratingModel;

    public function __construct() {
        $this->propertyModel = new Property();
        $this->imageModel = new PropertyImage();
        $this->videoModel = new PropertyVideo();
        $this->ratingModel = new Rating();
        require_once __DIR__ . '/../models/SavedProperty.php';
        $this->saveModel = new SavedProperty();
    }

    // returns array with status and errors
    public function create($data, $files) {
        $errors = [];
        // Insert property and get id
        $owner_id = $_SESSION['user_id'];
        $property_id = $this->propertyModel->create(
            $owner_id,
            $data['title'],
            $data['description'],
            $data['price'],
            $data['type'],
            $data['location'],
            $data['status'] ?? 'Available'
        );
        if ($property_id) {
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('property', "User {$owner_id} created property {$property_id}");
            // send email to actor if they have a privileged role
            $role = $_SESSION['role'] ?? '';
            if (in_array($role, ['admin','owner','broker'])) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                $nc->sendEmail($owner_id,
                    'Dashboard Update – Property Created',
                    "You have successfully created a new property (ID {$property_id}).");
            }
        }

        if (!$property_id) {
            return ['status' => false, 'errors' => ['Unable to create property']];
        }

        // handle images
        if (!empty($files['images']['name'][0])) {
            $ok = $this->uploadImages($property_id, $files['images']);
            if (!$ok) {
                $errors[] = 'One or more images could not be uploaded.';
            }
        }
        if (!empty($files['video']['name'])) {
            $ok = $this->uploadVideo($property_id, $files['video']);
            if (!$ok) {
                $errors[] = 'Video upload failed.';
            }
        }

        return ['status' => true, 'errors' => $errors];
    }

    private function uploadImages($property_id, $images) {
        $folder = __DIR__ . '/../uploads/images/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $allowed = ['jpg','jpeg','png','gif'];
        $success = true;
        foreach ($images['name'] as $index => $name) {
            if ($images['error'][$index] !== UPLOAD_ERR_OK) {
                $success = false;
                continue;
            }
            $tmp = $images['tmp_name'][$index];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $success = false;
                continue;
            }
            $filename = uniqid() . ".{$ext}";
            if (move_uploaded_file($tmp, $folder . $filename)) {
                $this->imageModel->add($property_id, 'uploads/images/' . $filename);
            } else {
                $success = false;
            }
        }
        return $success;
    }

    private function uploadVideo($property_id, $video) {
        $folder = __DIR__ . '/../uploads/videos/';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $allowed = ['mp4','mov','avi','mkv'];
        if ($video['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        $name = $video['name'];
        $tmp = $video['tmp_name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) return false;
        $filename = uniqid() . ".{$ext}";
        if (move_uploaded_file($tmp, $folder . $filename)) {
            $this->videoModel->add($property_id, 'uploads/videos/' . $filename);
            return true;
        }
        return false;
    }

    public function update($id, $data, $files) {
        $oldProp = $this->propertyModel->findById($id);
        $this->propertyModel->update($id, $data);
        require_once __DIR__ . '/UserController.php';
        $uc = new UserController();
        $uc->addSystemLog('property', "User {$_SESSION['user_id']} updated property {$id}");
        // also email the user notifying them of the update
        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['admin','owner','broker'])) {
            require_once __DIR__ . '/NotificationController.php';
            $nc = new NotificationController();
            $nc->sendEmail($_SESSION['user_id'],
                'Dashboard Update – Property Updated',
                "Your property (ID {$id}) was updated successfully.");
        }
        if (!empty($files['images']['name'][0])) {
            $this->uploadImages($id, $files['images']);
        }
        if (!empty($files['video']['name'])) {
            $this->uploadVideo($id, $files['video']);
        }
        // if status changed to sold, notify brokers whose reposts were removed
        if (isset($data['status']) && $data['status'] === 'Sold' && $oldProp && $oldProp['status'] !== 'Sold') {
            $reposts = $this->propertyModel->findReposts($id);
            if (!empty($reposts)) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                foreach ($reposts as $r) {
                    $nc->create($r['owner_id'], "The property you reposted (ID {$r['id']}) has been removed because the original listing was sold.");
                }
            }
        }
    }

    public function delete($id) {
        // collect repost info before deletion so we can notify brokers
        $reposts = $this->propertyModel->findReposts($id);
        $result = $this->propertyModel->delete($id);
        if ($result) {
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('property', "User {$_SESSION['user_id']} deleted property {$id}");
            if (!empty($reposts)) {
                require_once __DIR__ . '/NotificationController.php';
                $nc = new NotificationController();
                foreach ($reposts as $r) {
                    $nc->create($r['owner_id'], "The property you reposted (ID {$r['id']}) has been removed because the original listing was deleted.");
                }
            }
        }
        return $result;
    }

    public function view($id) {
        $property = $this->propertyModel->findById($id);
        $images = $this->imageModel->findByProperty($id);
        $videos = $this->videoModel->findByProperty($id);
        return ['property' => $property, 'images' => $images, 'videos' => $videos];
    }

    /**
     * Update only the status of a property and notify brokers when reposts are removed.
     */
    public function changeStatus($id, $status) {
        $prop = $this->propertyModel->findById($id);
        if (!$prop) return false;
        // if going to sold, gather reposts to notify
        $reposts = [];
        if ($status === 'Sold') {
            $reposts = $this->propertyModel->findReposts($id);
        }
        $res = $this->propertyModel->update($id, ['status' => $status]);
        if ($res && !empty($reposts)) {
            require_once __DIR__ . '/NotificationController.php';
            $nc = new NotificationController();
            foreach ($reposts as $r) {
                $nc->create($r['owner_id'], "The property you reposted (ID {$r['id']}) has been removed because the original listing was sold.");
            }
        }
        return $res;
    }

    public function listAll() {
        // for clients we only want properties that are still available
        $properties = $this->propertyModel->allAvailable();
        // add average rating to each property and sort
        foreach ($properties as &$p) {
            $ratingInfo = $this->ratingModel->getAverageRating($p['id']);
            $p['avg_rating'] = $ratingInfo['avg_rating'] ? round($ratingInfo['avg_rating'], 1) : 0;
            $p['rating_count'] = $ratingInfo['rating_count'] ? (int)$ratingInfo['rating_count'] : 0;
        }
        // sort by avg_rating descending
        usort($properties, function($a, $b) {
            return $b['avg_rating'] <=> $a['avg_rating'];
        });
        return $properties;
    }

    public function listByOwner($owner_id) {
        return $this->propertyModel->findByOwner($owner_id);
    }

    public function search($filters) {
        $properties = $this->propertyModel->search($filters);
        // add average rating to each property and sort
        foreach ($properties as &$p) {
            $ratingInfo = $this->ratingModel->getAverageRating($p['id']);
            $p['avg_rating'] = $ratingInfo['avg_rating'] ? round($ratingInfo['avg_rating'], 1) : 0;
            $p['rating_count'] = $ratingInfo['rating_count'] ? (int)$ratingInfo['rating_count'] : 0;
        }
        // sort by avg_rating descending
        usort($properties, function($a, $b) {
            return $b['avg_rating'] <=> $a['avg_rating'];
        });
        return $properties;
    }

    // save/unsave property for client
    public function saveForClient($property_id, $client_id) {
        return $this->saveModel->save($property_id, $client_id);
    }

    public function unsaveForClient($property_id, $client_id) {
        return $this->saveModel->unsave($property_id, $client_id);
    }

    public function isSaved($property_id, $client_id) {
        return $this->saveModel->isSaved($property_id, $client_id);
    }

    public function hasUserRated($property_id) {
        if (!isset($_SESSION['user_id'])) return false;
        return $this->ratingModel->hasRated($property_id, $_SESSION['user_id']);
    }

    // duplicate property record for another owner (used when broker reposts)
    public function repost($property_id, $new_owner_id) {
        $prop = $this->propertyModel->findById($property_id);
        if (!$prop) return false;
        // only allow repost of available listings
        if ($prop['status'] !== 'Available') {
            return false;
        }
        // copy fields except id and owner_id
        $newId = $this->propertyModel->create(
            $new_owner_id,
            $prop['title'],
            $prop['description'],
            $prop['price'],
            $prop['type'],
            $prop['location'],
            $prop['status'], // keep same status
            $prop['owner_id'] // record original owner
        );
        if ($newId) {
            // duplicate images
            $images = (new PropertyImage())->findByProperty($property_id);
            foreach ($images as $img) {
                $this->imageModel->add($newId, $img['image_path']);
            }
            // duplicate video if exists
            $videos = (new PropertyVideo())->findByProperty($property_id);
            foreach ($videos as $vid) {
                $this->videoModel->add($newId, $vid['video_path']);
            }
            // log system event
            require_once __DIR__ . '/UserController.php';
            $uc = new UserController();
            $uc->addSystemLog('property', "User {$_SESSION['user_id']} reposted property {$property_id} as {$newId}");
            return $newId;
        }
        return false;
    }

    public function submitRating($property_id, $rating, $comment='') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
            return false;
        }
        return $this->ratingModel->add($property_id, $_SESSION['user_id'], $rating, $comment);
    }
}
