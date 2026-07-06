<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
if (empty($_SESSION['branch_id'])) {
    flash('error', 'Pilih cabang dulu sebelum memesan');
    redirect(base_url('index.php'));
}
$categories = $db->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$sauces     = $db->query('SELECT * FROM sauces WHERE is_active = 1 ORDER BY price_extra, name')->fetchAll();
$menus      = $db->query('SELECT m.*, c.name category_name, c.icon FROM menus m JOIN categories c ON c.id = m.category_id ORDER BY c.name, m.name')->fetchAll();
$pageTitle  = 'Menu ' . ($_SESSION['branch_name'] ?? '');
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding:40px 0;">
    <div class="container">
        
        <div style="display:flex;gap:40px;">
            <!-- Left Sidebar: Categories -->
            <aside style="width: 250px; flex-shrink:0;" class="hide-mobile">
                <h3 style="font-size:1.15rem;margin-bottom:20px;font-weight:800;color:var(--on-surface);">Kategori Menu</h3>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:8px;" id="categorySidebar">
                    <li>
                        <button class="menu-sidebar-link active" data-filter-category="all">
                            <i class="fa-solid fa-utensils"></i> Semua Menu
                        </button>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <button class="menu-sidebar-link" data-filter-category="<?= (int) $cat['id'] ?>">
                                <i class="<?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Right Content: Search & Menu Grid -->
            <div style="flex:1;">
                
                <!-- Search Bar -->
                <div style="margin-bottom:32px;position:relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:24px;top:50%;transform:translateY(-50%);color:var(--secondary);font-size:1.1rem;"></i>
                    <input 
                        type="search" 
                        placeholder="Cari menu kesukaanmu..." 
                        data-menu-search 
                        style="width:100%;padding:18px 24px 18px 56px;border-radius:99px;border:1px solid var(--outline-variant);background:var(--surface);font-size:1.05rem;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
                </div>

                <!-- Menu Grid -->
                <div class="grid grid-3" data-menu-grid id="menuGrid">
                    <?php foreach ($menus as $menu):
                        $isAvailable = $menu['is_active'] && ($menu['stock'] === null || $menu['stock'] > 0);
                        $hasImage    = !empty($menu['image_url']);
                    ?>
                        <article
                            class="menu-card <?= $isAvailable ? '' : 'unavailable' ?>"
                            data-category="<?= (int) $menu['category_id'] ?>"
                            data-name="<?= e(strtolower($menu['name'])) ?>">

                            <div class="menu-img-wrap" onclick="openMenuModal(<?= (int) $menu['id'] ?>)" style="cursor:pointer;">
                                <?php if ($hasImage): ?>
                                    <img src="<?= e($menu['image_url']) ?>" alt="<?= e($menu['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="placeholder-img"><i class="fa-solid fa-utensils"></i></div>
                                <?php endif; ?>
                                
                                <?php if (!$isAvailable): ?>
                                    <div class="stock-badge">Habis</div>
                                <?php endif; ?>
                            </div>

                            <div class="menu-card-content">
                                <h3 onclick="openMenuModal(<?= (int) $menu['id'] ?>)" style="cursor:pointer;"><?= e($menu['name']) ?></h3>
                                <div class="menu-rating">
                                    <i class="fa-solid fa-star"></i> 4.8 <span>(120)</span>
                                </div>
                                
                                <div class="menu-card-footer">
                                    <span class="price"><?= format_rupiah((float) $menu['price']) ?></span>
                                    <?php if ($isAvailable): ?>
                                        <button class="btn-add-pill" onclick="openMenuModal(<?= (int) $menu['id'] ?>)" title="Pilih menu">
                                            Tambah +
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-add-pill" disabled style="opacity:0.5;cursor:not-allowed;background:var(--outline-variant);color:var(--secondary);">
                                            Habis
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div id="noMenuFound" style="display:none; text-align:center; padding:64px 20px;">
                    <div style="font-size:3rem; color:var(--outline); margin-bottom:16px;">
                        <i class="fa-solid fa-face-frown-open"></i>
                    </div>
                    <h3>Yahh, Menu Tidak Ditemukan</h3>
                    <p style="color:var(--secondary); margin-top:8px;">
                        Coba gunakan kata kunci lain atau pilih kategori yang berbeda.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mobile Categories (Visible only on mobile) -->
