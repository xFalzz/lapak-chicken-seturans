<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
if (empty($_SESSION['branch_id'])) {
    $defaultBranch = $db->query('SELECT id, name FROM branches WHERE is_active = 1 ORDER BY id ASC LIMIT 1')->fetch();
    if ($defaultBranch) {
        $_SESSION['branch_id'] = (int) $defaultBranch['id'];
        $_SESSION['branch_name'] = $defaultBranch['name'];
    } else {
        $_SESSION['branch_id'] = 1;
        $_SESSION['branch_name'] = 'Lapak Chicken Seturan';
    }
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
            
            <aside style="width: 280px; flex-shrink:0;" class="hide-mobile">
                
                <label style="display:flex;align-items:center;gap:12px;padding:16px;background:white;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,0.03);margin-bottom:32px;cursor:pointer;">
                    <div style="width:32px;height:32px;background:var(--primary-container);border-radius:50%;display:grid;place-items:center;color:var(--on-surface);">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <span style="font-weight:700;font-size:1.05rem;">Promo Spesial</span>
                    <input type="checkbox" style="margin-left:auto;accent-color:var(--primary-container);width:20px;height:20px;">
                </label>

                <h3 style="font-size:1.15rem;margin-bottom:16px;font-weight:800;color:var(--on-surface);">Kategori Utama</h3>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:8px;margin-bottom:32px;" id="categorySidebar">
                    <li>
                        <button class="menu-sidebar-link active" data-filter-category="all" style="padding:12px 16px;width:100%;text-align:left;border-radius:12px;font-weight:600;">
                            Semua Menu
                        </button>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <button class="menu-sidebar-link" data-filter-category="<?= (int) $cat['id'] ?>" style="padding:12px 16px;width:100%;text-align:left;border-radius:12px;font-weight:600;display:flex;justify-content:space-between;align-items:center;">
                                <?= e($cat['name']) ?>
                                <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;color:var(--outline);"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h3 style="font-size:1.15rem;margin-bottom:16px;font-weight:800;color:var(--on-surface);">Kategori Populer</h3>
                <div style="display:flex;flex-wrap:wrap;gap:12px;">
                    <button class="chip-populer"><i class="fa-solid fa-bowl-food"></i> Rice Box</button>
                    <button class="chip-populer"><i class="fa-solid fa-drumstick-bite"></i> Ala Carte</button>
                    <button class="chip-populer"><i class="fa-solid fa-glass-water"></i> Minuman</button>
                    <button class="chip-populer"><i class="fa-solid fa-cookie"></i> Snack</button>
                </div>
            </aside>

            <div style="flex:1;">

                <div style="margin-bottom:32px;position:relative;display:flex;align-items:center;background:white;border-radius:99px;box-shadow:0 8px 24px rgba(0,0,0,0.04);padding:8px;border:1px solid var(--outline-variant);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left:24px;color:var(--secondary);font-size:1.1rem;"></i>
                    <input 
                        type="search" 
                        placeholder="Cari menu kesukaanmu (misal: 'Chicken')..." 
                        data-menu-search 
                        style="flex:1;border:none;background:transparent;padding:12px 24px;font-size:1.05rem;outline:none;">
                    <button type="button" class="btn btn-primary" style="border-radius:99px;padding:12px 32px;font-weight:700;">Cari</button>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                    <h2 id="searchResultText" style="font-size:1.4rem;font-weight:800;">Semua Menu</h2>
                    <span id="searchResultCount" style="color:var(--secondary);font-weight:600;font-size:0.95rem;"></span>
                </div>

                <div class="grid grid-3" data-menu-grid id="menuGrid">
                    <?php foreach ($menus as $menu):
                        $isAvailable = $menu['is_active'] && ($menu['stock'] === null || $menu['stock'] > 0);
                        $hasImage    = !empty($menu['image_url']);
                    ?>
                        <article
                            class="menu-card <?= $isAvailable ? '' : 'unavailable' ?>"
                            data-category="<?= (int) $menu['category_id'] ?>"
                            data-name="<?= e(strtolower($menu['name'])) ?>"
                            onclick="openMenuModal(<?= (int) $menu['id'] ?>)"
                            style="border-radius:24px;overflow:hidden;background:white;box-shadow:0 8px 24px rgba(0,0,0,0.03);border:1px solid var(--outline-variant);position:relative;cursor:pointer;">

                            <div class="menu-img-wrap" style="position:relative;height:180px;">
                                <?php if ($hasImage): ?>
                                    <img src="<?= e($menu['image_url']) ?>" alt="<?= e($menu['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <div class="placeholder-img" style="width:100%;height:100%;background:var(--surface-container);display:grid;place-items:center;"><i class="fa-solid fa-utensils"></i></div>
                                <?php endif; ?>
                                
                                <?php if ($menu['price'] > 20000): // Simulasi promo ?>
                                <div style="position:absolute;top:12px;left:12px;background:var(--primary-container);color:var(--on-surface);padding:4px 12px;border-radius:8px;font-size:0.75rem;font-weight:700;display:flex;align-items:center;gap:4px;">
                                    <i class="fa-solid fa-tag"></i> Promo Spesial
                                </div>
                                <?php endif; ?>

                                <?php if (!$isAvailable): ?>
                                    <div class="stock-badge" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.7);color:white;padding:8px 16px;border-radius:8px;font-weight:700;">Habis</div>
                                <?php endif; ?>
                            </div>

                            <div class="menu-card-content" style="padding:20px;">
                                <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:8px;line-height:1.3;"><?= e($menu['name']) ?></h3>
                                <p style="font-size:0.85rem;color:var(--secondary);display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:16px;">Ayam goreng renyah dengan sambal spesial yang diproses dengan bumbu rahasia.</p>
                                
                                <div class="menu-card-footer" style="display:flex;align-items:center;justify-content:space-between;">
                                    <span class="price" style="font-weight:800;font-size:1.15rem;color:var(--primary);"><?= format_rupiah((float) $menu['price']) ?></span>
                                    <?php if ($isAvailable): ?>
                                        <button type="button" style="width:40px;height:40px;border-radius:50%;background:var(--primary-container);color:var(--on-surface);border:none;display:grid;place-items:center;cursor:pointer;font-size:1.1rem;transition:transform 0.2s;">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <button disabled style="width:40px;height:40px;border-radius:50%;background:var(--surface-container);color:var(--secondary);border:none;display:grid;place-items:center;cursor:not-allowed;font-size:1.1rem;">
                                            <i class="fa-solid fa-plus"></i>
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
  background: rgba(0, 0, 0, 0.65) !important;
  backdrop-filter: blur(8px) !important;
  opacity: 0 !important;
  visibility: hidden !important;
  transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

