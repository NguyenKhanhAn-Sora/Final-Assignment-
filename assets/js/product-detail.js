// Hàm để thay đổi ảnh chính khi click vào ảnh thumbnail
function changeMainImage(newImageSrc, clickedThumbnail) {
  // Lấy tham chiếu đến ảnh chính
  const mainImage = document.getElementById("mainProductImage");

  // Nếu đã click vào ảnh đang active, không cần làm gì
  if (clickedThumbnail.classList.contains("active")) {
    return;
  }

  // Áp dụng hiệu ứng fade-out cho ảnh chính hiện tại
  mainImage.classList.add("fade-out");

  // Đợi hiệu ứng fade-out hoàn thành (200ms) rồi mới thay đổi src
  setTimeout(() => {
    // Thay đổi src của ảnh chính
    mainImage.src = newImageSrc;

    // Xóa class active từ tất cả các thumbnail
    const thumbnails = document.querySelectorAll(".thumbnail-image");
    thumbnails.forEach((thumb) => {
      thumb.classList.remove("active");
    });

    // Thêm class active vào thumbnail được click
    clickedThumbnail.classList.add("active");

    // Đợi ảnh chính load xong thì áp dụng hiệu ứng fade-in
    mainImage.onload = () => {
      mainImage.classList.remove("fade-out");
      mainImage.classList.add("fade-in");

      // Xóa class fade-in sau khi hiệu ứng hoàn thành để có thể áp dụng lại
      setTimeout(() => {
        mainImage.classList.remove("fade-in");
      }, 500);
    };

    // Trong trường hợp ảnh đã được cache và onload không kích hoạt
    if (mainImage.complete) {
      mainImage.classList.remove("fade-out");
      mainImage.classList.add("fade-in");
      setTimeout(() => {
        mainImage.classList.remove("fade-in");
      }, 500);
    }
  }, 200);
}

document.addEventListener("DOMContentLoaded", function () {
  // Xử lý chuyển đổi ảnh khi click vào ảnh thumbnail
  const thumbnails = document.querySelectorAll(".thumbnail-image");
  thumbnails.forEach((thumbnail) => {
    thumbnail.addEventListener("click", function () {
      const imageUrl = this.getAttribute("data-image-url");
      if (imageUrl) {
        changeMainImage(imageUrl, this);
      }
    });
  });

  // Xử lý chọn kích thước
  const sizeOptions = document.querySelectorAll(".size-option");
  sizeOptions.forEach((option) => {
    option.addEventListener("click", function () {
      // Xóa class active từ tất cả các options
      sizeOptions.forEach((opt) => opt.classList.remove("active"));
      // Thêm class active vào option được chọn
      this.classList.add("active");
    });
  });

  // Xử lý tăng giảm số lượng
  const quantityInput = document.querySelector(".quantity-controls input");
  const minusBtn = document.querySelector(".quantity-btn.minus");
  const plusBtn = document.querySelector(".quantity-btn.plus");

  // Đặt giá trị mặc định và giới hạn
  const minQuantity = 1;
  const maxQuantity = 99;

  // Cập nhật số lượng
  function updateQuantity(newValue) {
    newValue = Math.max(minQuantity, Math.min(maxQuantity, newValue));
    quantityInput.value = newValue;
  }

  // Xử lý sự kiện click nút giảm
  minusBtn.addEventListener("click", () => {
    updateQuantity(parseInt(quantityInput.value) - 1);
  });

  // Xử lý sự kiện click nút tăng
  plusBtn.addEventListener("click", () => {
    updateQuantity(parseInt(quantityInput.value) + 1);
  });

  // Xử lý khi người dùng nhập trực tiếp vào input
  quantityInput.addEventListener("change", function () {
    let value = parseInt(this.value) || minQuantity;
    updateQuantity(value);
  });

  // Ngăn không cho nhập chữ vào input số lượng
  quantityInput.addEventListener("keypress", function (e) {
    if (!/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  });
});
