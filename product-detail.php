<?php
session_start();
require_once 'includes/db.php';

// Kiểm tra xem có tham số slug không
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: index.php');
    exit();
}

$slug = $_GET['slug'];

// Lấy thông tin sản phẩm từ database
$product_query = "
    SELECT p.*, m.material_name, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN materials m ON p.material_id = m.material_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.slug = :slug
";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bindParam(':slug', $slug);
$product_stmt->execute();

// Kiểm tra nếu không tìm thấy sản phẩm
if ($product_stmt->rowCount() === 0) {
    header('Location: index.php');
    exit();
}

// Lấy thông tin sản phẩm
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách hình ảnh của sản phẩm
$images_query = "
    SELECT * FROM product_images 
    WHERE product_id = :product_id 
    ORDER BY is_primary DESC
";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bindParam(':product_id', $product['product_id']);
$images_stmt->execute();
$product_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách kích thước của sản phẩm nếu có
$sizes_query = "
    SELECT * FROM product_sizes 
    WHERE product_id = :product_id 
    ORDER BY size_value
";
$sizes_stmt = $conn->prepare($sizes_query);
$sizes_stmt->bindParam(':product_id', $product['product_id']);
$sizes_stmt->execute();
$product_sizes = $sizes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy hình ảnh chính hoặc hình ảnh đầu tiên
$main_image = '';
if (count($product_images) > 0) {
    // Tìm hình ảnh chính
    foreach ($product_images as $img) {
        if ($img['is_primary'] == 1) {
            $main_image = $img['image_url'];
            break;
        }
    }
    // Nếu không có hình ảnh chính, lấy hình ảnh đầu tiên
    if (empty($main_image)) {
        $main_image = $product_images[0]['image_url'];
    }
} else {
    // Nếu không có hình ảnh nào, sử dụng ảnh mặc định
    $main_image = 'assets/images/no-image.png';
}

// Định dạng giá và giá khuyến mãi
$price_display = number_format($product['price'], 0, ',', '.') . '₫';
$has_discount = false;
$discount_percent = 0;

if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']) {
    $has_discount = true;
    $discount_percent = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
    $sale_price_display = number_format($product['sale_price'], 0, ',', '.') . '₫';
}

// Thiết lập tiêu đề trang
$page_title = htmlspecialchars($product['name']) . ' - Kim Phát Gold';

