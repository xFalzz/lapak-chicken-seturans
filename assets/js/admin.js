document.addEventListener("change", async (event) => {
  const status = event.target.closest("[data-order-status]");
  if (!status) return;
  try {
    await apiFetch("api/status.php?action=update", {
      method: "POST",
      body: JSON.stringify({ order_id: status.dataset.orderStatus, status: status.value })
    });
    toast("Status pesanan diperbarui");
  } catch (error) {
    toast(error.message, "error");
  }
});
