<!-- Header -->
<div class="header">
    <div class="welcome">
        <h2>Admin Dashboard</h2>
    </div>
    
    <div class="admin-profile">
        <div class="admin-info" onclick="toggleDropdown()">
            <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($_SESSION['admin_email']))); ?>?s=32&d=mp" alt="Admin Avatar" class="admin-avatar">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div class="admin-dropdown" id="adminDropdown">
            <a href="#" class="dropdown-item">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="#" class="dropdown-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <div class="dropdown-divider"></div>            
            <a href="/Assignment/admin/includes/logout.php" class="dropdown-item text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('adminDropdown');
    dropdown.classList.toggle('show');
}

// Click outside to close dropdown
document.addEventListener('click', function(event) {
    const adminInfo = document.querySelector('.admin-info');
    const dropdown = document.getElementById('adminDropdown');
    
    if (!adminInfo.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
