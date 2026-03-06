<?php
// views/timeout.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
session_unset();
session_destroy();
?>

<?php include __DIR__ . '/header.php'; ?>

<style>
.timeout-wrapper{
    min-height:70vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.timeout-card{
    border:none;
    border-radius:12px;
    overflow:hidden;
}

.timeout-header{
    background:linear-gradient(135deg,#ffcc00,#ff9800);
    padding:25px;
    text-align:center;
    color:white;
}

.timeout-icon{
    font-size:60px;
}

.timeout-body{
    padding:35px;
    text-align:center;
}

.login-btn{
    padding:12px 30px;
    font-size:18px;
    border-radius:8px;
}
</style>

<div class="container timeout-wrapper">
    <div class="col-md-6">

        <div class="card shadow-lg timeout-card">

            <div class="timeout-header">
                <i class="fas fa-clock timeout-icon"></i>
                <h3 class="mt-3">Session Expired</h3>
            </div>

            <div class="timeout-body">

                <p class="lead">
                    Your session has expired due to inactivity.
                </p>

                <p class="text-muted mb-4">
                    For security reasons, you have been automatically logged out of the system.
                    Please login again to continue using the Estate Management System.
                </p>

                <a href="/estate/views/login.php" class="btn btn-primary login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login Again
                </a>

            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>