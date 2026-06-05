<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
if (empty($_SESSION['branch_id'])) {
    flash('error', 'Pilih cabang dulu sebelum memesan');
    redirect(base_url('index.php'));
}
$categories = $db->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$sauces = $db->query('SELECT * FROM sauces WHERE is_active = 1 ORDER BY price_extra, name')->fetchAll();
$menus = $db->query('SELECT m.*, c.name category_name, c.icon FROM menus m JOIN categories c ON c.id = m.category_id ORDER BY c.name, m.name')->fetchAll();
$pageTitle = 'Menu ' . $_SESSION['branch_name'];
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        
        <div class="tabs">
            <button class="tab active" type="button" data-filter-category="all">
                <i class="fa-solid fa-list"></i>
                Semua
            </button>
            <?php foreach ($categories as $cat): ?>
                <button class="tab" type="button" data-filter-category="<?= (int) $cat['id'] ?>">
                    <i class="<?= e($cat['icon']) ?>"></i>
                    <?= e($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 32px 0 24px;">
            <h1 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Pilihan Menu</h1>
            <div style="position: relative; max-width: 300px; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--gray);"></i>
                <input type="search" placeholder="Cari menu..." data-menu-search style="width: 100%; border: 1px solid var(--gray-border); border-radius: var(--radius-pill); padding: 12px 16px 12px 40px; font-size: 0.95rem;">
            </div>
        </div>
        
        <div class="grid grid-4" data-menu-grid>
            <?php foreach ($menus as $menu): 
                $isAvailable = $menu['is_active'] && ($menu['stock'] === null || $menu['stock'] > 0);
            ?>
                <article class="menu-card <?= $isAvailable ? '' : 'unavailable' ?>" data-category="<?= (int) $menu['category_id'] ?>" data-name="<?= e(strtolower($menu['name'])) ?>">
                    <div class="menu-img-wrap">
                        <?php if (!empty($menu['image_url'])): ?>
                            <img src="<?= e($menu['image_url']) ?>" alt="<?= e($menu['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div style="position: absolute; inset: 0; display: grid; place-items: center; font-size: 3rem; color: var(--gray); background: var(--gray-light);">
                                <i class="<?= e($menu['icon']) ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-card-content">
                        <h3><?= e($menu['name']) ?></h3>
                        <p title="<?= e($menu['description']) ?>"><?= e($menu['description']) ?></p>
                        <span class="price"><?= format_rupiah((float) $menu['price']) ?></span>
                        <?php if ($menu['stock'] !== null && $menu['stock'] > 0): ?>
                            <span class="stock-badge" style="font-size: 0.8rem; color: var(--primary); margin-top: 4px; display: block; font-weight: 500;">Stok: <?= (int)$menu['stock'] ?> porsi</span>
                        <?php endif; ?>
                        
                        <?php if ($isAvailable): ?>
                            <form data-add-cart class="menu-actions">
                                <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                                <div style="display: grid; grid-template-columns: 1fr 64px; gap: 8px;">
                                    <select name="sauce_id" aria-label="Pilih saus" style="border-radius: var(--radius-sm); border: 1px solid var(--gray-border); background: var(--white); font-size: 0.9rem;">
                                        <option value="">Pilih saus...</option>
                                        <?php foreach ($sauces as $sauce): ?>
                                            <option value="<?= (int) $sauce['id'] ?>"><?= e($sauce['name']) ?> <?= (float) $sauce['price_extra'] > 0 ? '+ ' . format_rupiah((float) $sauce['price_extra']) : '' ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="quantity" min="1" value="1" aria-label="Jumlah" style="border-radius: var(--radius-sm); border: 1px solid var(--gray-border); text-align: center; padding: 0;">
                                </div>
                                <textarea name="notes" placeholder="Catatan (opsional)" style="min-height: 48px; font-size: 0.85rem; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--gray-border); resize: none;"></textarea>
                                <button class="btn btn-outline" type="submit" style="width: 100%; border-radius: var(--radius-pill); font-size: 0.95rem; padding: 10px;">
                                    Tambah
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="margin-top: auto; padding-top: 16px;">
                                <span style="display: block; text-align: center; padding: 10px; font-weight: 600; color: var(--danger); font-size: 0.95rem;">Habis Terjual</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script src="<?= base_url('assets/js/cart.js') ?>?v=1.2"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  qsa('[data-filter-category]').forEach(btn => btn.addEventListener('click', () => {
    qsa('[data-filter-category]').forEach(item => item.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.filterCategory;
    qsa('[data-menu-grid] .menu-card').forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
  }));

  qs('[data-menu-search]')?.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    qsa('[data-menu-grid] .menu-card').forEach(card => {
        if (card.dataset.name.includes(term)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
  });
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
