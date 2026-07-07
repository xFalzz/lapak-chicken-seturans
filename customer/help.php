<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Pusat Bantuan';
require __DIR__ . '/../includes/header.php';

$faqs = [
    [
        'q' => 'Bagaimana cara memesan makanan di Lapak Chicken?',
        'a' => 'Pilih cabang terdekat, telusuri menu, tambahkan item ke keranjang, lalu selesaikan pembayaran. Pesanan Anda akan langsung diproses oleh dapur kami.',
    ],
    [
        'q' => 'Metode pembayaran apa saja yang tersedia?',
        'a' => 'Kami menerima pembayaran melalui Gopay, OVO, Dana, BCA Virtual Account, Mandiri Virtual Account, dan pembayaran tunai (Cash on Delivery).',
    ],
    [
        'q' => 'Berapa lama estimasi waktu pesanan siap?',
        'a' => 'Untuk pesanan takeaway dan dine-in, estimasi waktu sekitar 15-25 menit tergantung jumlah pesanan. Untuk delivery, tambahkan estimasi waktu pengiriman sekitar 15-30 menit.',
    ],
    [
        'q' => 'Apakah bisa membatalkan pesanan yang sudah dibuat?',
        'a' => 'Pesanan hanya bisa dibatalkan selama statusnya masih "Menunggu". Setelah dikonfirmasi atau dimasak, pesanan tidak dapat dibatalkan.',
    ],
    [
        'q' => 'Bagaimana cara mendapatkan poin reward?',
        'a' => 'Setiap pembelian Rp 1.000 akan mendapatkan 1 poin. Poin dapat ditukarkan dengan voucher diskon atau menu gratis di halaman Profil Anda.',
    ],
    [
        'q' => 'Apakah ada minimum order untuk delivery?',
        'a' => 'Minimum order untuk delivery adalah Rp 25.000 (sebelum pajak). Gratis ongkir untuk jarak di bawah 3 km dari cabang.',
    ],
];
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container" style="max-width:900px;">

        <div style="text-align:center;margin-bottom:48px;">
            <div style="width:64px;height:64px;background:var(--primary-container);color:var(--on-primary-container);border-radius:50%;display:grid;place-items:center;font-size:1.6rem;margin:0 auto 20px;">
                <i class="fa-solid fa-headset"></i>
            </div>
            <h1 style="font-size:2rem;font-weight:800;margin-bottom:12px;">Pusat Bantuan</h1>
            <p style="color:var(--secondary);font-size:1.05rem;max-width:500px;margin:0 auto;">Ada yang bisa kami bantu? Temukan jawaban dari pertanyaan yang sering diajukan.</p>
        </div>

        <div style="position:relative;margin-bottom:48px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:24px;top:50%;transform:translateY(-50%);color:var(--secondary);font-size:1.1rem;"></i>
            <input type="search" placeholder="Cari pertanyaan atau topik..." style="width:100%;padding:18px 24px 18px 56px;border-radius:99px;border:1px solid var(--outline-variant);background:var(--surface);font-size:1.05rem;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:48px;">
            <a href="#faq" class="checkout-card" style="text-align:center;padding:32px 20px;cursor:pointer;">
                <div style="font-size:1.8rem;color:#B29500;margin-bottom:12px;"><i class="fa-solid fa-circle-question"></i></div>
                <h3 style="font-size:1rem;margin-bottom:6px;">FAQ</h3>
                <p style="font-size:0.85rem;color:var(--secondary);">Pertanyaan umum</p>
            </a>
            <a href="mailto:cs@lapak-chicken.com" class="checkout-card" style="text-align:center;padding:32px 20px;cursor:pointer;">
                <div style="font-size:1.8rem;color:#B29500;margin-bottom:12px;"><i class="fa-solid fa-envelope"></i></div>
                <h3 style="font-size:1rem;margin-bottom:6px;">Email Kami</h3>
                <p style="font-size:0.85rem;color:var(--secondary);">cs@lapak-chicken.com</p>
            </a>
            <a href="https://wa.me/6282121112143" class="checkout-card" style="text-align:center;padding:32px 20px;cursor:pointer;">
                <div style="font-size:1.8rem;color:#B29500;margin-bottom:12px;"><i class="fa-brands fa-whatsapp"></i></div>
                <h3 style="font-size:1rem;margin-bottom:6px;">WhatsApp</h3>
                <p style="font-size:0.85rem;color:var(--secondary);">+62 821-2111-2143</p>
            </a>
        </div>

        <div id="faq">
            <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:24px;">Pertanyaan yang Sering Diajukan</h2>
            
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($faqs as $i => $faq): ?>
                    <div class="checkout-card faq-item" style="padding:0;">
                        <button type="button" onclick="toggleFaq(this)" style="width:100%;padding:20px 24px;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;text-align:left;">
                            <strong style="font-size:1rem;color:var(--on-surface);"><?= e($faq['q']) ?></strong>
                            <i class="fa-solid fa-chevron-down faq-chevron" style="color:var(--secondary);transition:transform 0.3s;flex-shrink:0;margin-left:16px;"></i>
                        </button>
                        <div class="faq-answer" style="max-height:0;overflow:hidden;transition:max-height 0.3s ease;">
                            <div style="padding:0 24px 20px;color:var(--secondary);font-size:0.95rem;line-height:1.6;">
                                <?= e($faq['a']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="text-align:center;margin-top:64px;padding:48px;background:var(--surface-container-low);border-radius:24px;">
            <h3 style="font-size:1.3rem;margin-bottom:12px;">Masih butuh bantuan?</h3>
            <p style="color:var(--secondary);margin-bottom:24px;">Tim kami siap membantu Anda kapan saja.</p>
            <a href="https://wa.me/6282121112143" class="btn btn-primary" style="border-radius:99px;padding:12px 32px;">
                <i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp
            </a>
        </div>

    </div>
</section>

<script>
function toggleFaq(btn) {
    const answer = btn.nextElementSibling;
    const chevron = btn.querySelector('.faq-chevron');
    const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';
    document.querySelectorAll('.faq-answer').forEach(a => { a.style.maxHeight = '0px'; });
    document.querySelectorAll('.faq-chevron').forEach(c => { c.style.transform = 'rotate(0deg)'; });
    
    if (!isOpen) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        chevron.style.transform = 'rotate(180deg)';
    }
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
