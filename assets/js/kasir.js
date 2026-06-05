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

async function refreshKasirQueue() {
  const container = qs("[data-kasir-refresh]");
  if (!container) return;
  const branchId = container.dataset.branchId;
  try {
    const orders = await apiFetch(`api/order.php?action=list&branch_id=${branchId}`);
    
    const readyQueue = qs("[data-queue-ready]");
    const activeQueue = qs("[data-queue-active]");
    
    if (!readyQueue || !activeQueue) return;
    
    // Group orders
    const readyOrders = orders.filter(o => o.status === 'ready');
    const activeOrders = orders.filter(o => ['confirmed', 'cooking'].includes(o.status));
    
    // Render ready orders
    if (readyOrders.length === 0) {
      readyQueue.innerHTML = '<p class="muted" style="grid-column: 1/-1; text-align: center; padding: 24px; background: var(--white); border-radius: var(--radius-md); border: 1px solid var(--gray-border);">Tidak ada antrean siap dibayar.</p>';
    } else {
      readyQueue.innerHTML = readyOrders.map(o => `
        <article class="card order-ticket" data-order-ticket-id="${o.id}">
          <div class="code">${escapeHtml(o.order_code)}</div>
          <p>${escapeHtml(o.customer_name)} - ${escapeHtml(o.order_type)}</p>
          <p>${escapeHtml(o.items_count || '0')} item - ${timeAgo(o.created_at)}</p>
          <h3>${rupiah(Number(o.total))}</h3>
          <a class="btn btn-primary" href="process.php?order_id=${o.id}">Proses Pembayaran</a>
        </article>
      `).join('');
    }
    
    // Render active orders
    if (activeOrders.length === 0) {
      activeQueue.innerHTML = '<p class="muted" style="grid-column: 1/-1; text-align: center; padding: 24px; background: var(--white); border-radius: var(--radius-md); border: 1px solid var(--gray-border);">Tidak ada antrean sedang diproses.</p>';
    } else {
      activeQueue.innerHTML = activeOrders.map(o => `
        <article class="card order-ticket" data-order-ticket-id="${o.id}">
          <div class="code">${escapeHtml(o.order_code)}</div>
          <p>${escapeHtml(o.customer_name)} - ${getStatusLabel(o.status)}</p>
          <p>${escapeHtml(o.items_count || '0')} item - ${timeAgo(o.created_at)}</p>
          <h3>${rupiah(Number(o.total))}</h3>
        </article>
      `).join('');
    }
  } catch (err) {
    console.error("Failed to refresh cashier queue:", err);
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function timeAgo(dateStr) {
  const seconds = Math.max(0, Math.floor((new Date() - new Date(dateStr.replace(/-/g, "/"))) / 1000));
  if (seconds < 60) return `${seconds} detik lalu`;
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes} menit lalu`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} jam lalu`;
  return `${Math.floor(hours / 24)} hari lalu`;
}

function getStatusLabel(status) {
  return {
    'pending': 'Menunggu',
    'confirmed': 'Dikonfirmasi',
    'cooking': 'Dimasak',
    'ready': 'Siap Saji',
    'completed': 'Selesai',
    'cancelled': 'Batal'
  }[status] || status;
}

if (qs("[data-kasir-refresh]")) {
  setInterval(refreshKasirQueue, 10000);
}