.modal.show {
  opacity: 1 !important;
  visibility: visible !important;
}

#menuModal .modal-dialog {
  width: 100% !important;
  max-width: 1050px !important;
  max-height: 88vh !important;
  background: var(--surface) !important;
  border-radius: 32px !important;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25) !important;
  display: flex !important;
  flex-direction: column !important;
  overflow: hidden !important;
  transform: translateY(30px) scale(0.95) !important;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
}

#menuModal.show .modal-dialog {
  transform: translateY(0) scale(1) !important;
}

#addToCartForm {
  display: flex !important;
  flex-direction: row !important;
  flex: 1 !important;
  overflow: hidden !important;
  margin: 0 !important;
}

.detail-modal-left {
  flex: 1.25 !important;
  overflow-y: auto !important;
  padding: 32px !important;
  border-right: 1px solid var(--outline-variant) !important;
  background: var(--surface) !important;
}

.detail-modal-right {
  flex: 0.85 !important;
  display: flex !important;
  flex-direction: column !important;
  background: var(--surface-container-lowest, #ffffff) !important;
  overflow: hidden !important;
}

.detail-modal-right-scroll {
  flex: 1 !important;
  overflow-y: auto !important;
  padding: 32px !important;
}

.detail-bottom-bar {
  padding: 20px 32px !important;
  border-top: 1px solid var(--outline-variant) !important;
  background: var(--surface-container-lowest, #ffffff) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 20px !important;
  flex-shrink: 0 !important;
}

.detail-modal-left::-webkit-scrollbar,
.detail-modal-right-scroll::-webkit-scrollbar,
#addToCartForm::-webkit-scrollbar {
  width: 6px;
}
.detail-modal-left::-webkit-scrollbar-thumb,
.detail-modal-right-scroll::-webkit-scrollbar-thumb,
#addToCartForm::-webkit-scrollbar-thumb {
  background: var(--outline-variant);
  border-radius: 99px;
}

