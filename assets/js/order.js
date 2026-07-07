document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-checkout-form]");
  if (!form) return;
  event.preventDefault();
  
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) submitBtn.classList.add('loading');
  
  const data = Object.fromEntries(new FormData(form).entries());
  try {
    const order = await apiFetch("api/order.php?action=create", { method: "POST", body: JSON.stringify(data) });
    window.location.href = `${window.APP.baseUrl}/customer/order-status.php?code=${encodeURIComponent(order.order_code)}`;
  } catch (error) {
    if (submitBtn) submitBtn.classList.remove('loading');
    toast(error.message, "error");
  }
});

async function pollOrderStatus() {
  const root = qs("[data-order-code]");
  if (!root) return;
  
  try {
    const order = await apiFetch(`api/order.php?action=status&code=${encodeURIComponent(root.dataset.orderCode)}`);
    if (order.status === 'cancelled') {
        const progressLine = qs('[data-stepper-progress]');
        if (progressLine) {
            progressLine.style.background = 'var(--danger)';
            progressLine.style.width = '0%';
        }
        
        qsa('[data-step]').forEach(step => {
            step.className = 'step cancelled';
        });
        
        const badge = qs("[data-live-status]");
        if (badge) {
            badge.className = 'badge badge-red';
            badge.textContent = order.status_label;
        }
        return;
    }
    
    const currentIndex = order.progress_index;
    const totalSteps = qsa("[data-step]").length;
    const progressLine = qs('[data-stepper-progress]');
    if (progressLine) {
        progressLine.style.background = 'var(--primary)';
        progressLine.style.width = `${(currentIndex / (totalSteps - 1)) * 100}%`;
    }
    qsa("[data-step]").forEach((step) => {
        const stepIndex = parseInt(step.dataset.stepIndex);
        if (order.status === 'completed' || stepIndex < currentIndex) {
            step.className = 'step completed';
        } else if (stepIndex === currentIndex) {
            step.className = 'step active';
        } else {
            step.className = 'step';
        }
    });
    const badge = qs("[data-live-status]");
    if (badge) {
        badge.textContent = order.status_label;
        if (order.status === 'completed') badge.className = 'badge badge-green';
        else if (order.status === 'ready') badge.className = 'badge badge-orange';
        else badge.className = 'badge badge-black';
    }
    if (order.status === 'completed' && !qs('form[action*="review"]')) {
        setTimeout(() => window.location.reload(), 2000);
    }
    
  } catch (_) {}
}

if (qs("[data-order-code]")) {
  pollOrderStatus();
  setInterval(pollOrderStatus, 10000);
}
