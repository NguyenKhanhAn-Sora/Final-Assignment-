// Gold Prices page functionality
let priceHistoryChart;

document.addEventListener("DOMContentLoaded", function () {
  // Initialize chart
  initializePriceHistoryChart();
});

function initializePriceHistoryChart() {
  fetch("../includes/get_price_history.php")
    .then((response) => response.json())
    .then((data) => {
      const ctx = document.getElementById("priceHistoryChart").getContext("2d");

      priceHistoryChart = new Chart(ctx, {
        type: "line",
        data: {
          labels: data.dates,
          datasets: [
            {
              label: "Buy Price (24K)",
              data: data.buyPrices,
              borderColor: "#FFD700",
              tension: 0.1,
            },
            {
              label: "Sell Price (24K)",
              data: data.sellPrices,
              borderColor: "#DAA520",
              tension: 0.1,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: "24K Gold Price History (Last 30 Days)",
            },
          },
          scales: {
            y: {
              beginAtZero: false,
              ticks: {
                callback: function (value) {
                  return new Intl.NumberFormat("vi-VN").format(value) + " VND";
                },
              },
            },
          },
        },
      });
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading price history", "error");
    });
}

function editGoldPrice(priceId) {
  // Reset form
  document.getElementById("goldPriceForm").reset();
  document.getElementById("priceId").value = priceId;
  document.getElementById("modalTitle").textContent = "Edit Gold Price";

  fetch(`../includes/get_gold_price.php?id=${priceId}`)
    .then((response) => response.json())
    .then((data) => {
      document.querySelector('[name="gold_type"]').value = data.gold_type;
      document.querySelector('[name="buy_price"]').value = data.buy_price;
      document.querySelector('[name="sell_price"]').value = data.sell_price;

      const modal = new bootstrap.Modal(
        document.getElementById("goldPriceModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading gold price data", "error");
    });
}

function deleteGoldPrice(priceId) {
  if (confirm("Are you sure you want to delete this gold price record?")) {
    fetch("../includes/delete_gold_price.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ price_id: priceId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("Gold price record deleted successfully", "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showNotification("Error deleting gold price record", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showNotification("Error deleting gold price record", "error");
      });
  }
}

function saveGoldPrice() {
  const form = document.getElementById("goldPriceForm");
  const formData = new FormData(form);

  fetch("../includes/save_gold_price.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        bootstrap.Modal.getInstance(
          document.getElementById("goldPriceModal")
        ).hide();
        showNotification("Gold price saved successfully", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification(data.message || "Error saving gold price", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error saving gold price", "error");
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
