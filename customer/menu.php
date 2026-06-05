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
$pageTitle = 'Menu';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="page-title">
            <h1>Menu <?= e($_SESSION['branch_name'] ?? '') ?></h1>
            <div class="form-field"><input type="search" placeholder="Cari menu..." data-menu-search></div>
        </div>
        <div class="tabs">
            <button class="tab active" type="button" data-filter-category="all">Semua</button>
            <?php foreach ($categories as $cat): ?>
                <button class="tab" type="button" data-filter-category="<?= (int) $cat['id'] ?>"><i class="<?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="grid grid-3 section" data-menu-grid>
            <?php foreach ($menus as $menu): ?>
                <article class="card menu-card <?= $menu['is_active'] ? '' : 'unavailable' ?>" data-category="<?= (int) $menu['category_id'] ?>" data-name="<?= e(strtolower($menu['name'])) ?>">
                    <span class="badge badge-gray"><i class="<?= e($menu['icon']) ?>"></i><?= e($menu['category_name']) ?></span>
                    <h3><?= e($menu['name']) ?></h3>
                    <p class="muted"><?= e($menu['description']) ?></p>
                    <span class="price"><?= format_rupiah((float) $menu['price']) ?></span>
                    <?php if ($menu['is_active']): ?>
                        <form data-add-cart class="form-grid">
                            <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                            <div class="menu-actions">
                                <select name="sauce_id" aria-label="Pilih saus">
                                    <option value="">Tanpa saus</option>
                                    <?php foreach ($sauces as $sauce): ?>
                                        <option value="<?= (int) $sauce['id'] ?>"><?= e($sauce['name']) ?> <?= (float) $sauce['price_extra'] > 0 ? '+ ' . format_rupiah((float) $sauce['price_extra']) : '(Gratis)' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="quantity" min="1" value="1" aria-label="Jumlah">
                            </div>
                            <textarea name="notes" placeholder="Catatan item (opsional)"></textarea>
                            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus"></i>Tambah ke Keranjang</button>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-red">Habis</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/cart.js') ?>"></script>
<script>
qsa('[data-filter-category]').forEach(btn => btn.addEventListener('click', () => {
  qsa('[data-filter-category]').forEach(item => item.classList.remove('active'));
  btn.classList.add('active');
  const cat = btn.dataset.filterCategory;
  qsa('[data-menu-grid] .menu-card').forEach(card => card.style.display = cat === 'all' || card.dataset.category === cat ? '' : 'none');
}));
qs('[data-menu-search]')?.addEventListener('input', (e) => {
  const term = e.target.value.toLowerCase();
  qsa('[data-menu-grid] .menu-card').forEach(card => card.style.display = card.dataset.name.includes(term) ? '' : 'none');
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
