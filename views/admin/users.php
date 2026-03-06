<?php 
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}

$uc = new UserController();
$users = $uc->all();
?>

<?php include __DIR__ . '/../header.php'; ?>

<style>

.page-title{
font-size:28px;
font-weight:600;
margin-bottom:20px;
color:#333;
}

.dashboard-cards{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
margin-bottom:30px;
}

.card{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
text-align:center;
}

.card h3{
margin:0;
font-size:24px;
color:#2c3e50;
}

.card p{
margin:5px 0 0;
color:#777;
}

.table-container{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
overflow-x:auto;
}

table{
width:100%;
border-collapse:collapse;
}

th{
background:#f4f6f9;
padding:12px;
text-align:left;
font-weight:600;
}

td{
padding:12px;
border-top:1px solid #eee;
}

tr:hover{
background:#fafafa;
}

.badge{
padding:5px 10px;
border-radius:20px;
font-size:12px;
font-weight:600;
}

.badge-admin{background:#ff7675;color:white;}
.badge-owner{background:#00b894;color:white;}
.badge-broker{background:#0984e3;color:white;}
.badge-client{background:#636e72;color:white;}

.btn{
padding:6px 12px;
border:none;
border-radius:5px;
font-size:13px;
cursor:pointer;
text-decoration:none;
margin-right:5px;
}

.btn-view{background:#3498db;color:white;}
.btn-update{background:#27ae60;color:white;}
.btn-delete{background:#e74c3c;color:white;}

select{
padding:4px;
border-radius:5px;
border:1px solid #ccc;
}

</style>


<h2 class="page-title">Manage Users</h2>
<p>
    <a href="user_logs.php" class="btn btn-sm btn-primary">View All User Logs</a>
    <a href="system_logs.php" class="btn btn-sm btn-secondary">View System Logs</a>
</p>

<?php
$admins=0;$owners=0;$brokers=0;$clients=0;
foreach($users as $u){
if($u['role']=="admin")$admins++;
if($u['role']=="owner")$owners++;
if($u['role']=="broker")$brokers++;
if($u['role']=="client")$clients++;
}
?>

<div class="dashboard-cards">

<div class="card">
<h3><?=count($users)?></h3>
<p>Total Users</p>
</div>

<div class="card">
<h3><?=$admins?></h3>
<p>Admins</p>
</div>

<div class="card">
<h3><?=$owners?></h3>
<p>Owners</p>
</div>

<div class="card">
<h3><?=$brokers?></h3>
<p>Brokers</p>
</div>

<div class="card">
<h3><?=$clients?></h3>
<p>Clients</p>
</div>

</div>


<div class="table-container">

<table>

<thead>
<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($users as $u): ?>

<tr>

<td><?=$u['id']?></td>

<td><?=htmlspecialchars($u['username'])?></td>

<td><?=htmlspecialchars($u['email'])?></td>

<td>

<?php
$role=$u['role'];
echo "<span class='badge badge-$role'>".ucfirst($role)."</span>";
?>

</td>

<td>
    <?php if($u['role']==='broker' && !$u['broker_approved']): ?>
        <a href="/estate/controllers/index.php?action=approveBroker&id=<?=$u['id']?>" class="btn btn-sm btn-success">Approve</a>
    <?php elseif($u['role']==='broker' && $u['broker_approved']): ?>
        <span class="badge badge-success">Broker approved</span>
    <?php endif; ?>

<a href="user_activity.php?id=<?=$u['id']?>" class="btn btn-view">
View
</a>

<form method="post" action="/estate/controllers/index.php?action=changeUserRole" style="display:inline;">

<input type="hidden" name="id" value="<?=$u['id']?>">

<select name="role">

<?php foreach(['admin','owner','broker','client'] as $r): ?>

<option value="<?=$r?>" <?=$u['role']==$r?'selected':''?>>
<?=ucfirst($r)?>
</option>

<?php endforeach; ?>

</select>

<button class="btn btn-update">Update</button>

</form>

<?php if($u['id'] != $_SESSION['user_id']): ?>

<a href="/estate/controllers/index.php?action=deleteUser&id=<?=$u['id']?>" 
class="btn btn-delete"
onclick="return confirm('Delete user?');">
Delete
</a>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include __DIR__ . '/../footer.php'; ?>