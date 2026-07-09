const qs = (selector, root = document) => root.querySelector(selector);
const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

async function apiFetch(path, options = {}) {
  const headers = {
    "X-CSRF-Token": window.APP?.csrf || "",
    ...(options.headers || {})
  };
  if (!(options.body instanceof FormData)) headers["Content-Type"] = headers["Content-Type"] || "application/json";
  const response = await fetch(`${window.APP.baseUrl}/${path}`.replace(/([^:]\/)\/+/g, "$1"), { ...options, headers });
  const json = await response.json();
  if (!json.success) throw new Error(json.message || "Request gagal");
  return json.data;
}

function toast(message, variant = "success") {
  const root = qs("#toast-root") || document.body;
  const el = document.createElement("div");
  el.className = `toast ${variant}`;
  
  const iconMap = { success: 'fa-circle-check', error: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
  const icon = iconMap[variant] || 'fa-circle-info';
  
  el.innerHTML = `
    <i class="fa-solid ${icon} toast-icon"></i>
    <div class="toast-content">${message}</div>
    <button class="toast-close"><i class="fa-solid fa-times"></i></button>
    <div class="toast-progress"></div>
  `;
  
  root.appendChild(el);
  
  const closeBtn = el.querySelector('.toast-close');
  let timeoutId;
  
  const removeToast = () => {
      el.classList.add('hiding');
      el.addEventListener('animationend', () => el.remove());
  };
  
  closeBtn.addEventListener('click', () => {
      clearTimeout(timeoutId);
      removeToast();
  });
  
  timeoutId = setTimeout(removeToast, 4000);
}

function rupiah(value) {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(Number(value || 0));
}

async function refreshCartCount() {
  qsa("[data-cart-count]").forEach(async (count) => {
    try {
      const cart = await apiFetch("api/cart.php?action=get");
      count.textContent = cart.count;
    } catch (_) {}
  });
}
function initBannerCarousel() {
    const track = qs('#bannerTrack');
    const navs = qsa('.banner-dot');
    if (!track || navs.length === 0) return;
    
    let currentIndex = 0;
    const total = navs.length;
    let intervalId;
    
    const goToSlide = (index) => {
        currentIndex = index;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        navs.forEach(nav => nav.classList.remove('active'));
        navs[currentIndex].classList.add('active');
    };
    
    const nextSlide = () => {
        goToSlide((currentIndex + 1) % total);
    };
    
    const startAutoPlay = () => {
        clearInterval(intervalId);
        intervalId = setInterval(nextSlide, 5000);
    };
    
    navs.forEach((nav, idx) => {
        nav.addEventListener('click', () => {
            goToSlide(idx);
            startAutoPlay();
        });
    });
    
    startAutoPlay();
}

document.addEventListener("click", async (event) => {
  const sidebarBtn = event.target.closest("[data-toggle-sidebar]");
  if (sidebarBtn) qs(".sidebar")?.classList.toggle("open");
  const drawerBtn = event.target.closest("[data-toggle-drawer]");
  if (drawerBtn) qs("#mobileDrawer")?.classList.toggle("open");

  const logout = event.target.closest("[data-logout]");
  if (logout) {
    await apiFetch("api/auth.php?action=logout", { method: "POST", body: JSON.stringify({}) });
    window.location.href = `${window.APP.baseUrl}/customer/login.php`;
  }
});

document.addEventListener("DOMContentLoaded", () => {
    initBannerCarousel();
    qsa(".server-toast").forEach((el) => {
        const msg = el.textContent;
        const variant = el.classList.contains('success') ? 'success' : 'error';
        el.remove();
        toast(msg, variant);
    });
});
