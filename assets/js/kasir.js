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
  
  const submitBtn = form.querySelector('button[type="submit"], button:not([type])');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
  }
  
  const data = Object.fromEntries(new FormData(form).entries());
  try {
    const result = await apiFetch("api/status.php?action=pay", { method: "POST", body: JSON.stringify(data) });
    window.location.href = `${window.APP.baseUrl}/kasir/receipt.php?order_id=${result.order_id}`;
  } catch (error) {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Konfirmasi Pembayaran';
    }
    toast(error.message || "Gagal memproses pembayaran", "error");
  }
});

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
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

async function refreshKasirQueue() {
  const container = qs("[data-kasir-refresh]");
  if (!container) return;
  const branchId = container.dataset.branchId;
  try {
    const orders = await apiFetch(`api/order.php?action=list&branch_id=${branchId}`);
    
    const readyQueue = qs("[data-queue-ready]");
    const activeQueue = qs("[data-queue-active]");
    
    if (!readyQueue || !activeQueue) return;
    const readyOrders = orders.filter(o => o.status === 'ready');
    const activeOrders = orders.filter(o => ['confirmed', 'cooking'].includes(o.status));
    const readyCountEl = qs("[data-ready-count]");
    const activeCountEl = qs("[data-active-count]");
    if (readyCountEl) readyCountEl.textContent = readyOrders.length;
    if (activeCountEl) activeCountEl.textContent = activeOrders.length;
    if (readyOrders.length === 0) {
      readyQueue.innerHTML = `
        <div class="empty-state">
          <i class="fa-solid fa-inbox"></i>
          <h3>Belum ada pesanan siap bayar</h3>
          <p>Pesanan akan muncul setelah dapur menyelesaikan masakan</p>
        </div>
      `;
    } else {
      readyQueue.innerHTML = readyOrders.map(o => `
        <article class="card order-ticket" data-order-ticket-id="${o.id}">
          <div class="code">${escapeHtml(o.order_code)}</div>
          <p class="ticket-meta"><i class="fa-solid fa-user"></i> ${escapeHtml(o.customer_name)}</p>
          <p class="ticket-meta"><i class="fa-solid fa-tag"></i> ${escapeHtml(o.order_type)} • ${escapeHtml(String(o.items_count || '0'))} item</p>
          <p class="ticket-meta"><i class="fa-regular fa-clock"></i> ${timeAgo(o.created_at)}</p>
          <div class="ticket-total">${rupiah(Number(o.total))}</div>
          <a class="btn btn-primary" href="process.php?order_id=${o.id}" style="width:100%;justify-content:center;border-radius:var(--radius-md);margin-top:4px;">
            <i class="fa-solid fa-credit-card"></i> Proses Pembayaran
          </a>
        </article>
      `).join('');
    }
    if (activeOrders.length === 0) {
      activeQueue.innerHTML = `
        <div class="empty-state">
          <i class="fa-solid fa-fire-flame-curved"></i>
          <h3>Tidak ada pesanan diproses</h3>
          <p>Pesanan yang dikonfirmasi atau sedang dimasak akan muncul di sini</p>
        </div>
      `;
    } else {
      activeQueue.innerHTML = activeOrders.map(o => `
        <article class="card order-ticket" data-order-ticket-id="${o.id}">
          <div class="code">${escapeHtml(o.order_code)}</div>
          <p class="ticket-meta"><i class="fa-solid fa-user"></i> ${escapeHtml(o.customer_name)}</p>
          <p class="ticket-meta"><i class="fa-solid fa-spinner fa-spin"></i> ${getStatusLabel(o.status)}</p>
          <p class="ticket-meta"><i class="fa-regular fa-clock"></i> ${timeAgo(o.created_at)}</p>
          <div class="ticket-total">${rupiah(Number(o.total))}</div>
        </article>
      `).join('');
    }
  } catch (err) {
    console.error("Failed to refresh cashier queue:", err);
  }
}

if (qs("[data-kasir-refresh]")) {
  setInterval(refreshKasirQueue, 10000);
}
