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
    <div class="kds-item"><span>${item.menu_name}${item.sauce_name ? ` - ${item.sauce_name}` : ""}</span><span class="kds-qty">x${item.quantity}</span></div>
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