// Hiển thị trạng thái tồn kho
$stock_status_display = '';
switch ($product['stock_status']) {
    case 'in_stock':
        $stock_status_display = 'Còn hàng';
        break;
    case 'out_of_stock':
        $stock_status_display = 'Hết hàng';
        break;
    case 'pre_order':
        $stock_status_display = 'Đặt trước';
        break;
    default:
        $stock_status_display = 'Liên hệ';
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?></title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link rel="stylesheet" href="./assets/css/style.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="./assets/css/auth.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="./assets/css/product-detail.css?v=<?php echo time(); ?>" />
  </head>
  <body>
    <!-- Header -->
    <header class="header">
      <div class="header-top">
        <div class="container">
          <div class="contact-info">
            <span><i class="fas fa-phone"></i> Hotline: 1800 1168</span>
            <span
              ><i class="fas fa-envelope"></i> Email:
              contact@kimphat.com.vn</span
            >
          </div>
        </div>
      </div>
      <div class="header-main">
        <div class="container">
          <div class="logo">
            <a href="index.html">
              <img
                src="https://theme.hstatic.net/200000759101/1001102004/14/logo.png?v=435"
                alt="Kim Phát Gold"
              />
            </a>
          </div>
          <nav class="main-nav">
            <ul>
              <li><a href="index.html">Trang chủ</a></li>
              <li><a href="#">Sản phẩm</a></li>
              <li><a href="#">Bảng giá</a></li>
              <li><a href="#">Tin tức</a></li>
              <li><a href="#">Liên hệ</a></li>
            </ul>
          </nav>
          <div class="search-cart">
            <div class="search">
              <i class="fas fa-search"></i>
            </div>
            <div class="cart">
              <i class="fas fa-shopping-cart"></i>
              <span class="cart-count">0</span>
            </div>
            <div class="user">
              <i class="fa-solid fa-user"></i>
              <div class="auth-dropdown">
                <a href="#" id="loginBtn">Đăng nhập</a>
                <a href="#" id="signupBtn">Đăng ký</a>
                <div class="logged-in-menu" style="display: none">
                  <a href="#" id="profileBtn">Tài khoản của tôi</a>
                  <hr />
                  <a href="#" id="logoutBtn">Đăng xuất</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <div class="container">
        <ul>
          <li><a href="index.php">Trang chủ</a></li>
          <?php if (!empty($product['category_slug']) && !empty($product['category_name'])): ?>
          <li><a href="products.php?category=<?php echo $product['category_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
          <?php endif; ?>
          <li><?php echo htmlspecialchars($product['name']); ?></li>
        </ul>
      </div>
    </div>

    <!-- Product Detail Section -->
    <section class="product-detail">
      <div class="container">
        <div class="product-detail-grid">
          <!-- Product Images -->
          <div class="product-images">
            <div class="main-image">
              <img
                src="<?php echo htmlspecialchars($main_image); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                id="mainProductImage"
              />
              <?php if ($has_discount): ?>
              <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
              <?php endif; ?>
            </div>            <div class="thumbnail-images">
              <?php 
              // Hiển thị tối đa 4 hình ảnh thumbnail
              $count = 0;
              foreach ($product_images as $index => $image): 
                if ($count >= 4) break;
                // Sử dụng data attributes thay vì onclick trực tiếp để tránh vấn đề với dấu ngoặc kép
              ?>
              <img
                src="<?php echo htmlspecialchars($image['image_url']); ?>"
                alt="<?php echo htmlspecialchars($product['name'] . ' - ' . ($index + 1)); ?>"
                class="thumbnail-image <?php echo ($main_image === $image['image_url']) ? 'active' : ''; ?>"
                data-image-url="<?php echo htmlspecialchars($image['image_url']); ?>"
              />
              <?php 
                $count++;
              endforeach; 
              
              // Nếu không có hình ảnh nào, hiển thị hình ảnh mặc định
              if (count($product_images) === 0): 
              ?>
              <img
                src="assets/images/no-image.png"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                class="thumbnail-image active"
              />
              <?php endif; ?>
            </div>
          </div>

          <!-- Product Info -->
          <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-code">Mã sản phẩm: <?php echo htmlspecialchars($product['sku']); ?></div>
            
            <div class="product-price">
              <?php if ($has_discount): ?>
              <span class="current-price"><?php echo $sale_price_display; ?></span>
              <span class="old-price"><?php echo $price_display; ?></span>
              <?php else: ?>
              <span class="current-price"><?php echo $price_display; ?></span>
              <?php endif; ?>
            </div>
            
            <div class="product-status">
              <span class="status-label">Tình trạng:</span>
              <span class="status-value <?php echo strtolower($product['stock_status']); ?>"><?php echo $stock_status_display; ?></span>
            </div>

            <?php if (!empty($product_sizes)): ?>
            <div class="product-options">
              <div class="option-group">
                <label>Kích thước:</label>
                <div class="size-options">
                  <?php foreach ($product_sizes as $size): ?>
                  <button class="size-option" data-size="<?php echo htmlspecialchars($size['size_value']); ?>" 
                          <?php echo ($size['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                    <?php echo htmlspecialchars($size['size_value']); ?>
                  </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <div class="product-specs">
              <?php if (!empty($product['material_name'])): ?>
              <div class="spec-item">
                <span class="spec-label">Chất liệu:</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['material_name']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($product['weight'])): ?>
              <div class="spec-item">
                <span class="spec-label">Khối lượng:</span>
                <span class="spec-value"><?php echo $product['weight']; ?> chỉ</span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($product['stone_type'])): ?>
              <div class="spec-item">
                <span class="spec-label">Loại đá:</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['stone_type']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($product['stone_size'])): ?>
              <div class="spec-item">
                <span class="spec-label">Kích thước đá:</span>
                <span class="spec-value"><?php echo htmlspecialchars($product['stone_size']); ?></span>
              </div>
              <?php endif; ?>
            </div>

            <div class="quantity-selector">
              <label>Số lượng:</label>
              <div class="quantity-controls">
                <button type="button" class="quantity-btn minus" onclick="decreaseQuantity()">-</button>
                <input type="number" id="productQuantity" value="1" min="1" />
                <button type="button" class="quantity-btn plus" onclick="increaseQuantity()">+</button>
              </div>
            </div>

            <div class="product-actions">
              <button class="btn btn-primary btn-add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                <i class="fas fa-shopping-cart"></i>
                Thêm vào giỏ hàng
              </button>
              <button class="btn btn-secondary btn-buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">Mua ngay</button>
            </div>        <!-- Product Features -->
            <div class="product-features">
              <div class="feature">
                <i class="fas fa-gem"></i>
                <span><?php echo !empty($product['material_name']) ? htmlspecialchars($product['material_name']) : 'Vàng cao cấp'; ?></span>
              </div>
              <div class="feature">
                <i class="fas fa-certificate"></i>
                <span>Bảo hành trọn đời</span>
              </div>
              <div class="feature">
                <i class="fas fa-exchange-alt"></i>
                <span>Đổi trả trong 48h</span>
              </div>
              <div class="feature">
                <i class="fas fa-truck"></i>
                <span>Giao hàng toàn quốc</span>
              </div>
            </div>
          </div>
        </div>        <!-- Product Description -->
        <div class="product-description">
          <h2 class="section-title">Chi tiết sản phẩm</h2>
          <div class="description-content">
            <!-- Bảng thông số kỹ thuật -->
            <table class="specs-table">
              <tr>
                <td><i class="fas fa-tag"></i> Mã sản phẩm</td>
                <td><?php echo htmlspecialchars($product['sku']); ?></td>
              </tr>
              
              <?php if (!empty($product['material_name'])): ?>
              <tr>
                <td><i class="fas fa-gem"></i> Chất liệu</td>
                <td><?php echo htmlspecialchars($product['material_name']); ?></td>
              </tr>
              <?php endif; ?>
              
              <?php if (!empty($product['weight'])): ?>
              <tr>
                <td><i class="fas fa-weight-hanging"></i> Trọng lượng</td>
                <td><?php echo $product['weight']; ?> chỉ</td>
              </tr>
              <?php endif; ?>
              
              <?php if (!empty($product['stone_type'])): ?>
              <tr>
                <td><i class="fas fa-certificate"></i> Loại đá</td>
                <td><?php echo htmlspecialchars($product['stone_type']); ?></td>
              </tr>
              <?php endif; ?>
              
              <?php if (!empty($product['stone_size'])): ?>
              <tr>
                <td><i class="fas fa-ruler"></i> Kích thước đá</td>
                <td><?php echo htmlspecialchars($product['stone_size']); ?></td>
              </tr>
              <?php endif; ?>
              
              <?php if (!empty($product_sizes)): ?>
              <tr>
                <td><i class="fas fa-ring"></i> Kích thước có sẵn</td>
                <td>
                  <?php 
                  $size_values = array_map(function($size) {
                      return $size['size_value'];
                  }, $product_sizes);
                  echo implode(' - ', $size_values);
                  ?>
                </td>
              </tr>
              <?php endif; ?>
              
              <tr>
                <td><i class="fas fa-th-list"></i> Danh mục</td>
                <td>
                  <?php if (!empty($product['category_slug']) && !empty($product['category_name'])): ?>
                  <a href="products.php?category=<?php echo $product['category_slug']; ?>" class="category-link">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                  </a>
                  <?php else: ?>
                    Chưa phân loại
                  <?php endif; ?>
                </td>
              </tr>
              
              <?php if ($product['stock_status'] !== 'out_of_stock'): ?>
              <tr>
                <td><i class="fas fa-truck-loading"></i> Thời gian giao hàng</td>
                <td>2-3 ngày làm việc</td>
              </tr>
              <?php endif; ?>
            </table>
            
            <!-- Mô tả sản phẩm -->
            <div class="description-text">
              <h3>Mô tả sản phẩm</h3>
              
              <?php if (!empty($product['description'])): ?>
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
              <?php else: ?>
              <p>
                <strong><?php echo htmlspecialchars($product['name']); ?></strong> là một trong những sản phẩm tinh tế và sang
                trọng của Kim Phát Gold. 
                <?php if (!empty($product['material_name'])): ?>
                Được chế tác từ <strong><?php echo htmlspecialchars($product['material_name']); ?></strong> cao cấp
                <?php endif; ?>
                
                <?php if (!empty($product['stone_type'])): ?>
                , kết hợp với <strong><?php echo htmlspecialchars($product['stone_type']); ?></strong>
                <?php if (!empty($product['stone_size'])): ?>
                <strong><?php echo htmlspecialchars($product['stone_size']); ?></strong>
                <?php endif; ?>
                tạo nên vẻ đẹp lộng lẫy và quý phái.
                <?php endif; ?>
              </p>
              
              <p>
                Thiết kế tinh tế với những đường nét mềm mại, thanh lịch, phù
                hợp với nhiều phong cách thời trang khác nhau. Đây là món trang
                sức hoàn hảo cho các dịp đặc biệt hoặc làm quà tặng cho người
                thân yêu.
              </p>
              
              <div class="product-highlights">
                <h4>Đặc điểm nổi bật</h4>
                <ul>
                  <?php if (!empty($product['material_name'])): ?>
                  <li>Chất liệu: <?php echo htmlspecialchars($product['material_name']); ?> cao cấp, bền đẹp theo thời gian</li>
                  <?php endif; ?>
                  
                  <?php if (!empty($product['stone_type'])): ?>
                  <li>Đá: <?php echo htmlspecialchars($product['stone_type']); ?> chất lượng, kiểm định nghiêm ngặt</li>
                  <?php endif; ?>
                  
                  <?php if (!empty($product['weight'])): ?>
                  <li>Trọng lượng: <?php echo $product['weight']; ?> chỉ, phù hợp với nhu cầu sử dụng hàng ngày</li>
                  <?php endif; ?>
                  
                  <li>Thiết kế độc đáo, sáng tạo, mang đến vẻ đẹp sang trọng, quý phái</li>
                  <li>Chế tác thủ công tỉ mỉ bởi những người thợ kim hoàn lành nghề</li>
                </ul>
              </div>
              <?php endif; ?>
              
              <div class="care-instructions">
                <h4>Hướng dẫn bảo quản</h4>
                <ul>
                  <li>Tránh tiếp xúc với hóa chất mạnh, nước có clo, mỹ phẩm</li>
                  <li>Làm sạch nhẹ nhàng bằng khăn mềm và nước ấm hoặc dung dịch tẩy rửa trang sức chuyên dụng</li>
                  <li>Bảo quản trong hộp riêng, tránh va đập mạnh</li>
                  <li>Nên tháo trang sức khi tắm, bơi, tập thể thao hoặc làm việc nặng</li>
                </ul>
              </div>
            </div>
          </div>
        </div><!-- Related Products -->
        <div class="related-products">
          <h2 class="section-title">Sản phẩm liên quan</h2>
          <div class="product-grid">
            <?php
            // Lấy các sản phẩm liên quan cùng danh mục, không bao gồm sản phẩm hiện tại
            $related_query = "
                SELECT p.*, m.material_name, i.image_url 
                FROM products p
                LEFT JOIN materials m ON p.material_id = m.material_id
                LEFT JOIN product_images i ON p.product_id = i.product_id AND i.is_primary = 1
                WHERE p.category_id = :category_id 
                  AND p.product_id != :current_product_id
                  AND p.stock_status = 'in_stock'
                ORDER BY 
                  CASE WHEN p.sale_price IS NOT NULL THEN 0 ELSE 1 END,
                  p.created_at DESC
                LIMIT 4
            ";
            
            $related_stmt = $conn->prepare($related_query);
            $related_stmt->bindParam(':category_id', $product['category_id']);
            $related_stmt->bindParam(':current_product_id', $product['product_id']);
            $related_stmt->execute();
            
            if ($related_stmt->rowCount() > 0):
                while ($related_product = $related_stmt->fetch()):
                    // Xử lý hình ảnh
                    if (empty($related_product['image_url'])) {
                        $img_query = "SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1";
                        $img_stmt = $conn->prepare($img_query);
                        $img_stmt->execute([$related_product['product_id']]);
                        $img_result = $img_stmt->fetch();
                        $related_image = $img_result ? $img_result['image_url'] : 'assets/images/no-image.png';
                    } else {
                        $related_image = $related_product['image_url'];
                    }
                    
                    // Xử lý giá và giảm giá
                    $related_price_display = number_format($related_product['price'], 0, ',', '.') . '₫';
                    $related_has_discount = false;
                    
                    if (!empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['price']) {
                        $related_has_discount = true;
                        $related_discount_percent = round((($related_product['price'] - $related_product['sale_price']) / $related_product['price']) * 100);
                        $related_sale_price_display = number_format($related_product['sale_price'], 0, ',', '.') . '₫';
                    }
                    
                    // Tạo URL chi tiết sản phẩm
                    $related_product_url = "product-detail.php?slug=" . $related_product['slug'];
            ?>
            <div class="product-card">
              <div class="product-image">
                <img
                  src="<?php echo htmlspecialchars($related_image); ?>"
                  alt="<?php echo htmlspecialchars($related_product['name']); ?>"
                />
                <?php if ($related_has_discount): ?>
                <div class="discount-badge">-<?php echo $related_discount_percent; ?>%</div>
                <?php endif; ?>
                <div class="product-overlay">
                  <a href="<?php echo $related_product_url; ?>" class="btn-view">Xem chi tiết</a>
                </div>
              </div>
              <div class="product-info">
                <?php if (!empty($related_product['material_name'])): ?>
                <div class="product-material"><?php echo htmlspecialchars($related_product['material_name']); ?></div>
                <?php endif; ?>
                <h3><a href="<?php echo $related_product_url; ?>" class="product-name"><?php echo htmlspecialchars($related_product['name']); ?></a></h3>
                <p class="price">
                  <?php if ($related_has_discount): ?>
                  <span class="old-price"><?php echo $related_price_display; ?></span>
                  <?php echo $related_sale_price_display; ?>
                  <?php else: ?>
                  <?php echo $related_price_display; ?>
                  <?php endif; ?>
                </p>
              </div>
            </div>
            <?php 
                endwhile;
            else:
                // Nếu không có sản phẩm liên quan cùng danh mục, hiển thị các sản phẩm khác
                $other_query = "
                    SELECT p.*, m.material_name, i.image_url 
                    FROM products p
                    LEFT JOIN materials m ON p.material_id = m.material_id
                    LEFT JOIN product_images i ON p.product_id = i.product_id AND i.is_primary = 1
                    WHERE p.product_id != :current_product_id
                      AND p.stock_status = 'in_stock'
                    ORDER BY 
                      CASE WHEN p.sale_price IS NOT NULL THEN 0 ELSE 1 END,
                      p.created_at DESC
                    LIMIT 4
                ";
                
                $other_stmt = $conn->prepare($other_query);
                $other_stmt->bindParam(':current_product_id', $product['product_id']);
                $other_stmt->execute();
                
                while ($other_product = $other_stmt->fetch()):
                    // Xử lý thông tin sản phẩm tương tự như trên
                    if (empty($other_product['image_url'])) {
                        $img_query = "SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1";
                        $img_stmt = $conn->prepare($img_query);
                        $img_stmt->execute([$other_product['product_id']]);
                        $img_result = $img_stmt->fetch();
                        $other_image = $img_result ? $img_result['image_url'] : 'assets/images/no-image.png';
                    } else {
                        $other_image = $other_product['image_url'];
                    }
                    
                    $other_price_display = number_format($other_product['price'], 0, ',', '.') . '₫';
                    $other_has_discount = false;
                    
                    if (!empty($other_product['sale_price']) && $other_product['sale_price'] < $other_product['price']) {
                        $other_has_discount = true;
                        $other_discount_percent = round((($other_product['price'] - $other_product['sale_price']) / $other_product['price']) * 100);
                        $other_sale_price_display = number_format($other_product['sale_price'], 0, ',', '.') . '₫';
                    }
                    
                    $other_product_url = "product-detail.php?slug=" . $other_product['slug'];
            ?>
            <div class="product-card">
              <div class="product-image">
                <img
                  src="<?php echo htmlspecialchars($other_image); ?>"
                  alt="<?php echo htmlspecialchars($other_product['name']); ?>"
                />
                <?php if ($other_has_discount): ?>
                <div class="discount-badge">-<?php echo $other_discount_percent; ?>%</div>
                <?php endif; ?>
                <div class="product-overlay">
                  <a href="<?php echo $other_product_url; ?>" class="btn-view">Xem chi tiết</a>
                </div>
              </div>
              <div class="product-info">
                <?php if (!empty($other_product['material_name'])): ?>
                <div class="product-material"><?php echo htmlspecialchars($other_product['material_name']); ?></div>
                <?php endif; ?>
                <h3><a href="<?php echo $other_product_url; ?>" class="product-name"><?php echo htmlspecialchars($other_product['name']); ?></a></h3>
                <p class="price">
                  <?php if ($other_has_discount): ?>
                  <span class="old-price"><?php echo $other_price_display; ?></span>
                  <?php echo $other_sale_price_display; ?>
                  <?php else: ?>
                  <?php echo $other_price_display; ?>
                  <?php endif; ?>
                </p>
              </div>
            </div>
            <?php 
                endwhile;
            endif;
            ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-col">
            <h3>Về Kim Phát Gold</h3>
            <p>
              Kim Phát Gold - Thương hiệu trang sức uy tín với hơn 20 năm kinh
              nghiệm trong ngành kim hoàn.
            </p>
          </div>
          <div class="footer-col">
            <h3>Liên kết nhanh</h3>
            <ul>
              <li><a href="#">Trang chủ</a></li>
              <li><a href="#">Sản phẩm</a></li>
              <li><a href="#">Bảng giá</a></li>
              <li><a href="#">Tin tức</a></li>
              <li><a href="#">Liên hệ</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>Thông tin liên hệ</h3>
            <ul class="contact-info">
              <li>
                <i class="fas fa-map-marker-alt"></i> 123 Nguyễn Huệ, Q.1,
                TP.HCM
              </li>
              <li><i class="fas fa-phone"></i> 1800 1168</li>
              <li><i class="fas fa-envelope"></i> contact@kimphat.com.vn</li>
            </ul>
          </div>
          <div class="footer-col">
            <h3>Kết nối với chúng tôi</h3>
            <div class="social-links">
              <a href="#"><i class="fab fa-facebook-f"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
              <a href="#"><i class="fab fa-youtube"></i></a>
              <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 Kim Phát Gold. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <script src="./assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="./assets/js/auth.js?v=<?php echo time(); ?>"></script>
    <script src="./assets/js/product-detail.js?v=<?php echo time(); ?>"></script>
  </body>
</html>