.sauce-card-label {
  border: 1px solid var(--outline-variant);
  border-radius: 16px;
  padding: 16px;
  cursor: pointer;
  position: relative;
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  background: var(--surface);
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.sauce-card-label:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}
.sauce-card-label:has(input:checked) {
  border: 2px solid var(--primary) !important;
  background: rgba(255, 253, 0, 0.1) !important;
  box-shadow: 0 4px 12px rgba(98, 97, 0, 0.08);
}

.spice-level-label {
  flex: 1;
  min-width: 44px;
  height: 48px;
  border-radius: 14px;
  border: 1px solid var(--outline-variant);
  display: grid;
  place-items: center;
  cursor: pointer;
  font-weight: 800;
  font-size: 1rem;
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  background: var(--surface);
  color: var(--secondary);
}
.spice-level-label:hover {
  border-color: var(--primary);
  transform: scale(1.05);
}
.spice-level-label:has(input:checked) {
  border: 2px solid var(--primary) !important;
  background: var(--primary-container) !important;
  color: var(--on-primary-container, #1d1d00) !important;
  box-shadow: 0 4px 12px rgba(255, 253, 0, 0.3);
  transform: scale(1.05);
}

@media (max-width: 768px) {
  #menuModal {
    padding: 0 !important;
    align-items: flex-end !important;
  }
  #menuModal .modal-dialog {
    max-width: 100% !important;
    max-height: 92vh !important;
    border-radius: 28px 28px 0 0 !important;
    margin: 0 !important;
  }
  #addToCartForm {
    flex-direction: column !important;
    overflow-y: auto !important;
  }
  .detail-modal-left {
    flex: none !important;
    overflow: visible !important;
    padding: 24px !important;
    border-right: none !important;
    border-bottom: 8px solid var(--surface-container-low, #f4f3f3) !important;
  }
  .detail-modal-right {
    flex: none !important;
    overflow: visible !important;
  }
  .detail-modal-right-scroll {
    overflow: visible !important;
    padding: 24px !important;
  }
  .detail-bottom-bar {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 20 !important;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.06) !important;
    padding: 16px 24px !important;
  }
}
</style>

<div class="mobile-cats container">
    <button class="chip active" type="button" data-filter-category="all">Semua Menu</button>
    <?php foreach ($categories as $cat): ?>
        <button class="chip" type="button" data-filter-category="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></button>
    <?php endforeach; ?>
</div>

