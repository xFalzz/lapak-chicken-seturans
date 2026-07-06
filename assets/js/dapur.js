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

function renderKdsCard(order) {
  const urgent = order.status === "cooking" && order.elapsed_minutes >= 10;
  const items = order.items.map((item) => `
    <div class="kds-item" style="flex-direction:column;align-items:flex-start;margin-bottom:8px;padding-bottom:8px;border-bottom:1px dashed var(--outline-variant);">
      <div style="display:flex;justify-content:space-between;width:100%;">
        <span style="font-weight:700;">${item.menu_name}</span>
        <span class="kds-qty">x${item.quantity}</span>
      </div>
      <div style="font-size:0.85rem;color:var(--secondary);display:flex;flex-wrap:wrap;gap:6px;margin-top:2px;">
        ${item.sauce_name ? `<span>Saus ${item.sauce_name}</span>` : ""}
        ${item.spice_level && item.spice_level !== '0' ? `<span style="color:#b29500;font-weight:700;">[Lvl ${item.spice_level}]</span>` : ""}
      </div>
      ${item.notes ? `<div style="font-size:0.8rem;color:var(--error);font-style:italic;margin-top:2px;">Catatan: "${item.notes}"</div>` : ""}
    </div>
  `).join("");
  return `
    <article class="kds-card ${order.status === "cooking" ? "cooking" : ""} ${urgent ? "urgent" : ""}" data-kds-card="${order.id}">
      <div class="kds-code">${order.order_code}</div>
      <p><span class="badge badge-blue">${order.order_type}</span> ${order.elapsed_label}</p>
      ${items}
      <button class="btn btn-primary" data-kds-action="${order.id}" data-next="${order.status === "confirmed" ? "cooking" : "ready"}">
        ${order.status === "confirmed" ? "Mulai Masak" : "Pesanan Siap"}
      </button>
    </article>
  `;
}

async function refreshKds() {
  if (!kdsRoot) return;
  const branchId = kdsRoot.dataset.branchId;
  const orders = await apiFetch(`api/order.php?action=kitchen&branch_id=${branchId}`);
  const incoming = new Set(orders.map((order) => String(order.id)));
  if ([...incoming].some((id) => !knownOrders.has(id)) && knownOrders.size) beep();
  knownOrders = incoming;
  qsa("[data-kds-card]").forEach((card) => { if (!incoming.has(card.dataset.kdsCard)) card.remove(); });
  orders.forEach((order) => {
    const existing = qs(`[data-kds-card="${order.id}"]`);
    if (existing) existing.outerHTML = renderKdsCard(order);
    else kdsRoot.insertAdjacentHTML("beforeend", renderKdsCard(order));
  });
}

document.addEventListener("click", async (event) => {
  const btn = event.target.closest("[data-kds-action]");
  if (!btn) return;
  await apiFetch("api/status.php?action=update", {
    method: "POST",
    body: JSON.stringify({ order_id: btn.dataset.kdsAction, status: btn.dataset.next })
  });
  refreshKds();
});

if (kdsRoot) {
  refreshKds();
  setInterval(refreshKds, 10000);
}
