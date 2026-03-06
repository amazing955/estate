<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Property.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Inquiry.php';
require_once __DIR__ . '/../../models/Notification.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}

// Check for session timeout
if (!AuthController::checkSessionTimeout()) {
    // This will redirect to timeout page if session expired
}

$propModel = new Property();
$userModel = new User();
$inquiryModel = new Inquiry();
$notifModel = new Notification();

$allProperties = $propModel->all();
$allUsers = $userModel->all();

$owners = array_filter($allUsers, fn($u)=>$u['role']==='owner');
$brokers = array_filter($allUsers, fn($u)=>$u['role']==='broker');
$clients = array_filter($allUsers, fn($u)=>$u['role']==='client');
$admins = array_filter($allUsers, fn($u)=>$u['role']==='admin');

// Limit initial display to 5 properties
$displayProperties = array_slice($allProperties, 0, 5);
$hasMoreProperties = count($allProperties) > 5;

//$notifications = $notifModel->findByUser($_SESSION['user_id']);
?>

<?php include __DIR__ . '/../header.php'; ?>

<div class="container-fluid dashboard-wrapper">

<h2 class="mb-4 dashboard-title">Admin Dashboard</h2>

<!-- SYSTEM STATS -->
<div class="row mb-4">

<div class="col-md-3">
<div class="card stat-card shadow">
<div class="card-body">
<h6>Total Properties</h6>
<h2><?=count($allProperties)?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card shadow">
<div class="card-body">
<h6>Total Users</h6>
<h2><?=count($allUsers)?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card shadow">
<div class="card-body">
<h6>Brokers</h6>
<h2><?=count($brokers)?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card shadow">
<div class="card-body">
<h6>Clients</h6>
<h2><?=count($clients)?></h2>
</div>
</div>
</div>

</div>


<!-- USER DISTRIBUTION -->
<div class="row mb-4">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-dark text-white">
User Distribution
</div>

<div class="card-body">

<ul class="list-group">

<li class="list-group-item d-flex justify-content-between">
Estate Owners
<span class="badge bg-primary"><?=count($owners)?></span>
</li>

<li class="list-group-item d-flex justify-content-between">
Brokers
<span class="badge bg-success"><?=count($brokers)?></span>
</li>

<li class="list-group-item d-flex justify-content-between">
Clients
<span class="badge bg-info"><?=count($clients)?></span>
</li>

<li class="list-group-item d-flex justify-content-between">
Admins
<span class="badge bg-danger"><?=count($admins)?></span>
</li>

</ul>

</div>
</div>

</div>


<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-primary text-white">
Notifications
</div>

<div class="card-body">

<?php if(empty($notifications)): ?>

<p>No notifications</p>

<?php else: ?>

<ul class="list-group">

<?php foreach ($notifications as $n): ?>

<li class="list-group-item d-flex justify-content-between">

<?=htmlspecialchars($n['message'])?>

<?php if(!$n['is_read']): ?>

<a href="/estate/controllers/index.php?action=markNotificationRead&id=<?=$n['id']?>" 
class="btn btn-sm btn-success">
Mark Read
</a>

<?php endif; ?>

</li>

<?php endforeach; ?>

</ul>

<?php endif; ?>

</div>
</div>

</div>

</div>


<!-- ADVERT & USER/INQUIRY MANAGEMENT -->
<div class="mb-4">
    <a href="adverts.php" class="btn btn-warning btn-lg shadow mr-2">
        Manage Adverts
    </a>
    <a href="users.php" class="btn btn-info btn-lg shadow mr-2">
        Manage Users
    </a>
    <a href="system_logs.php" class="btn btn-secondary btn-lg shadow mr-2">
        System Logs
    </a>
    <a href="inquiries.php" class="btn btn-secondary btn-lg shadow mr-2">
        View Inquiries
    </a>
    <a href="send_message.php" class="btn btn-success btn-lg shadow">
        Send Message to Users
    </a>
</div>


<!-- PROPERTY MANAGEMENT -->
<div class="card shadow">

<div class="card-header bg-secondary text-white">
All Property Listings
</div>

<div class="card-body">

<table class="table table-hover">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Title</th>
<th>Owner</th>
<th>Status</th>
<th>Actions</th>
</tr>

</thead>

<tbody id="propertiesTableBody">

<?php foreach($displayProperties as $p): ?>

<tr>

<td><?=$p['id']?></td>

<td><?=htmlspecialchars($p['title'])?></td>

<td><?=$p['owner_id']?></td>

<td>
<span class="badge bg-success">
<?=htmlspecialchars($p['status'])?>
</span>
</td>

<td>

<a href="/estate/controllers/index.php?action=deleteProperty&id=<?=$p['id']?>" 
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this property?');">
Delete
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php if($hasMoreProperties): ?>
<div class="text-center mt-3">
<button id="viewMoreBtn" class="btn btn-primary" onclick="loadMoreProperties()">View More Properties</button>
</div>
<?php endif; ?>

</div>

</div>

</div>

<script>
function loadMoreProperties() {
    const btn = document.getElementById('viewMoreBtn');
    btn.disabled = true;
    btn.innerHTML = 'Loading...';

    fetch('/estate/controllers/index.php?action=getMoreProperties&offset=5&getAll=true')
        .then(response => response.json())
        .then(data => {
            if (data.properties && data.properties.length > 0) {
                const tbody = document.getElementById('propertiesTableBody');
                data.properties.forEach(p => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${p.id}</td>
                        <td>${p.title.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</td>
                        <td>${p.owner_id}</td>
                        <td><span class="badge bg-success">${p.status.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span></td>
                        <td>
                            <a href="/estate/controllers/index.php?action=deleteProperty&id=${p.id}" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this property?');">
                               Delete
                            </a>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                btn.style.display = 'none';
            } else {
                btn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading more properties:', error);
            btn.disabled = false;
            btn.innerHTML = 'View More Properties';
        });
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

// auto-refresh admin dashboard every 5 seconds
setInterval(() => { window.location.reload(); }, 5000);
</script>

<?php include __DIR__ . '/../footer.php'; ?>