<style>
.hide-mobile { display: block; }
.mobile-cats { display: none; }
@media(max-width: 768px) {
    .hide-mobile { display: none !important; }
    .mobile-cats {
        display: flex;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 8px;
        margin-bottom: 24px;
    }
    .mobile-cats::-webkit-scrollbar { display: none; }
    .grid-3 { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
}

/* ============ PREMIUM MODAL WITH BLURRED BACKDROP ============ */
.modal {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  z-index: 1000 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 20px !important;
  background: rgba(0, 0, 0, 0.6) !important;
  backdrop-filter: blur(8px) !important;
  opacity: 0 !important;
  visibility: hidden !important;
  transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

.modal.show {
  opacity: 1 !important;
  visibility: visible !important;
}

.modal-dialog {
  width: 100% !important;
  max-width: 480px !important;
  max-height: 90vh !important;
  display: flex !important;
  flex-direction: column !important;
  background: var(--surface) !important;
  border-radius: 24px !important;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25) !important;
  transform: translateY(30px) scale(0.95) !important;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
  overflow: hidden !important;
}

.modal.show .modal-dialog {
  transform: translateY(0) scale(1) !important;
}

/* Modal Internal Layout Elements */
#addToCartForm {
  display: flex !important;
  flex-direction: column !important;
  overflow: hidden !important;
  flex: 1 !important;
  margin: 0 !important;
}

.detail-modal-body {
  overflow-y: auto !important;
  flex: 1 !important;
  padding: 24px !important;
  padding-top: 20px !important;
}

.detail-modal-body::-webkit-scrollbar {
  width: 6px;
}
.detail-modal-body::-webkit-scrollbar-thumb {
  background: var(--outline-variant);
  border-radius: 4px;
}

.detail-bottom-bar {
  padding: 16px 24px !important;
  border-top: 1px solid var(--outline-variant) !important;
  background: var(--surface) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 16px !important;
  flex-shrink: 0 !important;
}

.detail-modal-img {
  width: 100% !important;
  height: 280px !important;
  object-fit: cover !important;
  display: block !important;
  flex-shrink: 0 !important;
}

.btn-close-premium {
  position: absolute !important;
  top: 16px !important;
  right: 16px !important;
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(8px) !important;
  border-radius: 50% !important;
  width: 40px !important;
  height: 40px !important;
  display: grid !important;
  place-items: center !important;
  border: none !important;
  cursor: pointer !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
  z-index: 10 !important;
  transition: transform 0.2s ease !important;
}
.btn-close-premium:hover {
  transform: scale(1.05) !important;
}

/* Enhancing Sauces selection */
.sauce-label {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  padding: 16px !important;
  border: 1px solid var(--outline-variant) !important;
  border-radius: 12px !important;
  cursor: pointer !important;
  background: var(--surface) !important;
  transition: all 0.2s ease !important;
  margin-bottom: 0 !important;
}
.sauce-label:has(input:checked) {
  border: 2px solid var(--primary) !important;
  background: rgba(255, 253, 0, 0.05) !important; 
}
.sauce-label input {
  accent-color: var(--primary) !important;
  width: 20px !important;
  height: 20px !important;
  margin: 0 !important;
}
</style>

<div class="mobile-cats container">
    <button class="chip active" type="button" data-filter-category="all">Semua Menu</button>
    <?php foreach ($categories as $cat): ?>
        <button class="chip" type="button" data-filter-category="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></button>
    <?php endforeach; ?>
</div>

