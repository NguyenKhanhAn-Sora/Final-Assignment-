<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Kiểm tra có id sản phẩm không
if (!isset($_GET['id'])) {
    header('Location: ../pages/products.php');
    exit();
}

$product_id = $_GET['id'];

// Nếu form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Cập nhật thông tin sản phẩm
        $stmt = $conn->prepare("
            UPDATE products SET 
                name = ?,
                slug = ?,
                category_id = ?,
                price = ?,
                sale_price = ?,
                description = ?,
                material_id = ?,
                weight = ?,
                stone_type = ?,
                stone_size = ?,
                stock_status = ?
            WHERE product_id = ?
        ");

        // Tạo slug từ tên sản phẩm
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));

        $stmt->execute([
            $_POST['name'],
            $slug,
            $_POST['category_id'],
            $_POST['price'],
            !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
            $_POST['description'],
            $_POST['material_id'],
            !empty($_POST['weight']) ? $_POST['weight'] : null,
            !empty($_POST['stone_type']) ? $_POST['stone_type'] : null,
            !empty($_POST['stone_size']) ? $_POST['stone_size'] : null,
            $_POST['stock_status'],
            $product_id
        ]);

        // Xử lý upload ảnh mới nếu có
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../../assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Lấy SKU của sản phẩm
            $stmt = $conn->prepare("SELECT sku FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $sku = $stmt->fetchColumn();

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $newFileName = $sku . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmp_name, $targetPath)) {
                        $imageUrl = '/Assignment/assets/images/products/' . $newFileName;
                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $stmt->execute([$product_id, $imageUrl]);
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Cập nhật sản phẩm thành công!";
        header('Location: ../pages/products.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    }
}

// Lấy thông tin sản phẩm để hiển thị trong form
try {
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

    // Lấy ảnh sản phẩm
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
    <title>Chỉnh sửa sản phẩm - <?php echo htmlspecialchars($product['name']); ?></title>
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
                        <h5 class="mb-0">Chỉnh sửa sản phẩm</h5>
                        <a href="../pages/products.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <select class="form-control" name="category_id" required>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
                                        $stmt->execute();
                                        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $category['category_id'] == $product['category_id'] ? 'selected' : '';
                                            echo "<option value='{$category['category_id']}' {$selected}>{$category['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá</label>
                                    <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá khuyến mãi</label>
                                    <input type="number" step="0.01" class="form-control" name="sale_price" value="<?php echo $product['sale_price']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chất liệu</label>
                                    <select class="form-control" name="material_id" required>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * FROM materials ORDER BY material_name");
                                        $stmt->execute();
                                        while ($material = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = $material['material_id'] == $product['material_id'] ? 'selected' : '';
                                            echo "<option value='{$material['material_id']}' {$selected}>{$material['material_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trọng lượng (chỉ)</label>
                                    <input type="number" step="0.01" class="form-control" name="weight" value="<?php echo $product['weight']; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại đá</label>
                                    <input type="text" class="form-control" name="stone_type" value="<?php echo htmlspecialchars($product['stone_type']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kích thước đá</label>
                                    <input type="text" class="form-control" name="stone_size" value="<?php echo htmlspecialchars($product['stone_size']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>                            <div class="mb-3">
                                <label class="form-label">Ảnh hiện tại</label>
                                <div class="row">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="position-relative">
                                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 100%; height: 150px; object-fit: cover;">
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm position-absolute" 
                                                    style="top: 5px; right: 5px;"
                                                    onclick="deleteImage(<?php echo $image['image_id']; ?>, this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <input type="hidden" name="existing_images[]" value="<?php echo $image['image_id']; ?>">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Thêm ảnh mới</label>
                                <input type="file" class="form-control" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                                <small class="text-muted">Có thể chọn nhiều ảnh. Định dạng: JPG, PNG, GIF, WebP</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-control" name="stock_status">
                                    <option value="in_stock" <?php echo $product['stock_status'] == 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                                    <option value="out_of_stock" <?php echo $product['stock_status'] == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                                    <option value="pre_order" <?php echo $product['stock_status'] == 'pre_order' ? 'selected' : ''; ?>>Đặt trước</option>
                                </select>
                            </div>

                            <div class="text-end">
                                <a href="../pages/products.php" class="btn btn-secondary">Hủy</a>
                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteImage(imageId, button) {
            if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
                fetch('/Assignment/admin/includes/delete_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `image_id=${imageId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Xóa phần tử ảnh khỏi giao diện
                        button.closest('.col-md-3').remove();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa ảnh');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa ảnh');
                });
            }
        }
    </script>
</body>
</html>
