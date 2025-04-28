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
    <title>Manage Products - Kim Phát Gold</title>    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="../js/products.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include '../includes/header.php'; ?>
              <div class="container-fluid py-4">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Products</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <!-- Product Filters -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Product Filters</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <!-- Search by name/sku -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Name, SKU or description" 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            
                            <!-- Filter by category -->
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php
                                    $cat_query = "SELECT * FROM categories ORDER BY name";
                                    $cat_stmt = $conn->prepare($cat_query);
                                    $cat_stmt->execute();
                                    while ($cat = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '';
                                        echo "<option value='" . $cat['category_id'] . "' $selected>" . htmlspecialchars($cat['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <!-- Filter by material -->
                            <div class="col-md-3">
                                <label for="material" class="form-label">Material</label>
                                <select class="form-select" id="material" name="material">
                                    <option value="">All Materials</option>
                                    <?php
                                    $mat_query = "SELECT * FROM materials ORDER BY material_name";
                                    $mat_stmt = $conn->prepare($mat_query);
                                    $mat_stmt->execute();
                                    while ($mat = $mat_stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['material']) && $_GET['material'] == $mat['material_id']) ? 'selected' : '';
                                        echo "<option value='" . $mat['material_id'] . "' $selected>" . htmlspecialchars($mat['material_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <!-- Filter by price range -->
                            <div class="col-md-2">
                                <label for="price_range" class="form-label">Price Range</label>
                                <select class="form-select" id="price_range" name="price_range">
                                    <option value="">Any Price</option>
                                    <option value="0-2000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "0-2000000") ? 'selected' : ''; ?>>< 2 million</option>
                                    <option value="2000000-5000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "2000000-5000000") ? 'selected' : ''; ?>>2 - 5 million</option>
                                    <option value="5000000-10000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "5000000-10000000") ? 'selected' : ''; ?>>5 - 10 million</option>
                                    <option value="10000000-20000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "10000000-20000000") ? 'selected' : ''; ?>>10 - 20 million</option>
                                    <option value="20000000-0" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "20000000-0") ? 'selected' : ''; ?>>> 20 million</option>
                                </select>
                            </div>
                            
                            <!-- Filter by stock status -->
                            <div class="col-md-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="">All Status</option>
                                    <option value="in_stock" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                                    <option value="out_of_stock" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="pre_order" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'pre_order') ? 'selected' : ''; ?>>Pre Order</option>
                                </select>
                            </div>
                            
                            <!-- Filter by sale products -->
                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="on_sale" name="on_sale" value="1" 
                                           <?php echo (isset($_GET['on_sale'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="on_sale">
                                        On Sale Products
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Filter actions -->
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="data-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <?php
                            // Khởi tạo các điều kiện lọc
                            $where_conditions = [];
                            $params = [];
                            
                            // Xử lý tìm kiếm theo từ khóa
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $search_term = '%' . $_GET['search'] . '%';
                                $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
                                $params[':search'] = $search_term;
                            }
                            
                            // Lọc theo danh mục
                            if (isset($_GET['category']) && !empty($_GET['category'])) {
                                $where_conditions[] = "p.category_id = :category_id";
                                $params[':category_id'] = $_GET['category'];
                            }
                            
                            // Lọc theo vật liệu
                            if (isset($_GET['material']) && !empty($_GET['material'])) {
                                $where_conditions[] = "p.material_id = :material_id";
                                $params[':material_id'] = $_GET['material'];
                            }
                            
                            // Lọc theo khoảng giá
                            if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
                                $price_range = explode('-', $_GET['price_range']);
                                if (count($price_range) == 2) {
                                    $min_price = (float)$price_range[0];
                                    $max_price = (float)$price_range[1];
                                    
                                    if ($min_price > 0 && $max_price > 0) {
                                        $where_conditions[] = "p.price BETWEEN :min_price AND :max_price";
                                        $params[':min_price'] = $min_price;
                                        $params[':max_price'] = $max_price;
                                    } elseif ($min_price > 0 && $max_price == 0) {
                                        $where_conditions[] = "p.price >= :min_price";
                                        $params[':min_price'] = $min_price;
                                    } elseif ($min_price == 0 && $max_price > 0) {
                                        $where_conditions[] = "p.price <= :max_price";
                                        $params[':max_price'] = $max_price;
                                    }
                                }
                            }
                            
                            // Lọc theo tình trạng kho
                            if (isset($_GET['stock_status']) && !empty($_GET['stock_status'])) {
                                $where_conditions[] = "p.stock_status = :stock_status";
                                $params[':stock_status'] = $_GET['stock_status'];
                            }
                            
                            // Lọc sản phẩm đang giảm giá
                            if (isset($_GET['on_sale'])) {
                                $where_conditions[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price";
                            }
                              // Cấu hình phân trang
                            $items_per_page = 8; // Số sản phẩm hiển thị trên mỗi trang
                            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $current_page = max(1, $current_page); // Đảm bảo trang hiện tại không nhỏ hơn 1
                            $offset = ($current_page - 1) * $items_per_page;
                            
                            // Tạo câu truy vấn đếm tổng số sản phẩm
                            $count_query = "SELECT COUNT(*) as total FROM products p";
                            if (!empty($where_conditions)) {
                                $count_query .= " WHERE " . implode(' AND ', $where_conditions);
                            }
                            
                            // Thực thi truy vấn đếm
                            $count_stmt = $conn->prepare($count_query);
                            foreach ($params as $key => $value) {
                                $count_stmt->bindValue($key, $value);
                            }
                            $count_stmt->execute();
                            $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            $total_pages = ceil($total_records / $items_per_page);
                            
                            // Tạo câu truy vấn với các điều kiện lọc
                            $query = "SELECT p.*, c.name as category_name, pi.image_url 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.category_id 
                                    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1";
                                    
                            // Thêm điều kiện WHERE nếu có
                            if (!empty($where_conditions)) {
                                $query .= " WHERE " . implode(' AND ', $where_conditions);
                            }
                              
                            // Thêm ORDER BY
                            $query .= " ORDER BY p.created_at DESC";
                            
                            // Thêm LIMIT cho phân trang
                            $query .= " LIMIT :offset, :limit";
                              
                            // Thực thi truy vấn với các tham số
                            $stmt = $conn->prepare($query);
                            foreach ($params as $key => $value) {
                                $stmt->bindValue($key, $value);
                            }
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                            $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['product_id']}</td>";
                                echo "<td><img src='{$row['image_url']}' width='50' height='50' alt='Product Image'></td>";
                                echo "<td>{$row['name']}</td>";
                                echo "<td>{$row['category_name']}</td>";
                                echo "<td>$" . number_format($row['price'], 2) . "</td>";
                                echo "<td><span class='badge bg-" . 
                                    ($row['stock_status'] == 'in_stock' ? 'success' : 
                                    ($row['stock_status'] == 'out_of_stock' ? 'danger' : 'warning')) . 
                                    "'>{$row['stock_status']}</span></td>";                                echo "<td class='action-buttons'>
                                        <a href='/Assignment/admin/includes/view_product.php?id={$row['product_id']}' 
                                           class='btn btn-sm btn-success'>
                                            <i class='fas fa-eye'></i>
                                        </a>
                                        <a href='/Assignment/admin/includes/edit_product.php?id={$row['product_id']}' 
                                           class='btn btn-sm btn-info'>
                                            <i class='fas fa-edit'></i>
                                        </a>                                        <a href='/Assignment/admin/includes/delete_product.php?id={$row['product_id']}' 
                                             class='btn btn-sm btn-danger'
                                             onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này?\");'>
                                            <i class='fas fa-trash'></i>
                                          </a>
                                    </td>";
                                echo "</tr>";
                            }                            ?>
                        </tbody>
                    </table>
                    
                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-container mt-4">
                        <nav aria-label="Điều hướng phân trang">
                            <ul class="pagination justify-content-center">
                                <?php
                                // Nút Previous
                                $prev_class = ($current_page <= 1) ? "disabled" : "";
                                $prev_page = max(1, $current_page - 1);
                                
                                // Tạo URL phân trang với các tham số lọc
                                $query_string = $_GET;
                                
                                echo '<li class="page-item ' . $prev_class . '">';
                                $query_string['page'] = $prev_page;
                                echo '<a class="page-link" href="?' . http_build_query($query_string) . '">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>';
                                echo '</li>';
                                
                                // Các số trang
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                // Hiển thị trang đầu nếu không bắt đầu từ trang 1
                                if ($start_page > 1) {
                                    $query_string['page'] = 1;
                                    echo '<li class="page-item">';
                                    echo '<a class="page-link" href="?' . http_build_query($query_string) . '">1</a>';
                                    echo '</li>';
                                    
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                // Hiển thị các số trang trong khoảng
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    $active_class = ($i == $current_page) ? "active" : "";
                                    $query_string['page'] = $i;
                                    echo '<li class="page-item ' . $active_class . '">';
                                    echo '<a class="page-link" href="?' . http_build_query($query_string) . '">' . $i . '</a>';
                                    echo '</li>';
                                }
                                
                                // Hiển thị trang cuối nếu không kết thúc ở trang cuối
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    
                                    $query_string['page'] = $total_pages;
                                    echo '<li class="page-item">';
                                    echo '<a class="page-link" href="?' . http_build_query($query_string) . '">' . $total_pages . '</a>';
                                    echo '</li>';
                                }
                                
                                // Nút Next
                                $next_class = ($current_page >= $total_pages) ? "disabled" : "";
                                $next_page = min($total_pages, $current_page + 1);
                                
                                echo '<li class="page-item ' . $next_class . '">';
                                $query_string['page'] = $next_page;
                                echo '<a class="page-link" href="?' . http_build_query($query_string) . '">
                                    Tiếp <i class="fas fa-chevron-right"></i>
                                </a>';
                                echo '</li>';
                                ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>                <div class="modal-body">
                    <form id="addProductForm" method="POST" action="/Assignment/admin/includes/save_product.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category_id" required>                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM categories");
                                    $stmt->execute();
                                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control" name="sale_price">
                            </div>
                        </div>
                        <div class="row">                            <div class="col-md-6 mb-3">
                                <label class="form-label">Material</label>
                                <select class="form-control" name="material_id" required>
                                    <option value="">Select Material</option>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM materials ORDER BY material_name");
                                    $stmt->execute();
                                    while ($material = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$material['material_id']}'>{$material['material_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Weight (chỉ)</label>
                                <input type="number" step="0.01" class="form-control" name="weight">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stone Type</label>
                                <input type="text" class="form-control" name="stone_type">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stone Size</label>
                                <input type="text" class="form-control" name="stone_size">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>                        <div class="mb-3">
                            <label class="form-label">Product Images</label>
                            <input type="file" class="form-control" id="productImages" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="text-muted">Allowed types: JPG, PNG, GIF, WebP. Max size: 5MB per file. You can select multiple images.</small>
                            <div id="imagePreviewContainer" class="mt-3 d-flex flex-wrap gap-3" style="min-height: 150px; border: 2px dashed #ddd; padding: 15px; border-radius: 8px;">
                                <div class="text-center w-100 text-muted fs-6" id="dropText">
                                    <i class="fas fa-cloud-upload-alt fs-2 mb-2"></i><br>
                                    Drag & drop images here or click to select
                                </div>
                                <div id="previewList" class="d-flex flex-wrap gap-3 w-100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Status</label>
                            <select class="form-control" name="stock_status">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="pre_order">Pre Order</option>
                            </select>
                        </div>
                    </form>
                </div>                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addProductForm">Save Product</button>
                </div>
            </div>
        </div>
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/products.js"></script>

    <!-- View Product Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="productImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <!-- Images will be inserted here -->
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 id="viewProductName"></h4>
                            <p><strong>Category:</strong> <span id="viewProductCategory"></span></p>
                            <p><strong>Price:</strong> $<span id="viewProductPrice"></span></p>
                            <p><strong>Sale Price:</strong> $<span id="viewProductSalePrice"></span></p>
                            <p><strong>Material:</strong> <span id="viewProductMaterial"></span></p>
                            <p><strong>Weight:</strong> <span id="viewProductWeight"></span> chỉ</p>
                            <p><strong>Stone Type:</strong> <span id="viewProductStoneType"></span></p>
                            <p><strong>Stone Size:</strong> <span id="viewProductStoneSize"></span></p>
                            <p><strong>Stock Status:</strong> <span id="viewProductStockStatus"></span></p>
                            <p><strong>Description:</strong></p>
                            <p id="viewProductDescription"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" method="POST" action="/Assignment/admin/includes/edit_product.php" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" id="editProductId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" id="editName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category_id" id="editCategory" required>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM categories");
                                    $stmt->execute();
                                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" name="price" id="editPrice" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" step="0.01" class="form-control" name="sale_price" id="editSalePrice">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Material</label>
                                <select class="form-control" name="material_id" id="editMaterial" required>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM materials ORDER BY material_name");
                                    $stmt->execute();
                                    while ($material = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$material['material_id']}'>{$material['material_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Weight (chỉ)</label>
                                <input type="number" step="0.01" class="form-control" name="weight" id="editWeight">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stone Type</label>
                                <input type="text" class="form-control" name="stone_type" id="editStoneType">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stone Size</label>
                                <input type="text" class="form-control" name="stone_size" id="editStoneSize">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Images</label>
                            <div id="currentImages" class="d-flex flex-wrap gap-2 mb-3">
                                <!-- Current images will be displayed here -->
                            </div>
                            <label class="form-label">Add New Images</label>
                            <input type="file" class="form-control" id="editProductImages" name="new_images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                            <div id="editImagePreviewContainer" class="mt-3 d-flex flex-wrap gap-2">
                                <!-- New image previews will be displayed here -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Status</label>
                            <select class="form-control" name="stock_status" id="editStockStatus">
                                <option value="in_stock">In Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                                <option value="pre_order">Pre Order</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editProductForm">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
