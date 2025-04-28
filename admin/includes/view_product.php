<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ../pages/products.php');
    exit();
}

$product_id = $_GET['id'];

try {
    // Lấy thông tin sản phẩm và các thông tin liên quan
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, m.material_name
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN materials m ON p.material_id = m.material_id
        WHERE p.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error_message'] = "Sản phẩm không tồn tại";
        header('Location: ../pages/products.php');
        exit();
    }

    // Lấy tất cả ảnh của sản phẩm
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    header('Location: ../pages/products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chi tiết sản phẩm</h5>
                        <a href="../pages/products.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($images as $index => $image): ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                                     class="d-block w-100" 
                                                     alt="Product Image"
                                                     style="height: 400px; object-fit: cover;">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($images) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><strong>Mã SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></p>
                                <p><strong>Danh mục:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p><strong>Giá:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                <?php if ($product['sale_price']): ?>
                                    <p><strong>Giá khuyến mãi:</strong> $<?php echo number_format($product['sale_price'], 2); ?></p>
                                <?php endif; ?>
                                <p><strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['material_name']); ?></p>
                                <?php if ($product['weight']): ?>
                                    <p><strong>Trọng lượng:</strong> <?php echo htmlspecialchars($product['weight']); ?> chỉ</p>
                                <?php endif; ?>
                                <?php if ($product['stone_type']): ?>
                                    <p><strong>Loại đá:</strong> <?php echo htmlspecialchars($product['stone_type']); ?></p>
                                <?php endif; ?>
                                <?php if ($product['stone_size']): ?>
                                    <p><strong>Kích thước đá:</strong> <?php echo htmlspecialchars($product['stone_size']); ?></p>
                                <?php endif; ?>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $product['stock_status'] == 'in_stock' ? 'success' : 
                                            ($product['stock_status'] == 'out_of_stock' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars($product['stock_status']); ?>
                                    </span>
                                </p>
                                <div class="mt-4">
                                    <h5>Mô tả sản phẩm:</h5>
                                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
