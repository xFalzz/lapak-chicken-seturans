<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['kasir', 'admin']);
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $branchId = (int) ($_POST['branch_id'] ?? 1);
    $customerName = sanitize($_POST['customer_name'] ?? 'Pelanggan POS');
    $orderType = sanitize($_POST['order_type'] ?? 'dine_in');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
    $paymentStatus = sanitize($_POST['payment_status'] ?? 'paid');
    
    $cartData = json_decode($_POST['cart_data'] ?? '[]', true);
    if (empty($cartData) || !is_array($cartData)) {
        flash('error', 'Pesanan kosong! Silakan pilih menu terlebih dahulu.');
        redirect('pos.php');
    }

    $total = 0;
    foreach ($cartData as $item) {
        $total += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1));
    }

    $orderCode = 'LC-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
    
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO orders (user_id, branch_id, order_code, customer_name, customer_phone, order_type, status, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([current_user()['id'], $branchId, $orderCode, $customerName, '-', $orderType, 'confirmed', $total]);
        $orderId = (int) $db->lastInsertId();

        $stmtDetail = $db->prepare('INSERT INTO order_details (order_id, menu_id, sauce_id, quantity, price, subtotal, notes, spice_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($cartData as $item) {
            $menuId = (int) $item['id'];
            $sauceId = !empty($item['sauce_id']) ? (int) $item['sauce_id'] : null;
            $qty = (int) $item['qty'];
            $price = (float) $item['price'];
            $subtotal = $price * $qty;
            $notes = sanitize($item['notes'] ?? '');
            $spiceLevel = sanitize($item['spice_level'] ?? '0');

            $stmtDetail->execute([$orderId, $menuId, $sauceId, $qty, $price, $subtotal, $notes, $spiceLevel]);
        }

        $paidAt = $paymentStatus === 'paid' ? date('Y-m-d H:i:s') : null;
        $stmtPay = $db->prepare('INSERT INTO payments (order_id, payment_method, amount_paid, payment_status, paid_at, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmtPay->execute([$orderId, $paymentMethod, $total, $paymentStatus, $paidAt]);

        $db->commit();
        flash('success', 'Pesanan POS berhasil dibuat! Kode: ' . $orderCode);
        redirect('receipt.php?id=' . $orderId);
    } catch (Exception $e) {
        $db->rollBack();
        flash('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        redirect('pos.php');
    }
}

$branches = branch_options($db);
$menusStmt = $db->query('SELECT m.*, c.name category_name FROM menus m LEFT JOIN categories c ON c.id = m.category_id WHERE m.is_active = 1 ORDER BY c.name ASC, m.name ASC');
$menus = $menusStmt->fetchAll();
$saucesStmt = $db->query('SELECT * FROM sauces WHERE is_active = 1 ORDER BY name ASC');
$sauces = $saucesStmt->fetchAll();

$pageTitle = 'Buat Pesanan (POS)';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>

<section class="content-with-sidebar">
    <div class="page-title" style="margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--on-surface);">Point of Sale (POS)</h1>
            <p style="color: var(--secondary); font-size: 0.95rem;">Input pesanan pelanggan secara cepat di meja kasir</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
        <div>
            <div class="chip-row" style="margin-bottom: 20px;">
                <button type="button" class="chip active" onclick="filterCategory('all', this)">Semua Menu</button>
                <button type="button" class="chip" onclick="filterCategory('Makanan', this)">Makanan</button>
                <button type="button" class="chip" onclick="filterCategory('Minuman', this)">Minuman</button>
                <button type="button" class="chip" onclick="filterCategory('Paket', this)">Paket</button>
                <button type="button" class="chip" onclick="filterCategory('Camilan', this)">Camilan</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;" id="menuGrid">
                <?php foreach ($menus as $m): ?>
                    <div class="card menu-pos-card" data-category="<?= e($m['category_name'] ?? 'Lainnya') ?>" style="padding: 16px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--outline-variant); cursor: pointer; transition: all 0.2s;" onclick='addToCart(<?= json_encode([
                        "id" => (int) $m["id"],
                        "name" => $m["name"],
                        "price" => (float) $m["price"],
                        "image" => $m["image_url"] ?? ""
                    ]) ?>)'>
                        <div>
                            <?php if (!empty($m['image_url'])): ?>
                                <img src="<?= e(base_url($m['image_url'])) ?>" alt="<?= e($m['name']) ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                            <?php else: ?>
                                <div style="width: 100%; height: 120px; background: var(--surface-container); border-radius: 8px; margin-bottom: 12px; display: grid; place-items: center; color: var(--secondary);"><i class="fa-solid fa-utensils fa-2x"></i></div>
                            <?php endif; ?>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase;"><?= e($m['category_name'] ?? 'Menu') ?></span>
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--on-surface); margin: 4px 0 8px; line-height: 1.3;"><?= e($m['name']) ?></h3>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--outline-variant);">
                            <span style="font-weight: 800; color: var(--on-surface);"><?= format_rupiah((float) $m['price']) ?></span>
                            <span class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 99px;"><i class="fa-solid fa-plus"></i> Tambah</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="position: sticky; top: 24px; padding: 24px; border: 1px solid var(--outline-variant); box-shadow: var(--shadow-md);">
            <h2 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--on-surface);">
                <i class="fa-solid fa-cart-shopping"></i> Keranjang POS
            </h2>

            <form method="post" id="posForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="cart_data" id="cartDataInput" value="[]">

                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--on-surface); display: block; margin-bottom: 4px;">Cabang</label>
                        <select name="branch_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface); font-weight: 600;">
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--on-surface); display: block; margin-bottom: 4px;">Nama Pelanggan / Meja</label>
                        <input type="text" name="customer_name" placeholder="Contoh: Meja 4 - Budi" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface); font-weight: 600;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 0.85rem; font-weight: 700; color: var(--on-surface); display: block; margin-bottom: 4px;">Tipe Pesanan</label>
                            <select name="order_type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface); font-weight: 600;">
                                <option value="dine_in">Dine In</option>
                                <option value="takeaway">Takeaway</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; font-weight: 700; color: var(--on-surface); display: block; margin-bottom: 4px;">Pembayaran</label>
                            <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface); font-weight: 600;">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="qris">QRIS</option>
                                <option value="debit">Kartu Debit</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: var(--on-surface); display: block; margin-bottom: 4px;">Status Bayar</label>
                        <select name="payment_status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface); font-weight: 600;">
                            <option value="paid">Lunas (Paid)</option>
                            <option value="unpaid">Belum Bayar (Unpaid)</option>
                        </select>
                    </div>
                </div>

                <div id="cartItemsContainer" style="max-height: 280px; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px; border-top: 1px solid var(--outline-variant); border-bottom: 1px solid var(--outline-variant); padding: 12px 0;">
                    <div style="text-align: center; color: var(--secondary); padding: 24px 0;">Keranjang masih kosong</div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 1.2rem; font-weight: 800; color: var(--on-surface);">
                    <span>Total</span>
                    <span id="posTotalDisplay">Rp 0</span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 800; justify-content: center; border-radius: 12px;">
                    <i class="fa-solid fa-check-circle"></i> Buat Pesanan & Bayar
                </button>
            </form>
        </div>
    </div>
