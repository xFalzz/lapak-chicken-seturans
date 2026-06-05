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
  el.textContent = message;
  root.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

function rupiah(value) {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(Number(value || 0));
}

async function refreshCartCount() {
  const count = qs("[data-cart-count]");
  if (!count) return;
  try {
    const cart = await apiFetch("api/cart.php?action=get");
    count.textContent = cart.count;
  } catch (_) {}
}

document.addEventListener("click", async (event) => {
  const sidebarBtn = event.target.closest("[data-toggle-sidebar]");
  if (sidebarBtn) qs("[data-sidebar]")?.classList.toggle("open");

  const logout = event.target.closest("[data-logout]");
  if (logout) {
    await apiFetch("api/auth.php?action=logout", { method: "POST", body: JSON.stringify({}) });
    window.location.href = `${window.APP.baseUrl}/customer/login.php`;
  }
});

setTimeout(() => qsa(".server-toast").forEach((el) => el.remove()), 3000);
