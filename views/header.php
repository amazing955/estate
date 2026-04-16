<?php
// views/header.php
require_once __DIR__ . '/../controllers/AuthController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estate Management System</title>
    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/estate/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <?php if (AuthController::check()): ?>
                <?php
                // prepare notification badge for logged-in user
                require_once __DIR__ . '/../controllers/NotificationController.php';
                $nc = new NotificationController();
                $unreadCount = $nc->unreadCount($_SESSION['user_id']);
                ?>
                <?php if (!empty($_SESSION['profile_pic'])): ?>
                    <li class="nav-item">
                        <img src="/estate/<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" style="width:30px;height:30px;border-radius:50%;margin-right:5px;" />
                    </li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'client'): ?>
                    <li class="nav-item"><a class="nav-link" href="/estate/views/client/dashboard.php">My Dashboard<?php if($unreadCount>0): ?> <span class="badge bg-danger"><?=$unreadCount?></span><?php endif; ?></a></li>
                <?php else: ?>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                          <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Admin<?php if($unreadCount>0): ?> <span class="badge bg-danger"><?=$unreadCount?></span><?php endif; ?>
                          </a>
                          <div class="dropdown-menu" aria-labelledby="adminMenu">
                            <a class="dropdown-item" href="/estate/views/admin/dashboard.php">Dashboard</a>
                            <a class="dropdown-item" href="/estate/views/admin/users.php">Users</a>
                            <a class="dropdown-item" href="/estate/views/admin/inquiries.php">Inquiries</a>
                            <a class="dropdown-item" href="/estate/views/admin/adverts.php">Adverts</a>
                          </div>
                        </li>
                    <?php else: ?>
                        <!-- other roles (owner/broker) might also benefit from badge -->
                        <li class="nav-item"><a class="nav-link" href="/estate/views/<?=htmlspecialchars($_SESSION['role'])?>/dashboard.php"><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?> Dashboard<?php if($unreadCount>0): ?> <span class="badge bg-danger"><?=$unreadCount?></span><?php endif; ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/estate/controllers/index.php?action=logout">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                </li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="/estate/views/login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="/estate/views/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<div class="container mt-4">
