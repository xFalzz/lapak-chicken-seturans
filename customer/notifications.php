<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$db = db();
$user = current_user();
$notifications = [
    [
        'type' => 'order',
        'icon' => 'fa-solid fa-receipt',
        'icon_bg' => 'var(--primary-container)',
        'icon_color' => 'var(--on-primary-container)',
        'title' => 'Pesanan sedang disiapkan',
        'message' => 'Pesanan #ORD-2024-001 sedang dimasak oleh chef kami. Estimasi selesai dalam 15 menit.',
        'time' => '5 menit lalu',
        'unread' => true,
    ],
    [
        'type' => 'promo',
        'icon' => 'fa-solid fa-tag',
        'icon_bg' => '#FFF3CD',
        'icon_color' => '#856404',
        'title' => 'Promo Spesial Weekend!',
        'message' => 'Diskon 30% untuk semua paket hemat berlaku di akhir pekan ini. Gunakan kode WEEKEND30.',
        'time' => '1 jam lalu',
        'unread' => true,
    ],
    [
        'type' => 'order',
        'icon' => 'fa-solid fa-check-circle',
        'icon_bg' => '#D4EDDA',
        'icon_color' => '#155724',
        'title' => 'Pesanan selesai',
        'message' => 'Pesanan #ORD-2024-001 telah selesai. Terima kasih telah memesan di Lapak Chicken!',
        'time' => '2 jam lalu',
        'unread' => false,
    ],
    [
        'type' => 'system',
        'icon' => 'fa-solid fa-bell',
        'icon_bg' => 'var(--surface-container)',
        'icon_color' => 'var(--on-surface)',
        'title' => 'Poin reward bertambah',
        'message' => 'Selamat! Anda mendapatkan 450 poin dari pesanan terakhir. Total poin Anda sekarang: 4,280.',
        'time' => '3 jam lalu',
        'unread' => false,
    ],
    [
        'type' => 'promo',
        'icon' => 'fa-solid fa-gift',
        'icon_bg' => '#FFF3CD',
        'icon_color' => '#856404',
        'title' => 'Voucher Gratis Minuman',
        'message' => 'Anda mendapatkan voucher gratis 1 minuman untuk pembelian paket apa saja. Berlaku sampai akhir bulan ini.',
        'time' => '1 hari lalu',
        'unread' => false,
    ],
];

$pageTitle = 'Notifikasi';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container" style="max-width:800px;">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
            <div>
                <h1 style="font-size:1.8rem;margin-bottom:8px;font-weight:800;">Notifikasi</h1>
                <p style="color:var(--secondary);">Pantau pesanan dan promo terbaru dari Lapak Chicken.</p>
            </div>
            <button class="btn btn-outline" style="border-radius:99px;padding:8px 20px;font-size:0.9rem;" onclick="toast('Semua notifikasi ditandai telah dibaca','info')">
                <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
            </button>
        </div>

        <div style="display:flex;gap:12px;margin-bottom:32px;">
            <button class="chip-icon active" style="padding:10px 20px;">
                <i class="fa-solid fa-inbox"></i> Semua
            </button>
            <button class="chip-icon" style="padding:10px 20px;">
                <i class="fa-solid fa-receipt"></i> Pesanan
            </button>
            <button class="chip-icon" style="padding:10px 20px;">
                <i class="fa-solid fa-tag"></i> Promo
            </button>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($notifications as $notif): ?>
                <div class="checkout-card" style="padding:20px 24px;<?= $notif['unread'] ? 'border-left:3px solid #B29500;' : '' ?>">
                    <div style="display:flex;gap:16px;align-items:flex-start;">
                        <div style="width:44px;height:44px;border-radius:12px;background:<?= $notif['icon_bg'] ?>;color:<?= $notif['icon_color'] ?>;display:grid;place-items:center;flex-shrink:0;font-size:1.1rem;">
                            <i class="<?= $notif['icon'] ?>"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
                                <strong style="font-size:1rem;<?= $notif['unread'] ? '' : 'font-weight:600;' ?>"><?= $notif['title'] ?></strong>
                                <span style="font-size:0.8rem;color:var(--secondary);white-space:nowrap;"><?= $notif['time'] ?></span>
                            </div>
                            <p style="color:var(--secondary);font-size:0.9rem;margin-top:6px;line-height:1.5;"><?= $notif['message'] ?></p>
                        </div>
                        <?php if ($notif['unread']): ?>
                            <div style="width:8px;height:8px;background:#B29500;border-radius:50%;flex-shrink:0;margin-top:8px;"></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