<div class="modal" id="menuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none; height:100%; display:flex; flex-direction:column; overflow:hidden; width:100%;">

            <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 32px; border-bottom:1px solid var(--outline-variant); background:var(--surface); flex-shrink:0;">
                <div style="font-size:0.95rem; font-weight:600; color:var(--secondary);">
                    Beranda <span style="margin:0 8px;">/</span> Menu <span style="margin:0 8px;">/</span> <span id="modalBreadcrumb" style="color:var(--on-surface); font-weight:700;">Menu</span>
                </div>
                <button type="button" onclick="closeModal('menuModal')" style="background:var(--surface-container); border:none; width:40px; height:40px; border-radius:50%; cursor:pointer; font-size:1.1rem; color:var(--on-surface); display:grid; place-items:center; transition:all 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="addToCartForm">
                <input type="hidden" name="menu_id" id="modalMenuId">

                <div class="detail-modal-left">

                    <div style="position:relative; border-radius:24px; overflow:hidden; margin-bottom:28px; height:340px; background:var(--surface-container);">
                        <img id="modalImg" src="" alt="Menu" style="width:100%; height:100%; object-fit:cover; display:none;">
                        <div style="position:absolute;top:16px;left:16px;background:var(--primary-container);color:var(--on-primary-container, #1d1d00);padding:6px 16px;border-radius:99px;font-size:0.85rem;font-weight:800;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                            <i class="fa-solid fa-star"></i> Bestseller
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; gap:16px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <h2 id="modalTitle" style="font-size:2.2rem; font-weight:900; margin-bottom:8px; line-height:1.2; color:var(--on-surface); letter-spacing:-0.02em;">Nama Menu</h2>
                            <div style="display:flex; align-items:center; gap:12px; font-size:0.95rem; font-weight:600; color:var(--secondary); flex-wrap:wrap;">
                                <span style="display:flex; align-items:center; gap:4px; color:var(--on-surface); background:var(--surface-container-low); padding:4px 12px; border-radius:99px;">
                                    <i class="fa-solid fa-star" style="color:#f59e0b;"></i> <strong style="color:var(--on-surface);">4.9</strong> <span style="color:var(--secondary);font-weight:normal;">(100+ Ulasan)</span>
                                </span>
                                <span>•</span>
                                <span style="display:flex; align-items:center; gap:6px;"><i class="fa-regular fa-clock"></i> 15-20 menit</span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="text-decoration:line-through; color:var(--secondary); font-size:1.05rem; margin-bottom:2px;" id="modalPriceOriginal"></div>
                            <div id="modalPrice" style="font-size:1.85rem; font-weight:900; color:var(--primary);">Rp0</div>
                        </div>
                    </div>

                    <div style="background:var(--surface-container-low); padding:24px; border-radius:20px; margin-bottom:24px; border:1px solid rgba(202, 200, 170, 0.4);">
                        <h4 style="font-size:1.05rem; font-weight:800; margin-bottom:8px; color:var(--on-surface);">Deskripsi</h4>
                        <p id="modalDesc" style="color:var(--secondary); font-size:0.95rem; line-height:1.6; margin-bottom:16px;">Deskripsi menu</p>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="background:var(--surface-container); padding:8px 16px; border-radius:99px; font-size:0.85rem; font-weight:700; color:var(--on-surface); display:inline-flex; align-items:center; gap:8px;"><i class="fa-solid fa-fire-flame-curved" style="color:#e11d48;"></i> High Protein</span>
                            <span style="background:var(--surface-container); padding:8px 16px; border-radius:99px; font-size:0.85rem; font-weight:700; color:var(--on-surface); display:inline-flex; align-items:center; gap:8px;"><i class="fa-solid fa-leaf" style="color:#16a34a;"></i> Fresh Ingredients</span>
                        </div>
                    </div>

                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:16px;">
                            <div>
                                <h4 style="font-size:1.15rem; font-weight:800; color:var(--on-surface);">Pelengkap Sempurna</h4>
                                <p style="font-size:0.9rem; color:var(--secondary);">Cobain menu lainnya yang nggak kalah hits!</p>
                            </div>
                            <span style="font-size:0.9rem; font-weight:700; color:var(--primary); cursor:pointer; display:flex; align-items:center; gap:4px;">Lihat Semua <i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                        
                        <div style="display:flex; gap:16px; overflow-x:auto; padding-bottom:12px;" class="hide-scrollbar">
                            <div style="min-width:170px; background:var(--surface-container-lowest, #fff); border:1px solid var(--outline-variant); border-radius:20px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.03); display:flex; flex-direction:column; transition:transform 0.2s;">
                                <div style="height:110px; width:100%; background:var(--surface-container); overflow:hidden;">
                                    <img src="https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=300&q=80" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <div style="padding:14px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                                    <div>
                                        <h5 style="font-weight:700; font-size:0.95rem; margin-bottom:4px; color:var(--on-surface);">Iced Lemon Tea</h5>
                                        <div style="font-weight:800; color:var(--primary); font-size:0.95rem; margin-bottom:12px;">Rp 12.000</div>
                                    </div>
                                    <button type="button" class="btn" style="width:100%; padding:8px; font-size:0.85rem; font-weight:700; border-radius:10px; background:var(--surface-container); color:var(--on-surface); border:none; cursor:pointer;">+ Add</button>
                                </div>
                            </div>
                            <div style="min-width:170px; background:var(--surface-container-lowest, #fff); border:1px solid var(--outline-variant); border-radius:20px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.03); display:flex; flex-direction:column; transition:transform 0.2s;">
                                <div style="height:110px; width:100%; background:var(--surface-container); overflow:hidden;">
                                    <img src="https://images.unsplash.com/photo-1576107232684-1279f390859f?auto=format&fit=crop&w=300&q=80" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <div style="padding:14px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                                    <div>
                                        <h5 style="font-weight:700; font-size:0.95rem; margin-bottom:4px; color:var(--on-surface);">French Fries Large</h5>
                                        <div style="font-weight:800; color:var(--primary); font-size:0.95rem; margin-bottom:12px;">Rp 18.500</div>
                                    </div>
                                    <button type="button" class="btn" style="width:100%; padding:8px; font-size:0.85rem; font-weight:700; border-radius:10px; background:var(--surface-container); color:var(--on-surface); border:none; cursor:pointer;">+ Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-modal-right">
                    
                    <div class="detail-modal-right-scroll">
                        
                        <div id="sauceSelection" style="margin-bottom:28px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <h4 style="font-size:1.1rem; font-weight:800; color:var(--on-surface);">Pilih Saus</h4>
                                <span style="background:var(--surface-container-high); color:var(--on-surface-variant); font-size:0.75rem; font-weight:800; padding:4px 10px; border-radius:6px; letter-spacing:0.05em; text-transform:uppercase;">Wajib</span>
                            </div>
                            
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <?php foreach ($sauces as $s): ?>
                                    <label class="sauce-card-label">
                                        <input type="radio" name="sauce_id" value="<?= $s['id'] ?>" style="position:absolute; opacity:0; width:0; height:0;">
                                        <div style="font-weight:700; font-size:0.95rem; color:var(--on-surface);"><?= e($s['name']) ?></div>
                                        <div style="font-size:0.8rem; color:var(--secondary); font-weight:600;"><?= $s['price_extra'] > 0 ? '+ ' . format_rupiah((float)$s['price_extra']) : 'Gratis' ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="spiceSelection" style="margin-bottom:28px;">
                            <h4 style="font-size:1.1rem; font-weight:800; color:var(--on-surface); margin-bottom:14px;">Level Pedas</h4>
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <?php foreach ([0, 1, 2, 3, 4, 'MAX'] as $lvl): ?>
                                    <label class="spice-level-label">
                                        <input type="radio" name="spice_level" value="<?= $lvl ?>" <?= $lvl === 0 ? 'checked' : '' ?> style="position:absolute; opacity:0; width:0; height:0;">
                                        <?= $lvl ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div style="margin-bottom:8px;">
                            <h4 style="font-size:1.1rem; font-weight:800; color:var(--on-surface); margin-bottom:14px;">Catatan Tambahan</h4>
                            <textarea name="notes" placeholder="Contoh: Pisahkan sausnya ya..." style="width:100%; min-height:80px; border-radius:16px; border:1px solid var(--outline-variant); padding:16px; font-family:inherit; font-size:0.95rem; resize:vertical; background:var(--surface-container-low); outline:none; transition:all 0.2s;"></textarea>
                        </div>
                    </div>

                    <div class="detail-bottom-bar">
                        
                        <div class="qty-pill" style="display:flex; align-items:center; gap:14px; background:var(--surface-container-high); padding:6px 14px; border-radius:99px; border:1px solid rgba(202, 200, 170, 0.6);">
                            <button type="button" class="btn-qty" id="btnMinus" style="border:none; background:white; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:1rem; color:var(--on-surface); display:grid; place-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.06); transition:all 0.2s;"><i class="fa-solid fa-minus"></i></button>
                            <span id="qtyDisplay" style="font-size:1.15rem; font-weight:800; width:24px; text-align:center; color:var(--on-surface);">1</span>
                            <button type="button" class="btn-qty" id="btnPlus" style="border:none; background:white; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:1rem; color:var(--primary); display:grid; place-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.06); transition:all 0.2s;"><i class="fa-solid fa-plus"></i></button>
                            <input type="hidden" name="quantity" id="qtyInput" value="1">
                        </div>

                        <div style="flex:1; text-align:right;">
                            <div style="font-size:0.8rem; font-weight:700; color:var(--secondary); margin-bottom:2px; text-transform:uppercase; letter-spacing:0.05em;">Subtotal</div>
                            <div style="font-size:1.4rem; font-weight:900; color:var(--on-surface); margin-bottom:12px;" id="modalTotalPrice">Rp0</div>
                            <button type="submit" class="btn btn-primary" style="width:100%; padding:16px 24px; border-radius:18px; font-size:1.05rem; font-weight:800; display:flex; align-items:center; justify-content:center; gap:10px; box-shadow:0 8px 24px rgba(255, 253, 0, 0.25); transition:all 0.2s;" id="btnAddCart">
                                <i class="fa-solid fa-cart-shopping"></i> Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
            catLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll(`[data-filter-category="${currentCat}"]`).forEach(el => el.classList.add('active'));
            
            filterMenus();
        });
    });
});
const menus = <?= json_encode($menus) ?>;
const modal = document.getElementById('menuModal');
const qtyInput = document.getElementById('qtyInput');
const qtyDisplay = document.getElementById('qtyDisplay');
const modalTotalPrice = document.getElementById('modalTotalPrice');

