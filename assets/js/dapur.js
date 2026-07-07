const kdsRoot = qs("[data-kds]");
let knownOrders = new Set();

function beep() {
  const AudioContext = window.AudioContext || window.webkitAudioContext;
  if (!AudioContext) return;
  const ctx = new AudioContext();
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.frequency.value = 880;
  gain.gain.value = 0.05;
  osc.connect(gain);
  gain.connect(ctx.destination);
  osc.start();
  setTimeout(() => { osc.stop(); ctx.close(); }, 160);
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function renderKdsCard(order) {
  const urgent = order.status === "cooking" && order.elapsed_minutes >= 10;
  const typeClass = (order.order_type || '').replace(/\s/g, '_').toLowerCase();
  
  const items = order.items.map((item) => `
    <div class="kds-item">
      <div class="kds-item-info">
        <div class="kds-item-name">${escapeHtml(item.menu_name)}</div>
        <div class="kds-item-extras">
          ${item.sauce_name ? `<span class="kds-item-tag sauce">🌶️ ${escapeHtml(item.sauce_name)}</span>` : ""}
          ${item.spice_level && item.spice_level !== '0' ? `<span class="kds-item-tag spice">🔥 Lvl ${escapeHtml(item.spice_level)}</span>` : ""}
        </div>
        ${item.notes ? `<div class="kds-item-note">📝 "${escapeHtml(item.notes)}"</div>` : ""}
      </div>
      <span class="kds-qty">x${item.quantity}</span>
    </div>
  `).join("");

  return `
    <article class="kds-card ${order.status === "cooking" ? "cooking" : ""} ${urgent ? "urgent" : ""}" data-kds-card="${order.id}">
      <div class="kds-card-header">
        <div class="kds-code">${escapeHtml(order.order_code)}</div>
        <span class="kds-type-badge ${typeClass}">${escapeHtml(order.order_type)}</span>
      </div>
      <div class="kds-timer">
        <i class="fa-regular fa-clock"></i>
        <span>${escapeHtml(order.elapsed_label)}</span>
        <span style="margin-left:auto;font-weight:700;color:rgba(255,255,255,0.6);">${escapeHtml(order.customer_name || '')}</span>
      </div>
      <div class="kds-card-items">${items}</div>
      <div class="kds-card-action">
        <button class="btn btn-primary" data-kds-action="${order.id}" data-next="${order.status === "confirmed" ? "cooking" : "ready"}">
          <i class="fa-solid ${order.status === "confirmed" ? "fa-fire-burner" : "fa-circle-check"}"></i>
          ${order.status === "confirmed" ? "Mulai Masak" : "Pesanan Siap"}
        </button>
      </div>
    </article>
  `;
}

async function refreshKds() {
  if (!kdsRoot) return;
  const branchId = kdsRoot.dataset.branchId;
  try {
    const orders = await apiFetch(`api/order.php?action=kitchen&branch_id=${branchId}`);
    const incoming = new Set(orders.map((order) => String(order.id)));
    if ([...incoming].some((id) => !knownOrders.has(id)) && knownOrders.size) beep();
    knownOrders = incoming;
    
    qsa("[data-kds-card]").forEach((card) => { 
      if (!incoming.has(card.dataset.kdsCard)) card.remove(); 
    });
    
    if (orders.length === 0) {
      if (!qs('.kds-empty')) {
        kdsRoot.innerHTML = `
          <div class="kds-empty">
            <i class="fa-solid fa-mug-hot"></i>
            <h3>Tidak ada pesanan aktif</h3>
            <p>Pesanan baru akan otomatis muncul di sini</p>
          </div>
        `;
      }
      return;
    }
    const emptyEl = qs('.kds-empty');
    if (emptyEl) emptyEl.remove();
    
    orders.forEach((order) => {
      const existing = qs(`[data-kds-card="${order.id}"]`);
      if (existing) existing.outerHTML = renderKdsCard(order);
      else kdsRoot.insertAdjacentHTML("beforeend", renderKdsCard(order));
    });
  } catch (err) {
    console.error("Failed to refresh KDS:", err);
  }
}

document.addEventListener("click", async (event) => {
  const btn = event.target.closest("[data-kds-action]");
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
  try {
    await apiFetch("api/status.php?action=update", {
      method: "POST",
      body: JSON.stringify({ order_id: btn.dataset.kdsAction, status: btn.dataset.next })
    });
    refreshKds();
  } catch (err) {
    btn.disabled = false;
    btn.innerHTML = 'Gagal, coba lagi';
    console.error(err);
  }
});

if (kdsRoot) {
  refreshKds();
  setInterval(refreshKds, 10000);
}
