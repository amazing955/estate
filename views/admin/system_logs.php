<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

if (!AuthController::check() || $_SESSION['role'] !== 'admin') {
    header('Location: /estate/views/login.php');
    exit;
}

$uc = new UserController();
$logs = $uc->systemLogs();
?>
<?php include __DIR__ . '/../header.php'; ?>
<style>
.table-container{background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow-x:auto;}
th{background:#f4f6f9;padding:12px;text-align:left;font-weight:600;}
td{padding:12px;border-top:1px solid #eee;}
tr:hover{background:#fafafa;}
</style>
<h2>System Logs (last 30 days)</h2>
<div class="table-container">
<table>
    <thead>
        <tr>
            <th>Type</th>
            <th>Message</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($logs as $log): ?>
        <tr>
            <td><?=htmlspecialchars($log['type'])?></td>
            <td><?=htmlspecialchars($log['message'])?></td>
            <td><?=htmlspecialchars($log['created_at'])?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
