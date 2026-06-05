document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-checkout-form]");
  if (!form) return;
  event.preventDefault();
  const data = Object.fromEntries(new FormData(form).entries());
  try {
    const order = await apiFetch("api/order.php?action=create", { method: "POST", body: JSON.stringify(data) });
    window.location.href = `${window.APP.baseUrl}/customer/order-status.php?code=${encodeURIComponent(order.order_code)}`;
  } catch (error) {
    toast(error.message, "error");
  }
});

async function pollOrderStatus() {
  const root = qs("[data-order-code]");
  if (!root) return;
  try {
    const order = await apiFetch(`api/order.php?action=status&code=${encodeURIComponent(root.dataset.orderCode)}`);
    qsa("[data-step]").forEach((step) => step.classList.toggle("active", step.dataset.stepIndex <= order.progress_index));
    const badge = qs("[data-live-status]");
    if (badge) badge.textContent = order.status_label;
  } catch (_) {}
}

if (qs("[data-order-code]")) {
  pollOrderStatus();
  setInterval(pollOrderStatus, 15000);
}
