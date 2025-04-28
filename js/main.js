// Initialize Swiper
const heroSwiper = new Swiper(".heroSwiper", {
  // Enable loop mode
  loop: true,

  // Enable auto play
  autoplay: {
    delay: 5000,
    disableOnInteraction: false,
  },

  // Add pagination
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },

  // Add navigation arrows
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  // Enable smooth transitions
  effect: "fade",
  fadeEffect: {
    crossFade: true,
  },
});

// Scroll animations
document.addEventListener("DOMContentLoaded", function () {
  // Header scroll effect
  const header = document.querySelector(".header");
  let lastScroll = 0;

  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll <= 0) {
      header.classList.remove("scroll-up");
      return;
    }

    if (
      currentScroll > lastScroll &&
      !header.classList.contains("scroll-down")
    ) {
      header.classList.remove("scroll-up");
      header.classList.add("scroll-down");
    } else if (
      currentScroll < lastScroll &&
      header.classList.contains("scroll-down")
    ) {
      header.classList.remove("scroll-down");
      header.classList.add("scroll-up");
    }
    lastScroll = currentScroll;
  });

  // Animate elements on scroll
  const animateOnScroll = () => {
    const elements = document.querySelectorAll(
      ".price-item, .product-card, .feature-item, .news-card"
    );

    elements.forEach((element) => {
      const elementTop = element.getBoundingClientRect().top;
      const elementBottom = element.getBoundingClientRect().bottom;

      if (elementTop < window.innerHeight && elementBottom > 0) {
        element.style.opacity = "1";
        element.style.transform = "translateY(0)";
      }
    });
  };

  // Set initial state for animated elements
  const elements = document.querySelectorAll(
    ".price-item, .product-card, .feature-item, .news-card"
  );
  elements.forEach((element) => {
    element.style.opacity = "0";
    element.style.transform = "translateY(20px)";
    element.style.transition = "all 0.6s ease-out";
  });

  // Listen for scroll events
  window.addEventListener("scroll", animateOnScroll);
  // Initial check for elements in view
  animateOnScroll();
});

// Search functionality
const searchIcon = document.querySelector(".search");
searchIcon.addEventListener("click", () => {
  // Add search functionality here
  alert("Tính năng tìm kiếm đang được phát triển");
});

// Cart functionality
const cartIcon = document.querySelector(".cart");
cartIcon.addEventListener("click", () => {
  // Add cart functionality here
  alert("Tính năng giỏ hàng đang được phát triển");
});
