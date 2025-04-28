<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: pages/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kim Phát Gold - Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
              <div class="dashboard-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <!-- Today's Orders -->
                    <div class="stats-card">
                        <div class="stats-icon" style="color: #4CAF50; background: rgba(76, 175, 80, 0.1);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-number" id="todayOrders">0</div>
                        <div class="stats-title">Today's Orders</div>
                    </div>

                    <!-- Total Products -->
                    <div class="stats-card">
                        <div class="stats-icon" style="color: #2196F3; background: rgba(33, 150, 243, 0.1);">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="stats-number" id="totalProducts">0</div>
                        <div class="stats-title">Total Products</div>
                    </div>

                    <!-- Total Users -->
                    <div class="stats-card">
                        <div class="stats-icon" style="color: #FFC107; background: rgba(255, 193, 7, 0.1);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-number" id="totalUsers">0</div>
                        <div class="stats-title">Total Users</div>
                    </div>

                    <!-- Today's Revenue -->
                    <div class="stats-card">
                        <div class="stats-icon" style="color: #E91E63; background: rgba(233, 30, 99, 0.1);">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stats-number" id="todayRevenue">$0</div>
                        <div class="stats-title">Today's Revenue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/dashboard.js"></script>
</body>
</html>
