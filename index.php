<?php 
session_start(); 
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kim Phát Gold - Trang sức cao cấp</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link rel="stylesheet" href="./assets/css/style.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="./assets/css/auth.css?v=<?php echo time(); ?>" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
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
              <li><a href="#">Trang chủ</a></li>
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
            </div>            <div class="user">
              <i class="fa-solid fa-user"></i>
              <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
              <?php endif; ?>
              <div class="auth-dropdown">
                <?php if(!isset($_SESSION['user_id'])): ?>
                  <a href="#" id="loginBtn">Đăng nhập</a>
                  <a href="#" id="signupBtn">Đăng ký</a>
                <?php else: ?>
                  <div class="logged-in-menu">
                    <a href="#" id="profileBtn">Tài khoản của tôi</a>
                    <hr />
                    <a href="includes/logout.php" id="logoutBtn">Đăng xuất</a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Auth Modals -->    <div class="modal" id="loginModal">
      <div class="modal-content">
        <span class="modal-close">&times;</span>
        <form class="auth-form" id="loginForm" action="includes/process_login.php" method="POST">
          <h2>Đăng nhập</h2>
          <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
          <?php endif; ?>
          <div class="form-group">
            <label for="loginEmail">Email</label>
            <input type="email" id="loginEmail" name="email" required />
          </div>

          <div class="form-group">
            <label for="loginPassword">Mật khẩu</label>
            <input type="password" id="loginPassword" name="password" required />
          </div>
          <div class="form-group checkbox" style="display: flex; align-items: center;">
            <input type="checkbox" id="remember" name="remember" style="width: fit-content" />
            <label for="remember">Ghi nhớ đăng nhập</label>
          </div>
          <button type="submit" class="btn">Đăng nhập</button>
          <div class="switch-form">
            <p>
              Chưa có tài khoản?
              <a href="#" id="switchToSignup">Đăng ký ngay</a>
            </p>
          </div>
        </form>
      </div>
    </div>    <div class="modal" id="signupModal">
      <div class="modal-content">
        <span class="modal-close">&times;</span>
        <form class="auth-form" id="signupForm" action="includes/process_register.php" method="POST">
          <h2>Đăng ký</h2>
          <?php if(isset($_SESSION['register_error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
          <?php endif; ?>
          <div class="form-group">
            <label for="signupName">Họ và tên</label>
            <input type="text" id="signupName" name="full_name" required />
          </div>
          <div class="form-group">
            <label for="signupEmail">Email</label>
            <input type="email" id="signupEmail" name="email" required />
          </div>
          <div class="form-group">
            <label for="signupPhone">Số điện thoại</label>
            <input
              type="number"
              id="signupPhone"
              class="signupPhone"
              name="phone"
              required
            />
          </div>
          <div class="form-group">
            <label for="signupPassword">Mật khẩu</label>
            <input type="password" id="signupPassword" name="password" required />
          </div>
          <div class="form-group">
            <label for="signupConfirmPassword">Xác nhận mật khẩu</label>
            <input type="password" id="signupConfirmPassword" name="confirm_password" required />
          </div>
          <button type="submit" class="btn">Đăng ký</button>
          <div class="switch-form">
            <p>Đã có tài khoản? <a href="#" id="switchToLogin">Đăng nhập</a></p>
          </div>
        </form>
      </div>
    </div>    <!-- Hero Section -->
    <section class="hero">
      <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
          <?php
          // Lấy banner từ database
          $query = "SELECT * FROM banner ORDER BY create_at DESC";
          $stmt = $conn->query($query);
          
          // Kiểm tra nếu có banner
          if ($stmt->rowCount() > 0) {
            while ($banner = $stmt->fetch()) {
              ?>              <div class="swiper-slide">                <img
                  src="./<?php echo $banner['banner_img_url']; ?>"
                  alt="<?php echo htmlspecialchars($banner['banner_title']); ?>"
                />
                <div class="slide-content">
                  <h2><?php echo htmlspecialchars($banner['banner_title']); ?></h2>
                  <p><?php echo htmlspecialchars($banner['banner_desc']); ?></p>
                  <a href="#" class="btn">Xem ngay</a>
                </div>
              </div>
              <?php
            }
          } else {
            // Hiển thị banner mặc định nếu không có dữ liệu trong database
            ?>
            <div class="swiper-slide">
              <img
                src="https://product.hstatic.net/200000567741/product/3a_84348bdc63784a29bb8a7a1836e854ea.png"
                alt="Gold Collection"
              />
              <div class="slide-content">
                <h2>Bộ sưu tập mới</h2>
                <p>Khám phá những thiết kế độc đáo và sang trọng</p>
                <a href="#" class="btn">Xem ngay</a>
              </div>
            </div>
            <?php
          }
          ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
      </div>
    </section>    <!-- Gold Price Section -->
    <section class="gold-price">
      <div class="container">
        <div class="price-header">
          <h2 class="section-title">Bảng giá vàng hôm nay</h2>
          <div class="last-updated">
            <?php
            // Lấy thời gian cập nhật mới nhất của giá vàng
            $update_query = "SELECT MAX(updated_at) as last_update FROM gold_prices";
            $update_stmt = $conn->query($update_query);
            $update_time = $update_stmt->fetch(PDO::FETCH_ASSOC)['last_update'];
            
            if ($update_time) {
              echo '<p>Cập nhật lúc: ' . date('H:i - d/m/Y', strtotime($update_time)) . '</p>';
            }
            ?>
          </div>
        </div>
        
        <div class="price-table">
          <?php
          // Lấy danh sách giá vàng từ database
          $gold_query = "SELECT * FROM gold_prices ORDER BY FIELD(gold_type, 'SJC', '24K', '18K', '14K', '10K')";
          $gold_stmt = $conn->query($gold_query);
          
          if ($gold_stmt->rowCount() > 0) {
            while ($gold = $gold_stmt->fetch()) {
              // Hiển thị tên loại vàng
              $gold_name = '';
              switch ($gold['gold_type']) {
                case 'SJC':
                  $gold_name = 'Vàng SJC';
                  break;
                case '24K':
                  $gold_name = 'Vàng 24K';
                  break;
                case '18K':
                  $gold_name = 'Vàng 18K';
                  break;
                case '14K':
                  $gold_name = 'Vàng 14K';
                  break;
                case '10K':
                  $gold_name = 'Vàng 10K';
                  break;
                default:
                  $gold_name = $gold['gold_type'];
              }
              ?>
              <div class="price-item">
                <h3><?php echo $gold_name; ?></h3>
                <div class="price">
                  <div class="buy">
                    <span>Mua vào</span>
                    <strong><?php echo number_format($gold['buy_price'], 0, ',', '.'); ?>đ</strong>
                  </div>
                  <div class="sell">
                    <span>Bán ra</span>
                    <strong><?php echo number_format($gold['sell_price'], 0, ',', '.'); ?>đ</strong>
                  </div>
                </div>
              </div>
              <?php
            }
          } else {
            // Nếu không có dữ liệu, hiển thị thông báo
            echo '<div class="no-price-data">Chưa có thông tin giá vàng</div>';
          }
          ?>
        </div>
        <div class="price-note">
          <p>* Giá vàng được cập nhật theo thị trường. Vui lòng liên hệ cửa hàng để biết giá chính xác nhất.</p>
        </div>
      </div>
    </section>    <!-- Featured Products -->    <section id="featured-products" class="featured-products">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Sản phẩm nổi bật</h2>
          <p class="section-subtitle">Khám phá các sản phẩm trang sức cao cấp được chọn lọc kỹ lưỡng</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
          <form action="#featured-products" method="GET" class="product-filter-form">
            <div class="filter-row">
              <div class="search-box">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
              </div>
              
              <div class="filter-group">
                <select name="material" class="filter-select">
                  <option value="">Loại vàng</option>
                  <?php
                  // Lấy danh sách loại vàng từ bảng materials
                  $materials_query = "SELECT material_id, material_name FROM materials ORDER BY material_name";
                  $materials_stmt = $conn->query($materials_query);
                  while ($material = $materials_stmt->fetch()) {
                    $selected = (isset($_GET['material']) && $_GET['material'] == $material['material_id']) ? 'selected' : '';
                    echo '<option value="' . $material['material_id'] . '" ' . $selected . '>' . htmlspecialchars($material['material_name']) . '</option>';
                  }
                  ?>
                </select>
              </div>
              
              <div class="filter-group">
                <select name="category" class="filter-select">
                  <option value="">Loại trang sức</option>
                  <?php
                  // Lấy danh sách loại trang sức từ bảng categories
                  $categories_query = "SELECT category_id, name FROM categories ORDER BY name";
                  $categories_stmt = $conn->query($categories_query);
                  while ($category = $categories_stmt->fetch()) {
                    $selected = (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : '';
                    echo '<option value="' . $category['category_id'] . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
                  }
                  ?>
                </select>
              </div>
              
              <div class="filter-group">
                <select name="price_range" class="filter-select">
                  <option value="">Khoảng giá</option>
                  <option value="0-2000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "0-2000000") ? 'selected' : ''; ?>>Dưới 2 triệu</option>
                  <option value="2000000-5000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "2000000-5000000") ? 'selected' : ''; ?>>2 - 5 triệu</option>
                  <option value="5000000-10000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "5000000-10000000") ? 'selected' : ''; ?>>5 - 10 triệu</option>
                  <option value="10000000-20000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "10000000-20000000") ? 'selected' : ''; ?>>10 - 20 triệu</option>
                  <option value="20000000-0" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == "20000000-0") ? 'selected' : ''; ?>>Trên 20 triệu</option>
                </select>
              </div>
              
              <div class="filter-check">
                <input type="checkbox" id="on_sale" name="on_sale" value="1" 
                       <?php echo (isset($_GET['on_sale']) && $_GET['on_sale'] == "1") ? 'checked' : ''; ?>>
                <label for="on_sale">Đang giảm giá</label>
              </div>
              
              <div class="filter-actions">
                <button type="submit" class="filter-btn">Lọc</button>
                <a href="?#featured-products" class="clear-filter-btn">Xóa bộ lọc</a>
              </div>
            </div>
          </form>
        </div>

        <div class="product-grid">
          <?php          // Thiết lập thông số phân trang
          $products_per_page = 8; // Số sản phẩm hiển thị trên mỗi trang
          $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
          $current_page = max(1, $current_page); // Đảm bảo trang không nhỏ hơn 1
          $offset = ($current_page - 1) * $products_per_page;
          
          // Khởi tạo các điều kiện lọc
          $where_conditions = ["p.stock_status = 'in_stock'"];
          $params = [];
          
          // Xử lý tìm kiếm theo từ khóa
          if (isset($_GET['search']) && !empty($_GET['search'])) {
              $search_term = '%' . $_GET['search'] . '%';
              $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search)";
              $params[':search'] = $search_term;
          }
          
          // Lọc theo loại vàng (materials)
          if (isset($_GET['material']) && !empty($_GET['material'])) {
              $where_conditions[] = "p.material_id = :material_id";
              $params[':material_id'] = $_GET['material'];
          }
          
          // Lọc theo loại trang sức (categories)
          if (isset($_GET['category']) && !empty($_GET['category'])) {
              $where_conditions[] = "p.category_id = :category_id";
              $params[':category_id'] = $_GET['category'];
          }
          
          // Lọc theo khoảng giá
          if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
              $price_range = explode('-', $_GET['price_range']);
              if (count($price_range) == 2) {
                  $min_price = (float)$price_range[0];
                  $max_price = (float)$price_range[1];
                  
                  if ($min_price > 0 && $max_price > 0) {
                      // Khoảng giá từ min đến max
                      $where_conditions[] = "(p.sale_price > 0 AND p.sale_price BETWEEN :min_price AND :max_price) OR 
                                           (p.sale_price = 0 AND p.price BETWEEN :min_price AND :max_price)";
                      $params[':min_price'] = $min_price;
                      $params[':max_price'] = $max_price;
                  } elseif ($min_price > 0 && $max_price == 0) {
                      // Giá trên min
                      $where_conditions[] = "(p.sale_price > 0 AND p.sale_price >= :min_price) OR 
                                           (p.sale_price = 0 AND p.price >= :min_price)";
                      $params[':min_price'] = $min_price;
                  } elseif ($min_price == 0 && $max_price > 0) {
                      // Giá dưới max
                      $where_conditions[] = "(p.sale_price > 0 AND p.sale_price <= :max_price) OR 
                                           (p.sale_price = 0 AND p.price <= :max_price)";
                      $params[':max_price'] = $max_price;
                  }
              }
          }
          
          // Lọc sản phẩm đang giảm giá
          if (isset($_GET['on_sale']) && $_GET['on_sale'] == "1") {
              $where_conditions[] = "p.sale_price > 0 AND p.sale_price < p.price";
          }
          
          // Tạo câu WHERE từ các điều kiện
          $where_clause = implode(' AND ', $where_conditions);
          
          // Đếm tổng số sản phẩm để tính số trang (với các bộ lọc)
          $count_query = "
            SELECT COUNT(*) as total 
            FROM products p
            LEFT JOIN materials m ON p.material_id = m.material_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE $where_clause
          ";
          
          $count_stmt = $conn->prepare($count_query);
          foreach ($params as $key => $value) {
              $count_stmt->bindValue($key, $value);
          }
          $count_stmt->execute();
          $total_products = $count_stmt->fetch()['total'];
          $total_pages = ceil($total_products / $products_per_page);
          
          // Lấy danh sách sản phẩm nổi bật từ database với phân trang và các bộ lọc
          $featured_query = "
            SELECT p.*, m.material_name, i.image_url, c.name as category_name
            FROM products p
            LEFT JOIN materials m ON p.material_id = m.material_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN product_images i ON p.product_id = i.product_id AND i.is_primary = 1
            WHERE $where_clause
            ORDER BY 
              CASE WHEN p.sale_price IS NOT NULL THEN 0 ELSE 1 END,
              p.created_at DESC
            LIMIT :limit OFFSET :offset
          ";
          
          $featured_stmt = $conn->prepare($featured_query);
          $featured_stmt->bindParam(':limit', $products_per_page, PDO::PARAM_INT);
          $featured_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
          
          // Bind các tham số bộ lọc
          foreach ($params as $key => $value) {
              $featured_stmt->bindValue($key, $value);
          }
          $featured_stmt->execute();
          
          if ($featured_stmt->rowCount() > 0) {
            while ($product = $featured_stmt->fetch()) {
              // Nếu không có hình ảnh chính, lấy hình ảnh đầu tiên của sản phẩm
              if (empty($product['image_url'])) {
                $img_query = "SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1";
                $img_stmt = $conn->prepare($img_query);
                $img_stmt->execute([$product['product_id']]);
                $img_result = $img_stmt->fetch();
                $product_image = $img_result ? $img_result['image_url'] : 'assets/images/no-image.png';
              } else {
                $product_image = $product['image_url'];
              }

              // Tính phần trăm giảm giá nếu có
              $discount_percent = null;
              $price_display = number_format($product['price'], 0, ',', '.');
              
              if (!empty($product['sale_price'])) {
                $discount_percent = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                $price_display = '<span class="old-price">' . number_format($product['price'], 0, ',', '.') . 'đ</span> ' . 
                                 number_format($product['sale_price'], 0, ',', '.') . 'đ';
              } else {
                $price_display = number_format($product['price'], 0, ',', '.') . 'đ';
              }

              // Hiển thị tên vật liệu
              $material_display = !empty($product['material_name']) ? $product['material_name'] : '';
              
              // Tạo liên kết chi tiết sản phẩm
              $product_link = "product-detail.php?slug=" . $product['slug'];
              ?>
              
              <div class="product-card">
                <div class="product-image">
                  <img src="<?php echo htmlspecialchars($product_image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                  <?php if ($discount_percent): ?>
                  <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
                  <?php endif; ?>
                  <div class="product-overlay">
                    <a href="<?php echo $product_link; ?>" class="btn-view">Xem chi tiết</a>
                  </div>
                </div>
                <div class="product-info">
                  <?php if (!empty($material_display)): ?>
                  <div class="product-material"><?php echo htmlspecialchars($material_display); ?></div>
                  <?php endif; ?>
                  <h3><a href="<?php echo $product_link; ?>" class="product-name"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                  <p class="price"><?php echo $price_display; ?></p>
                </div>
              </div>
              <?php
            }
          } else {
            echo '<div class="no-products">Chưa có sản phẩm nào.</div>';
          }
          ?>
        </div>
          <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
          <ul class="pagination">            <?php 
            // Tạo query string cho các bộ lọc
            $filter_params = $_GET;
            
            // Hàm để tạo URL với các tham số lọc
            function buildPaginationUrl($page, $params = []) {
                $params['page'] = $page;
                return '?' . http_build_query($params) . '#featured-products';
            }
            
            if($current_page > 1): ?>
              <li><a href="<?php echo buildPaginationUrl(1, $filter_params); ?>" class="pagination-link first">&laquo;</a></li>
              <li><a href="<?php echo buildPaginationUrl($current_page-1, $filter_params); ?>" class="pagination-link prev">&lsaquo;</a></li>
            <?php endif; ?>
            
            <?php
            // Hiển thị các nút số trang
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            // Đảm bảo luôn hiển thị ít nhất 5 nút trang (nếu có đủ trang)
            if ($end_page - $start_page + 1 < 5) {
              if ($start_page == 1) {
                $end_page = min($total_pages, $start_page + 4);
              } elseif ($end_page == $total_pages) {
                $start_page = max(1, $end_page - 4);
              }
            }            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
              <li>
                <a href="<?php echo buildPaginationUrl($i, $filter_params); ?>" class="pagination-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php endfor; ?>            <?php if($current_page < $total_pages): ?>
              <li><a href="<?php echo buildPaginationUrl($current_page+1, $filter_params); ?>" class="pagination-link next">&rsaquo;</a></li>
              <li><a href="<?php echo buildPaginationUrl($total_pages, $filter_params); ?>" class="pagination-link last">&raquo;</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us">
      <div class="container">
        <h2 class="section-title">Tại sao chọn Kim Phát Gold?</h2>
        <div class="features-grid">
          <div class="feature-item">
            <i class="fas fa-certificate"></i>
            <h3>Chất lượng đảm bảo</h3>
            <p>Cam kết vàng nguyên chất, có giấy kiểm định</p>
          </div>
          <div class="feature-item">
            <i class="fas fa-exchange-alt"></i>
            <h3>Chính sách đổi trả</h3>
            <p>Đổi trả sản phẩm trong vòng 48h</p>
          </div>
          <div class="feature-item">
            <i class="fas fa-truck"></i>
            <h3>Giao hàng bảo mật</h3>
            <p>Vận chuyển an toàn, bảo mật</p>
          </div>
          <div class="feature-item">
            <i class="fas fa-headset"></i>
            <h3>Hỗ trợ 24/7</h3>
            <p>Tư vấn chuyên nghiệp mọi lúc</p>
          </div>
        </div>
      </div>
    </section>

    <!-- News Section -->
    <section class="news">
      <div class="container">
        <h2 class="section-title">Tin tức & Khuyến mãi</h2>
        <div class="news-grid">
          <div class="news-card">
            <div class="news-image">
              <img
                src="https://senydajewelry.com/wp-content/uploads/2025/01/Thumbnail-blog-vie-TRANG-SUC-KHONG-THE-BO-QUA-2025.jpg"
                alt="Tin tức vàng"
              />
            </div>
            <div class="news-content">
              <h3>Xu hướng trang sức 2025</h3>
              <p>
                Khám phá những xu hướng trang sức mới nhất trong năm 2025...
              </p>
              <a href="#" class="read-more">Xem thêm</a>
            </div>
          </div>
          <div class="news-card">
            <div class="news-image">
              <img
                src="https://nhahangvenus.com/wp-content/uploads/2020/10/Ads-cuoi-venus-900x603.jpg"
                alt="Khuyến mãi"
              />
            </div>
            <div class="news-content">
              <h3>Ưu đãi mùa cưới</h3>
              <p>Chương trình ưu đãi đặc biệt dành cho mùa cưới 2025...</p>
              <a href="#" class="read-more">Xem thêm</a>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="./assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="./assets/js/auth.js?v=<?php echo time(); ?>"></script>
  </body>
</html>
