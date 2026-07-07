<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);
$db = db();
if (isset($_GET['branch_id'])) {
    $_SESSION['admin_branch_id'] = $_GET['branch_id'] === 'all' ? null : (int) $_GET['branch_id'];
}
$branchId = $_SESSION['admin_branch_id'] ?? null;
$branches = branch_options($db);
$params = [];
$branchWhere = '';
if ($branchId) {
    $branchWhere = ' AND branch_id = ?';
    $params[] = $branchId;
}
$stats = [];
foreach ([
    'orders' => 'SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()' . $branchWhere,
    'revenue' => 'SELECT COALESCE(SUM(total),0) FROM orders WHERE status="completed" AND DATE(created_at)=CURDATE()' . $branchWhere,
    'pending' => 'SELECT COUNT(*) FROM orders WHERE status="pending"' . $branchWhere,
    'completed' => 'SELECT COUNT(*) FROM orders WHERE status="completed" AND DATE(created_at)=CURDATE()' . $branchWhere,
] as $key => $sql) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats[$key] = $stmt->fetchColumn();
}
$recent = $db->prepare('SELECT o.*, b.name branch_name FROM orders o JOIN branches b ON b.id=o.branch_id WHERE (? IS NULL OR o.branch_id=?) ORDER BY o.created_at DESC LIMIT 10');
$recent->execute([$branchId, $branchId]);
$chart = $db->prepare('SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue FROM orders WHERE status="completed" AND created_at >= CURDATE() - INTERVAL 6 DAY AND (? IS NULL OR branch_id=?) GROUP BY DATE(created_at) ORDER BY d');
$chart->execute([$branchId, $branchId]);
$bars = $chart->fetchAll();
$maxRevenue = max(1, ...array_map(fn($r) => (float) $r['revenue'], $bars ?: [['revenue' => 1]]));
$pageTitle = 'Admin Dashboard';
$bodyClass = 'admin-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <!-- Welcome Header -->
    <div class="admin-welcome">
        <h1>Selamat Datang, <?= e(current_user()['name']) ?>! 👋</h1>
        <p>Berikut ringkasan bisnis Anda hari ini — <?= format_date_id() ?></p>
    </div>

    <!-- Branch Filter -->
    <form class="admin-toolbar">
        <div class="form-field">
            <label>Filter Cabang</label>
            <select name="branch_id" onchange="this.form.submit()">
                <option value="all">Semua Cabang</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" <?= (int) $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Stat Cards -->
    <div class="grid grid-4" style="margin-bottom:32px;">
        <div class="card stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-bag-shopping"></i></div>
            <span class="stat-label">Total Pesanan Hari Ini</span>
            <strong class="stat-value"><?= (int) $stats['orders'] ?></strong>
        </div>
        <div class="card stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-wallet"></i></div>
            <span class="stat-label">Revenue Hari Ini</span>
            <strong class="stat-value"><?= format_rupiah((float) $stats['revenue']) ?></strong>
        </div>
        <div class="card stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-hourglass-half"></i></div>
            <span class="stat-label">Menunggu Konfirmasi</span>
            <strong class="stat-value"><?= (int) $stats['pending'] ?></strong>
        </div>
        <div class="card stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-circle-check"></i></div>
            <span class="stat-label">Selesai Hari Ini</span>
            <strong class="stat-value"><?= (int) $stats['completed'] ?></strong>
        </div>
    </div>

    <!-- Chart & Quick Info -->
    <div class="grid grid-2" style="margin-bottom:32px;">
        <div class="card" style="border-radius:var(--radius-lg);padding:28px;">
            <h2 style="margin-bottom:20px;font-size:1.1rem;font-weight:800;">📊 Revenue 7 Hari Terakhir</h2>
            <div class="chart">
                <?php foreach ($bars as $bar): ?>
                    <div class="bar" style="height:<?= max(12, ((float) $bar['revenue'] / $maxRevenue) * 170) ?>px">
                        <?= date('d/m', strtotime($bar['d'])) ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($bars)): ?>
                    <p style="color:var(--secondary);font-size:0.9rem;text-align:center;width:100%;padding:40px 0;">Belum ada data revenue minggu ini.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card" style="border-radius:var(--radius-lg);padding:28px;">
            <h2 style="margin-bottom:20px;font-size:1.1rem;font-weight:800;">ℹ️ Informasi Cepat</h2>
            <div class="info-item">
                <i class="fa-solid fa-store"></i>
                <div>
                    <div style="font-weight:700;font-size:0.9rem;">Cabang Aktif</div>
                    <div style="color:var(--secondary);font-size:0.85rem;"><?= count($branches) ?> cabang beroperasi</div>
                </div>
            </div>
            <div class="info-item">
                <i class="fa-solid fa-images"></i>
                <div>
                    <div style="font-weight:700;font-size:0.9rem;">Banner Aktif</div>
                    <div style="color:var(--secondary);font-size:0.85rem;"><?= (int) $db->query('SELECT COUNT(*) FROM banners WHERE is_active=1')->fetchColumn() ?> banner ditampilkan</div>
                </div>
            </div>
            <div class="info-item">
                <i class="fa-solid fa-clock"></i>
                <div>
                    <div style="font-weight:700;font-size:0.9rem;">Jam Operasional</div>
                    <div style="color:var(--secondary);font-size:0.85rem;">10:00 - 22:00 WIB</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="section-header">
        <h2>📋 Pesanan Terbaru</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Cabang</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent->fetchAll() as $o): ?>
                <tr>
                    <td style="font-weight:700;"><?= e($o['order_code']) ?></td>
                    <td><?= e($o['branch_name']) ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><span class="badge <?= get_status_color($o['status']) ?>"><?= get_status_label($o['status']) ?></span></td>
                    <td style="font-weight:700;"><?= format_rupiah((float) $o['total']) ?></td>
                    <td style="color:var(--secondary);font-size:0.85rem;"><?= e($o['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent->fetchAll())): ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
