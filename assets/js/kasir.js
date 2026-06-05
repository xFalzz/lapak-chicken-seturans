const cashInput = qs("[data-cash-input]");
if (cashInput) {
  cashInput.addEventListener("input", () => {
    const total = Number(cashInput.dataset.total || 0);
    const change = Math.max(0, Number(cashInput.value || 0) - total);
    qs("[data-change]") && (qs("[data-change]").textContent = rupiah(change));
  });
}

document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-pay-form]");
  if (!form) return;
  event.preventDefault();
  const data = Object.fromEntries(new FormData(form).entries());
  try {
    const result = await apiFetch("api/status.php?action=pay", { method: "POST", body: JSON.stringify(data) });
    window.location.href = `${window.APP.baseUrl}/kasir/receipt.php?order_id=${result.order_id}`;
  } catch (error) {
    toast(error.message, "error");
  }
});

if (qs("[data-kasir-refresh]")) setInterval(() => window.location.reload(), 20000);