<!-- Modal Detail Menu -->
<div class="modal" id="menuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; height:100%; display:flex; flex-direction:column; overflow:hidden;">
            <div style="position:relative; flex-shrink:0;">
                <img id="modalImg" src="" alt="Menu" class="detail-modal-img" style="display:none;background:var(--surface-container);">
                <button type="button" class="btn-close-premium" onclick="closeModal('menuModal')">
                    <i class="fa-solid fa-xmark" style="color:var(--on-surface);"></i>
                </button>
            </div>
            
            <form id="addToCartForm" style="display:flex; flex-direction:column; flex:1; overflow:hidden; margin:0;">
                <input type="hidden" name="menu_id" id="modalMenuId">
                <div class="detail-modal-body">
                    <h2 id="modalTitle" style="font-size:1.6rem;margin-bottom:8px;font-weight:800;color:var(--on-surface);">Nama Menu</h2>
                    <div class="menu-rating" style="margin-bottom:12px;display:flex;align-items:center;gap:6px;font-size:0.9rem;color:var(--secondary);font-weight:600;">
                        <i class="fa-solid fa-star" style="color:var(--warning);"></i> 4.8 <span style="color:var(--muted);font-weight:normal;">(120 ulasan)</span>
                    </div>
                    <div id="modalPrice" style="font-size:1.4rem;font-weight:800;color:var(--on-surface);margin-bottom:16px;">Rp0</div>
                    <p id="modalDesc" style="color:var(--secondary);font-size:0.95rem;margin-bottom:24px;line-height:1.6;">Deskripsi menu</p>
                    
                    <div id="sauceSelection" style="display:none;margin-bottom:16px;">
                        <h4 style="font-size:1.05rem;margin-bottom:12px;font-weight:700;color:var(--on-surface);">Pilih Saus / Sambal</h4>
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <?php foreach ($sauces as $s): ?>
                                <label style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border:1px solid var(--outline-variant);border-radius:12px;cursor:pointer;background:var(--surface);" class="sauce-label">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <input type="radio" name="sauce_id" value="<?= $s['id'] ?>" style="accent-color:var(--primary-container);width:18px;height:18px;">
                                        <span style="font-weight:600;font-size:0.95rem;color:var(--on-surface);"><?= e($s['name']) ?></span>
                                    </div>
                                    <span style="color:var(--secondary);font-size:0.9rem;font-weight:600;"><?= $s['price_extra'] > 0 ? '+ ' . format_rupiah((float)$s['price_extra']) : 'Gratis' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="detail-bottom-bar">
                    <div class="qty-pill" style="display:flex;align-items:center;gap:16px;">
                        <button type="button" class="btn-qty" id="btnMinus" style="width:36px;height:36px;border-radius:50%;border:1px solid var(--outline);background:var(--surface);cursor:pointer;display:grid;place-items:center;"><i class="fa-solid fa-minus"></i></button>
                        <span id="qtyDisplay" style="font-size:1.2rem;font-weight:700;width:24px;text-align:center;">1</span>
                        <button type="button" class="btn-qty" id="btnPlus" style="width:36px;height:36px;border-radius:50%;border:1px solid var(--outline);background:var(--surface);cursor:pointer;display:grid;place-items:center;"><i class="fa-solid fa-plus"></i></button>
                        <input type="hidden" name="quantity" id="qtyInput" value="1">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding:14px 32px;border-radius:99px;font-size:1rem;font-weight:700;box-shadow:0 4px 12px rgba(255,253,0,0.2);" id="btnAddCart">
                        Tambah ke Keranjang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search & Filter Logic
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('[data-menu-search]');
    const catLinks    = document.querySelectorAll('[data-filter-category]');
    const menuCards   = document.querySelectorAll('.menu-card');
    const noMenu      = document.getElementById('noMenuFound');

    let currentCat = 'all';
    let currentSearch = '';

    function filterMenus() {
        let visibleCount = 0;
        menuCards.forEach(card => {
            const cat = card.dataset.category;
            const name = card.dataset.name;
            const matchCat = (currentCat === 'all' || cat === currentCat);
            const matchSearch = name.includes(currentSearch);

            if (matchCat && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noMenu.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput?.addEventListener('input', (e) => {
        currentSearch = e.target.value.toLowerCase().trim();
        filterMenus();
    });

    catLinks.forEach(link => {
        link.addEventListener('click', () => {
            currentCat = link.dataset.filterCategory;
            
            // Update active states
            catLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll(`[data-filter-category="${currentCat}"]`).forEach(el => el.classList.add('active'));
            
            filterMenus();
        });
    });
});

// Modal Logic
const menus = <?= json_encode($menus) ?>;
const modal = document.getElementById('menuModal');
const qtyInput = document.getElementById('qtyInput');
const qtyDisplay = document.getElementById('qtyDisplay');

function openMenuModal(id) {
    const menu = menus.find(m => m.id == id);
    if(!menu) return;

    document.getElementById('modalMenuId').value = menu.id;
    document.getElementById('modalTitle').textContent = menu.name;
    document.getElementById('modalDesc').textContent = menu.description || '';
    document.getElementById('modalPrice').textContent = new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(menu.price);
    
    const img = document.getElementById('modalImg');
    if(menu.image_url) {
        img.src = menu.image_url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    // Reset sauce and qty
    document.getElementById('sauceSelection').style.display = (menu.category_name && menu.category_name.toLowerCase().includes('ayam')) ? 'block' : 'none';
    document.querySelectorAll('input[name="sauce_id"]').forEach(r => r.checked = false);
    
    qtyInput.value = 1;
    qtyDisplay.textContent = 1;

    modal.classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.getElementById('btnMinus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    if (val > 1) {
        val--;
        qtyInput.value = val;
        qtyDisplay.textContent = val;
    }
});

document.getElementById('btnPlus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    if (val < 99) {
        val++;
        qtyInput.value = val;
        qtyDisplay.textContent = val;
    }
});

document.getElementById('addToCartForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnAddCart');
    btn.classList.add('loading');
    
    const formData = new FormData(e.target);
    try {
        const res = await apiFetch('api/cart.php?action=add', {
            method: 'POST',
            body: JSON.stringify(Object.fromEntries(formData))
        });
        toast('Berhasil ditambahkan ke keranjang!', 'success');
        refreshCartCount();
        closeModal('menuModal');
    } catch(err) {
        toast(err.message || 'Gagal menambahkan ke keranjang', 'error');
    } finally {
        btn.classList.remove('loading');
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
