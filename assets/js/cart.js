document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-add-cart]");
  if (!form) return;
  event.preventDefault();
  const data = Object.fromEntries(new FormData(form).entries());
  data.quantity = Number(data.quantity || 1);
  try {
    await apiFetch("api/cart.php?action=add", { method: "POST", body: JSON.stringify(data) });
    await refreshCartCount();
    toast("Menu ditambahkan ke keranjang");
  } catch (error) {
    toast(error.message, "error");
  }
});

document.addEventListener("click", async (event) => {
  const qtyBtn = event.target.closest("[data-cart-qty]");
  const removeBtn = event.target.closest("[data-cart-remove]");
  if (!qtyBtn && !removeBtn) return;
  const row = event.target.closest("[data-cart-row]");
  const id = row?.dataset.cartRow;
  try {
    if (qtyBtn) {
      await apiFetch("api/cart.php?action=update", {
        method: "POST",
        body: JSON.stringify({ cart_item_id: id, quantity: Number(qtyBtn.dataset.cartQty) })
      });
    }
    if (removeBtn) {
      await apiFetch("api/cart.php?action=remove", {
        method: "POST",
        body: JSON.stringify({ cart_item_id: id })
      });
    }
    window.location.reload();
  } catch (error) {
    toast(error.message, "error");
  }
});
