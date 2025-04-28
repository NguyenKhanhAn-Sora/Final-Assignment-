document.addEventListener("DOMContentLoaded", function () {
  // DOM Elements
  const userIcon = document.querySelector(".user");
  const authDropdown = document.querySelector(".auth-dropdown");
  const loginModal = document.getElementById("loginModal");
  const signupModal = document.getElementById("signupModal");
  const loginBtn = document.getElementById("loginBtn");
  const signupBtn = document.getElementById("signupBtn");
  const logoutBtn = document.getElementById("logoutBtn");
  const switchToSignup = document.getElementById("switchToSignup");
  const switchToLogin = document.getElementById("switchToLogin");
  const closeButtons = document.querySelectorAll(".modal-close");
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  const loggedInMenu = document.querySelector(".logged-in-menu");

  // Toggle dropdown on user icon click
  userIcon.addEventListener("click", (e) => {
    e.stopPropagation();
    authDropdown.classList.toggle("show");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!userIcon.contains(e.target)) {
      authDropdown.classList.remove("show");
    }
  });

  // Show/Hide Modals
  loginBtn.addEventListener("click", (e) => {
    e.preventDefault();
    loginModal.classList.add("show");
    authDropdown.classList.remove("show");
  });

  signupBtn.addEventListener("click", (e) => {
    e.preventDefault();
    signupModal.classList.add("show");
    authDropdown.classList.remove("show");
  });

  // Switch between login and signup
  switchToSignup.addEventListener("click", (e) => {
    e.preventDefault();
    loginModal.classList.remove("show");
    signupModal.classList.add("show");
  });

  switchToLogin.addEventListener("click", (e) => {
    e.preventDefault();
    signupModal.classList.remove("show");
    loginModal.classList.add("show");
  });

  // Close modals
  closeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      loginModal.classList.remove("show");
      signupModal.classList.remove("show");
    });
  });

  // Close modals when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === loginModal) {
      loginModal.classList.remove("show");
    }
    if (e.target === signupModal) {
      signupModal.classList.remove("show");
    }
  });
  // Xử lý đăng nhập bằng AJAX để giữ modal mở khi có lỗi
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Ngăn chặn form submit bình thường

      // Lấy dữ liệu từ form
      const email = document.getElementById("loginEmail").value;
      const password = document.getElementById("loginPassword").value;
      const remember = document.getElementById("remember").checked ? "on" : "";

      // Tạo FormData object
      const formData = new FormData();
      formData.append("email", email);
      formData.append("password", password);
      if (remember) {
        formData.append("remember", remember);
      }

      // Gửi request đăng nhập qua AJAX
      fetch("includes/process_login.php", {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          // Xóa thông báo lỗi cũ nếu có
          const existingAlert = loginForm.querySelector(".alert");
          if (existingAlert) {
            existingAlert.remove();
          }

          if (data.success) {
            // Đăng nhập thành công
            showNotification("Đăng nhập thành công!", "success");
            // Chuyển hướng sau khi hiển thị thông báo
            setTimeout(() => {
              window.location.href = data.redirect || window.location.href;
            }, 1000);
          } else {
            // Đăng nhập thất bại, hiển thị lỗi trong modal
            const alertDiv = document.createElement("div");
            alertDiv.className = "alert alert-danger";
            alertDiv.textContent =
              data.message || "Đăng nhập thất bại. Vui lòng thử lại.";

            // Chèn thông báo lỗi ngay sau thẻ h2
            const heading = loginForm.querySelector("h2");
            heading.insertAdjacentElement("afterend", alertDiv);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Có lỗi xảy ra. Vui lòng thử lại sau.", "error");
        });
    });
  }

  // Handle Signup - Only validate password match, then submit directly to PHP
  if (signupForm) {
    signupForm.addEventListener("submit", (e) => {
      const password = document.getElementById("signupPassword").value;
      const confirmPassword = document.getElementById(
        "signupConfirmPassword"
      ).value;

      if (password !== confirmPassword) {
        e.preventDefault(); // Only prevent form submission if passwords don't match
        showNotification("Mật khẩu xác nhận không khớp!", "error");
        return;
      }
      // If passwords match, let the form submit normally to process_register.php
    });
  }

  // Show notification function
  function showNotification(message, type) {
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.classList.add("show");
    }, 100);

    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }
});