</section>

<script>
let posCart = [];
const saucesList = <?= json_encode($sauces) ?>;

function filterCategory(cat, btn) {
    document.querySelectorAll('.chip-row .chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#menuGrid .menu-pos-card').forEach(card => {
        if (cat === 'all' || card.getAttribute('data-category') === cat) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function addToCart(item) {
    const existing = posCart.find(x => x.id === item.id && !x.sauce_id && x.spice_level === '0' && !x.notes);
    if (existing) {
        existing.qty++;
    } else {
        posCart.push({
            id: item.id,
            name: item.name,
            price: item.price,
            qty: 1,
            sauce_id: saucesList.length > 0 ? saucesList[0].id : null,
            spice_level: '0',
            notes: ''
        });
    }
    renderCart();
}

function updateQty(index, delta) {
    posCart[index].qty += delta;
    if (posCart[index].qty <= 0) {
        posCart.splice(index, 1);
    }
    renderCart();
}

function updateItemDetail(index, field, value) {
    posCart[index][field] = value;
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    const totalDisplay = document.getElementById('posTotalDisplay');
    const inputData = document.getElementById('cartDataInput');
    
    if (posCart.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--secondary); padding: 24px 0;">Keranjang masih kosong</div>';
        totalDisplay.textContent = 'Rp 0';
        inputData.value = '[]';
        return;
    }

    let total = 0;
    container.innerHTML = posCart.map((item, idx) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        
        let sauceOptions = '<option value="">Tanpa Saus</option>';
        saucesList.forEach(s => {
            sauceOptions += `<option value="${s.id}" ${String(item.sauce_id) === String(s.id) ? 'selected' : ''}>${s.name}</option>`;
        });

        return `
            <div style="background: var(--surface-container-low); padding: 12px; border-radius: 8px; border: 1px solid var(--outline-variant);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <strong style="font-size: 0.95rem; color: var(--on-surface);">${item.name}</strong>
                    <span style="font-weight: 800; font-size: 0.9rem; color: var(--on-surface);">Rp ${subtotal.toLocaleString('id-ID')}</span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                    <select onchange="updateItemDetail(${idx}, 'sauce_id', this.value)" style="padding: 6px; font-size: 0.8rem; border-radius: 6px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface);">
                        ${sauceOptions}
                    </select>
                    <select onchange="updateItemDetail(${idx}, 'spice_level', this.value)" style="padding: 6px; font-size: 0.8rem; border-radius: 6px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface);">
                        <option value="0" ${item.spice_level === '0' ? 'selected' : ''}>Pedas Lvl 0</option>
                        <option value="1" ${item.spice_level === '1' ? 'selected' : ''}>Pedas Lvl 1</option>
                        <option value="2" ${item.spice_level === '2' ? 'selected' : ''}>Pedas Lvl 2</option>
                        <option value="3" ${item.spice_level === '3' ? 'selected' : ''}>Pedas Lvl 3</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <input type="text" placeholder="Catatan (opsional)" value="${item.notes}" onchange="updateItemDetail(${idx}, 'notes', this.value)" style="width: 60%; padding: 4px 8px; font-size: 0.8rem; border-radius: 6px; border: 1px solid var(--outline); background: var(--surface); color: var(--on-surface);">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="updateQty(${idx}, -1)" style="width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--outline); background: var(--surface); cursor: pointer; font-weight: 800;">-</button>
                        <span style="font-weight: 700; font-size: 0.9rem;">${item.qty}</span>
                        <button type="button" onclick="updateQty(${idx}, 1)" style="width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--outline); background: var(--surface); cursor: pointer; font-weight: 800;">+</button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    totalDisplay.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    inputData.value = JSON.stringify(posCart);
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
