document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-add-cart]");
  if (!form) return;
  event.preventDefault();
  
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) submitBtn.classList.add('loading');
  
  const data = Object.fromEntries(new FormData(form).entries());
  data.quantity = Number(data.quantity || 1);
  try {
    await apiFetch("api/cart.php?action=add", { method: "POST", body: JSON.stringify(data) });
    await refreshCartCount();
    
    // Reset form
    form.reset();
    if (submitBtn) submitBtn.classList.remove('loading');
    
    // Add success state to button briefly
    if (submitBtn) {
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Berhasil';
        submitBtn.classList.replace('btn-primary', 'btn-success');
        submitBtn.style.background = 'var(--success)';
        submitBtn.style.color = 'white';
        
        setTimeout(() => {
            submitBtn.innerHTML = originalHtml;
            submitBtn.classList.replace('btn-success', 'btn-primary');
            submitBtn.style.background = '';
            submitBtn.style.color = '';
        }, 2000);
    }
    
    toast("Menu ditambahkan ke keranjang", "success");
  } catch (error) {
    if (submitBtn) submitBtn.classList.remove('loading');
    toast(error.message, "error");
  }
});

document.addEventListener("click", async (event) => {
  const qtyBtn = event.target.closest("[data-cart-qty]");
  const removeBtn = event.target.closest("[data-cart-remove]");
  if (!qtyBtn && !removeBtn) return;
  
  const row = event.target.closest("[data-cart-row]");
  const id = row?.dataset.cartRow;
  
  // Disable all buttons in row while processing
  if (row) {
      row.style.opacity = '0.5';
      row.style.pointerEvents = 'none';
  }
  
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
    if (row) {
        row.style.opacity = '1';
        row.style.pointerEvents = 'auto';
    }
    toast(error.message, "error");
  }
});