let currentMenuPrice = 0;

function updateModalTotal() {
    let qty = parseInt(qtyInput.value) || 1;
    let saucePrice = 0;
    const selectedSauce = document.querySelector('input[name="sauce_id"]:checked');
    if (selectedSauce) {
    }
    const sauces = <?= json_encode($sauces) ?>;
    if(selectedSauce) {
        const s = sauces.find(x => x.id == selectedSauce.value);
        if(s) saucePrice = parseFloat(s.price_extra);
    }

    const total = (currentMenuPrice + saucePrice) * qty;
    modalTotalPrice.textContent = new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(total);
}

function openMenuModal(id) {
    const menu = menus.find(m => m.id == id);
    if(!menu) return;

    currentMenuPrice = parseFloat(menu.price);
    
    document.getElementById('modalMenuId').value = menu.id;
    document.getElementById('modalTitle').textContent = menu.name;
    document.getElementById('modalBreadcrumb').textContent = menu.name;
    document.getElementById('modalDesc').textContent = menu.description || 'Deskripsi menu belum tersedia.';
    document.getElementById('modalPrice').textContent = new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(menu.price);
    const origPrice = currentMenuPrice + 10000;
    document.getElementById('modalPriceOriginal').textContent = new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(origPrice);
    
    const img = document.getElementById('modalImg');
    if(menu.image_url) {
        img.src = menu.image_url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
    document.getElementById('sauceSelection').style.display = (menu.category_name && menu.category_name.toLowerCase().includes('ayam')) ? 'block' : 'none';
    document.querySelectorAll('input[name="sauce_id"]').forEach(r => r.checked = false);
    
    qtyInput.value = 1;
    qtyDisplay.textContent = 1;
    updateModalTotal();

    modal.classList.add('show');
}
document.querySelectorAll('input[name="sauce_id"]').forEach(radio => {
    radio.addEventListener('change', updateModalTotal);
});

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.getElementById('btnMinus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    if (val > 1) {
        val--;
        qtyInput.value = val;
        qtyDisplay.textContent = val;
        updateModalTotal();
    }
});

document.getElementById('btnPlus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || 1;
    if (val < 99) {
        val++;
        qtyInput.value = val;
        qtyDisplay.textContent = val;
        updateModalTotal();
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
