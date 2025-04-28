// Banners page functionality
document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips and other Bootstrap components
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Xử lý hiển thị modal thêm banner
  document
    .querySelectorAll('[data-bs-target="#bannerModal"]')
    .forEach((button) => {
      button.addEventListener("click", function () {
        resetBannerForm();
      });
    });
});

// Reset form khi thêm mới banner
function resetBannerForm() {
  document.getElementById("bannerForm").reset();
  document.getElementById("bannerId").value = "";
  document.getElementById("modalTitle").textContent = "Add New Banner";
  document.getElementById("currentImage").innerHTML = "";
}

// Lấy thông tin banner để chỉnh sửa
function editBanner(bannerId) {
  document.getElementById("bannerForm").reset();
  document.getElementById("bannerId").value = bannerId;
  document.getElementById("modalTitle").textContent = "Edit Banner";

  // Fetch banner data
  fetch(`../includes/get_banner.php?banner_id=${bannerId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.banner) {
        document.querySelector('[name="banner_title"]').value =
          data.banner.banner_title;
        document.querySelector('[name="banner_desc"]').value =
          data.banner.banner_desc;

        // Hiển thị ảnh hiện tại
        if (data.banner.banner_img_url) {
          document.getElementById("currentImage").innerHTML = `
            <img src="../../${data.banner.banner_img_url}" alt="Current Banner" style="max-width: 200px">
            <p class="mt-2">Hình ảnh hiện tại sẽ được giữ lại nếu bạn không tải lên ảnh mới</p>
          `;
        }
      } else {
        showNotification("Không thể tải thông tin banner", "error");
      }

      // Hiển thị modal
      const modal = new bootstrap.Modal(document.getElementById("bannerModal"));
      modal.show();
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Lỗi khi tải dữ liệu banner", "error");
    });
}

// Xóa banner
function deleteBanner(bannerId) {
  if (confirm("Bạn có chắc chắn muốn xóa banner này?")) {
    // Tạo form data
    const formData = new FormData();
    formData.append("banner_id", bannerId);

    fetch("../includes/delete_banner.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification(data.message, "success");
          // Reload trang để cập nhật danh sách banner
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotification(data.message || "Xóa banner thất bại", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Có lỗi xảy ra khi xóa banner", "error");
      });
  }
}

// Lưu banner (thêm mới hoặc cập nhật)
function saveBanner() {
  const form = document.getElementById("bannerForm");
  const formData = new FormData(form);

  fetch("../includes/save_banner.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success");
        // Đóng modal và reload trang
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("bannerModal")
        );
        modal.hide();
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification(data.message || "Lưu banner thất bại", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Có lỗi xảy ra khi lưu banner", "error");
    });
}

// Hiển thị thông báo
function showNotification(message, type) {
  // Tạo phần tử thông báo
  const notification = document.createElement("div");
  notification.className = `alert alert-${
    type === "success" ? "success" : "danger"
  } alert-dismissible fade show notification`;
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  // Thêm thông báo vào body
  document.body.appendChild(notification);

  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    notification.remove();
  }, 3000);
}
