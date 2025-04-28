// News & Promotions page functionality
document.addEventListener("DOMContentLoaded", function () {
  // Initialize TinyMCE
  tinymce.init({
    selector: "#newsContent",
    plugins:
      "anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount",
    toolbar:
      "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat",
    height: 400,
  });
});

function editNews(newsId) {
  // Reset form
  document.getElementById("newsForm").reset();
  document.getElementById("newsId").value = newsId;
  document.getElementById("modalTitle").textContent = "Edit Post";

  fetch(`../includes/get_news.php?id=${newsId}`)
    .then((response) => response.json())
    .then((data) => {
      document.querySelector('[name="title"]').value = data.title;
      document.querySelector('[name="type"]').value = data.type;
      document.querySelector('[name="status"]').value = data.status;

      // Set TinyMCE content
      tinymce.get("newsContent").setContent(data.content);

      // Show current image if exists
      if (data.image_url) {
        document.getElementById("currentImage").innerHTML = `
                    <img src="${data.image_url}" alt="Current Image" style="max-width: 200px">
                    <p class="mt-2">Current image will be kept if no new image is uploaded</p>
                `;
      }

      const modal = new bootstrap.Modal(document.getElementById("newsModal"));
      modal.show();
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading post data", "error");
    });
}

function deleteNews(newsId) {
  if (confirm("Are you sure you want to delete this post?")) {
    fetch("../includes/delete_news.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ news_id: newsId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("Post deleted successfully", "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification("Error deleting post", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Error deleting post", "error");
      });
  }
}

function saveNews() {
  const form = document.getElementById("newsForm");
  const formData = new FormData(form);

  // Add TinyMCE content to form data
  formData.append("content", tinymce.get("newsContent").getContent());

  fetch("../includes/save_news.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        bootstrap.Modal.getInstance(
          document.getElementById("newsModal")
        ).hide();
        showNotification("Post saved successfully", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification(data.message || "Error saving post", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error saving post", "error");
    });
}

function showNotification(message, type) {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => notification.classList.add("show"), 100);

  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}
