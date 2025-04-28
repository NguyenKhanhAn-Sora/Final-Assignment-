// Hàm để cập nhật số liệu thống kê
function updateDashboardStats() {
    // Giả lập dữ liệu thống kê (sau này sẽ lấy từ API)
    document.getElementById('todayOrders').textContent = '25';
    document.getElementById('totalProducts').textContent = '154';
    document.getElementById('totalUsers').textContent = '1,250';
    document.getElementById('todayRevenue').textContent = '$15,480';
}

// Cập nhật thống kê khi trang tải xong
document.addEventListener('DOMContentLoaded', function() {
    updateDashboardStats();
});

// Cập nhật thống kê mỗi 5 phút
setInterval(updateDashboardStats, 5 * 60 * 1000);
