<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Kim Phát Gold</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/header.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Users</h2>
                    <div class="search-section">
                        <input type="text" class="form-control" id="userSearch" placeholder="Search users...">
                    </div>
                </div>

                <div class="data-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php
                            $query = "SELECT * FROM users ORDER BY created_at DESC";
                            $stmt = $conn->query($query);

                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>{$row['user_id']}</td>";
                                echo "<td>{$row['full_name']}</td>";
                                echo "<td>{$row['email']}</td>";
                                echo "<td>{$row['phone']}</td>";
                                echo "<td>" . (strlen($row['address']) > 30 ? substr($row['address'], 0, 30) . '...' : $row['address']) . "</td>";
                                echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                echo "<td class='action-buttons'>
                                        <button class='btn btn-sm btn-info' onclick='viewUser({$row['user_id']})'>
                                            <i class='fas fa-eye'></i>
                                        </button>
                                        <button class='btn btn-sm btn-danger' onclick='deleteUser({$row['user_id']})'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="user-info mb-4">
                        <h6>Personal Information</h6>
                        <div id="personalInfo"></div>
                    </div>
                    <div class="order-history">
                        <h6>Order History</h6>
                        <div id="orderHistory" class="table-responsive"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/users.js"></script>
</body>
</html>
