<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

// Khởi tạo biến để lưu thông báo
$success_msg = $_SESSION['success_msg'] ?? null;
$error_msg = $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Khởi tạo biến cho chế độ chỉnh sửa
$edit_mode = false;
$price_to_edit = null;

// Xử lý hiển thị giá vàng để sửa
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $price_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM gold_prices WHERE price_id = ?");
    $stmt->execute([$price_id]);
    $price_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($price_to_edit) {
        $edit_mode = true;
    }
}

// Xử lý xóa giá vàng
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $price_id = intval($_GET['delete']);
    
    try {
        // Kiểm tra xem giá vàng có tồn tại không
        $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE price_id = ?');
        $check_stmt->execute([$price_id]);
        
        if ($check_stmt->rowCount() === 0) {
            $_SESSION['error_msg'] = 'Không tìm thấy thông tin giá vàng để xóa';
        } else {
            // Thực hiện xóa giá vàng
            $delete_stmt = $conn->prepare('DELETE FROM gold_prices WHERE price_id = ?');
            if ($delete_stmt->execute([$price_id])) {
                $_SESSION['success_msg'] = 'Xóa giá vàng thành công';
            } else {
                $_SESSION['error_msg'] = 'Có lỗi xảy ra khi xóa giá vàng';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = 'Lỗi: ' . $e->getMessage();
    }
    
    // Chuyển hướng để tránh gửi lại request khi refresh trang
    header('Location: gold-prices.php');
    exit();
}

// Xử lý lưu giá vàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_price'])) {
    try {
        // Lấy dữ liệu từ form
        $price_id = isset($_POST['price_id']) && !empty($_POST['price_id']) ? intval($_POST['price_id']) : null;
        $gold_type = $_POST['gold_type'] ?? '';
        $buy_price = isset($_POST['buy_price']) ? str_replace(',', '', $_POST['buy_price']) : 0;
        $sell_price = isset($_POST['sell_price']) ? str_replace(',', '', $_POST['sell_price']) : 0;
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($gold_type)) {
            throw new Exception('Vui lòng chọn loại vàng');
        }
        
        if (!is_numeric($buy_price) || $buy_price <= 0) {
            throw new Exception('Giá mua phải là số dương');
        }
        
        if (!is_numeric($sell_price) || $sell_price <= 0) {
            throw new Exception('Giá bán phải là số dương');
        }
        
        // Chuyển giá trị sang kiểu số thập phân
        $buy_price = floatval($buy_price);
        $sell_price = floatval($sell_price);
        
        // Nếu có ID, cập nhật giá vàng hiện có, ngược lại thêm mới
        if ($price_id) {
            // Kiểm tra xem giá vàng có tồn tại không
            $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE price_id = ?');
            $check_stmt->execute([$price_id]);
            
            if ($check_stmt->rowCount() === 0) {
                throw new Exception('Không tìm thấy thông tin giá vàng để cập nhật');
            }
            
            // Cập nhật giá vàng
            $update_stmt = $conn->prepare('UPDATE gold_prices SET gold_type = ?, buy_price = ?, sell_price = ?, updated_at = NOW() WHERE price_id = ?');
            if ($update_stmt->execute([$gold_type, $buy_price, $sell_price, $price_id])) {
                $_SESSION['success_msg'] = 'Cập nhật giá vàng thành công';
            } else {
                throw new Exception('Có lỗi xảy ra khi cập nhật giá vàng');
            }
        } else {
            // Kiểm tra xem loại vàng đã tồn tại chưa
            $check_stmt = $conn->prepare('SELECT price_id FROM gold_prices WHERE gold_type = ?');
            $check_stmt->execute([$gold_type]);
            
            if ($check_stmt->rowCount() > 0) {
                throw new Exception('Loại vàng này đã tồn tại, vui lòng cập nhật thay vì thêm mới');
            }
            
            // Thêm mới giá vàng
            $insert_stmt = $conn->prepare('INSERT INTO gold_prices (gold_type, buy_price, sell_price) VALUES (?, ?, ?)');
            if ($insert_stmt->execute([$gold_type, $buy_price, $sell_price])) {
                $_SESSION['success_msg'] = 'Thêm giá vàng mới thành công';
            } else {
                throw new Exception('Có lỗi xảy ra khi thêm giá vàng mới');
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }
    
    // Chuyển hướng để tránh gửi lại form khi refresh
    header('Location: gold-prices.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gold Prices - Kim Phát Gold</title>
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
                    <h2>Manage Gold Prices</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goldPriceModal">
                        <i class="fas fa-plus"></i> Add New Gold Price
                    </button>
                </div>                <!-- Hiển thị thông báo thành công hoặc lỗi -->
                <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="data-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Loại Vàng</th>
                                <th>Giá Mua (VNĐ)</th>
                                <th>Giá Bán (VNĐ)</th>
                                <th>Cập Nhật Lúc</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM gold_prices ORDER BY updated_at DESC";
                            $stmt = $conn->query($query);

                            while ($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>{$row['gold_type']}</td>";
                                echo "<td>" . number_format($row['buy_price'], 0, ',', '.') . "đ</td>";
                                echo "<td>" . number_format($row['sell_price'], 0, ',', '.') . "đ</td>";
                                echo "<td>" . date('d/m/Y H:i', strtotime($row['updated_at'])) . "</td>";
                                echo "<td class='action-buttons'>
                                        <a href='gold-prices.php?edit={$row['price_id']}' class='btn btn-sm btn-primary'>
                                            <i class='fas fa-edit'></i> Sửa
                                        </a>
                                        <a href='gold-prices.php?delete={$row['price_id']}' class='btn btn-sm btn-danger' 
                                           onclick=\"return confirm('Bạn có chắc chắn muốn xóa giá vàng này?')\">
                                            <i class='fas fa-trash'></i> Xóa
                                        </a>
                                    </td>";
                                echo "</tr>";
                            }
                            
                            if ($stmt->rowCount() === 0) {
                                echo "<tr><td colspan='5' class='text-center'>Chưa có dữ liệu giá vàng</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Price History Chart -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Price History</h5>
                                <canvas id="priceHistoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Add/Edit Gold Price Modal -->
    <div class="modal fade" id="goldPriceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_mode ? 'Cập Nhật Giá Vàng' : 'Thêm Giá Vàng Mới'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="goldPriceForm" method="POST" action="gold-prices.php">
                        <input type="hidden" name="price_id" value="<?php echo $edit_mode ? $price_to_edit['price_id'] : ''; ?>">
                        <div class="mb-3">
                            <label class="form-label">Loại Vàng</label>
                            <select class="form-control" name="gold_type" required <?php echo $edit_mode ? 'disabled' : ''; ?>>
                                <option value="">-- Chọn loại vàng --</option>
                                <option value="SJC" <?php echo ($edit_mode && $price_to_edit['gold_type'] == 'SJC') ? 'selected' : ''; ?>>Vàng SJC</option>
                                <option value="24K" <?php echo ($edit_mode && $price_to_edit['gold_type'] == '24K') ? 'selected' : ''; ?>>Vàng 24K</option>
                                <option value="18K" <?php echo ($edit_mode && $price_to_edit['gold_type'] == '18K') ? 'selected' : ''; ?>>Vàng 18K</option>
                                <option value="14K" <?php echo ($edit_mode && $price_to_edit['gold_type'] == '14K') ? 'selected' : ''; ?>>Vàng 14K</option>
                                <option value="10K" <?php echo ($edit_mode && $price_to_edit['gold_type'] == '10K') ? 'selected' : ''; ?>>Vàng 10K</option>
                            </select>
                            <?php if ($edit_mode): ?>
                            <!-- Nếu đang trong chế độ chỉnh sửa, loại vàng được vô hiệu hóa nhưng cần gửi giá trị đúng -->
                            <input type="hidden" name="gold_type" value="<?php echo $price_to_edit['gold_type']; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá Mua (VNĐ)</label>
                            <input type="text" class="form-control" name="buy_price" value="<?php echo $edit_mode ? number_format($price_to_edit['buy_price'], 0, ',', '.') : ''; ?>" required placeholder="Nhập giá mua vàng">
                            <small class="form-text text-muted">Ví dụ: 1,380,000</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá Bán (VNĐ)</label>
                            <input type="text" class="form-control" name="sell_price" value="<?php echo $edit_mode ? number_format($price_to_edit['sell_price'], 0, ',', '.') : ''; ?>" required placeholder="Nhập giá bán vàng">
                            <small class="form-text text-muted">Ví dụ: 1,400,000</small>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" name="save_price" class="btn btn-primary">Lưu Giá Vàng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/gold-prices.js"></script>
</body>
</html>
