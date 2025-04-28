// Orders page functionality
document.addEventListener("DOMContentLoaded", function () {
  // Initialize status filter
  const statusFilter = document.getElementById("orderStatus");
  if (statusFilter) {
    statusFilter.addEventListener("change", filterOrders);
  }
});

function filterOrders() {
  const status = document.getElementById("orderStatus").value;
  const tableBody = document.getElementById("ordersTableBody");

  fetch(`../includes/get_orders.php?status=${status}`)
    .then((response) => response.json())
    .then((data) => {
      tableBody.innerHTML = "";
      data.forEach((order) => {
        const statusClass = {
          pending: "warning",
          processing: "info",
          shipped: "primary",
          delivered: "success",
          cancelled: "danger",
        }[order.status];

        const paymentStatusClass = {
          pending: "warning",
          paid: "success",
          failed: "danger",
        }[order.payment_status];

        const row = `
                    <tr>
                        <td>#${order.order_id}</td>
                        <td>${order.full_name}<br><small>${
          order.email
        }</small></td>
                        <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td><span class="badge bg-${statusClass}">${
          order.status
        }</span></td>
                        <td><span class="badge bg-${paymentStatusClass}">${
          order.payment_status
        }</span></td>
                        <td>${new Date(order.created_at).toLocaleString()}</td>
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-info" onclick="viewOrder(${
                              order.order_id
                            })">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="updateOrderStatus(${
                              order.order_id
                            })">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
        tableBody.innerHTML += row;
      });
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading orders", "error");
    });
}

function viewOrder(orderId) {
  fetch(`../includes/get_order_details.php?id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      // Populate shipping information
      document.getElementById("shippingInfo").innerHTML = `
                <p><strong>Name:</strong> ${data.shipping_name}</p>
                <p><strong>Phone:</strong> ${data.shipping_phone}</p>
                <p><strong>Address:</strong> ${data.shipping_address}</p>
                <p><strong>Payment Method:</strong> ${data.payment_method}</p>
            `;

      // Populate order items
      let itemsHtml = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

      data.items.forEach((item) => {
        itemsHtml += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.size_value}</td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${(
                          parseFloat(item.price) * parseInt(item.quantity)
                        ).toFixed(2)}</td>
                    </tr>
                `;
      });

      itemsHtml += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$${parseFloat(
                              data.total_amount
                            ).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            `;

      document.getElementById("orderItems").innerHTML = itemsHtml;

      // Show modal
      const modal = new bootstrap.Modal(
        document.getElementById("orderDetailsModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading order details", "error");
    });
}

function updateOrderStatus(orderId) {
  fetch(`../includes/get_order_details.php?id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("updateOrderId").value = orderId;
      document.querySelector('[name="status"]').value = data.status;
      document.querySelector('[name="payment_status"]').value =
        data.payment_status;

      const modal = new bootstrap.Modal(
        document.getElementById("updateStatusModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error loading order status", "error");
    });
}

function saveOrderStatus() {
  const form = document.getElementById("updateStatusForm");
  const formData = new FormData(form);

  fetch("../includes/update_order_status.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        bootstrap.Modal.getInstance(
          document.getElementById("updateStatusModal")
        ).hide();
        showNotification("Order status updated successfully", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showNotification(
          data.message || "Error updating order status",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error updating order status", "error");
    });
}

function showNotification(message, type) {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);

  // Show notification
  setTimeout(() => notification.classList.add("show"), 100);

  // Hide and remove notification
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}
