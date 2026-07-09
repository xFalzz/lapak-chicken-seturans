# LAPORAN AUDIT & ANALISIS MENDALAM SISTEM INFORMASI
## Proyek: Lapak Chicken Seturan (Integrated F&B Management System)
**Disusun sebagai Bahan Studi Kasus & Analisis Arsitektur Perangkat Lunak untuk Mata Kuliah**

---

## 1. Executive Summary & Deskripsi Sistem
**Lapak Chicken Seturan** adalah sebuah aplikasi web terintegrasi berbasis **Multi-Role F&B Management System** yang dirancang untuk mengotomatisasi seluruh alur bisnis restoran cepat saji (khususnya sajian ayam goreng crispy & ricebox). 

Sistem ini memecahkan masalah fragmentasi operasional restoran dengan menghubungkan empat aktor utama dalam satu basis data tunggal yang tersinkronisasi secara *real-time*:
1. **Customer (Pelanggan):** Melakukan pemesanan mandiri (*Self-Order* baik untuk *Dine-in*, *Takeaway*, maupun *Delivery*), kustomisasi varian menu (pilihan saus & level kepedasan), serta pemantauan status pesanan secara langsung.
2. **Kasir (Cashier / POS):** Menangani pemesanan di meja kasir (*Point of Sales* / POS), proses pembayaran instan, pencetakan struk digital (*thermal receipt*), serta pengecekan antrean.
3. **Dapur (Kitchen Display System / KDS):** Layar monitor khusus di dapur yang memantau pesanan masuk secara otomatis (*real-time polling*) dan memperbarui status masakan (*Pending -> Cooking -> Ready*).
4. **Admin / Manajer:** Dasbor pusat untuk mengelola data master (cabang, menu, kategori, saus, stok, jam operasional), manajemen pengguna (*User & Role Control*), hingga pemantauan laporan penjualan bulanan.

---

## 2. Stack Teknologi & Bahasa Pemrograman
Sistem ini dibangun menggunakan pendekatan **Native Modular Architecture** yang sangat efisien, ringan (*lightweight*), dan mudah dipahami, menjadikannya model studi kasus yang sangat ideal untuk pembelajaran teknik perangkat lunak dan pemrograman web:

### A. Backend Development
* **Bahasa Pemrograman:** **PHP 8+ (Native / Procedural with OOP Database Singleton Helper)**
  * **Alasan Pemilihan / Keunggulan:** Tidak bergantung pada *framework* kelas berat (seperti Laravel atau Symfony) ataupun paket eksternal (*zero composer dependencies overhead*). Hal ini membuat waktu eksekusi skrip (*execution time*) sangat cepat (< 50 milidetik), konsumsi memori server sangat rendah, serta memperlihatkan pemahaman fundamental tentang pengelolaan HTTP Request/Response, *Session Handling*, dan *Routing* modular.
* **Database Driver:** **PHP Data Objects (PDO)**
  * Menggunakan lapisan abstraksi database berorientasi objek yang mendukung parameter *Prepared Statements* secara ketat untuk menjamin keamanan dari injeksi SQL.

### B. Database & Storage Layer
* **RDBMS:** **MySQL / MariaDB (Engine: InnoDB)**
  * **Alasan Pemilihan:** Engine InnoDB dipilih karena dukungannya terhadap **ACID Transactions** (Atomicity, Consistency, Isolation, Durability) dan **Foreign Key Constraints** (`ON DELETE CASCADE`, `ON DELETE RESTRICT`, `ON DELETE SET NULL`), yang mutlak diperlukan dalam aplikasi penanganan transaksi finansial dan inventaris.

### C. Frontend / Presentation Layer
* **Structure & Templating:** **HTML5 Semantic & PHP Server-Side Rendering (SSR)**
  * Menggunakan pemisahan *component template* (`includes/header.php`, `includes/footer.php`, `includes/sidebar-*.php`) untuk menjaga asas *DRY (Don't Repeat Yourself)*.
* **Styling & Design System:** **Vanilla CSS3 (Modular Custom Design System)**
  * Tanpa *framework* CSS eksternal seperti Bootstrap atau Tailwind. Seluruh gaya dibangun murni dari awal menggunakan **CSS Custom Properties (Variables)** (`--primary`, `--surface`, `--on-surface`, `--container-max`, dll.) yang diorganisasikan dalam 6 berkas modular (`main.css`, `customer.css`, `admin.css`, `kasir.css`, `dapur.css`, `components.css`). Hal ini menjamin desain yang *pixel-perfect*, mudah dimodifikasi, dan bebas dari *unused CSS bloat*.
* **Client-Side Behavior & Async Communication:** **Vanilla JavaScript (ES6+ & Fetch API)**
  * Pengolahan interaksi antarmuka (seperti animasi *modal drawer*, kalkulasi total harga keranjang, filter menu dinamis) serta komunikasi *asynchronous* ke server (AJAX) dilakukan menggunakan **Fetch API** berbasis `Promise`/`async-await` (`main.js`, `cart.js`, `kasir.js`, `dapur.js`, `order.js`), tanpa perlu pustaka jQuery.
* **Typography & Iconography:** **Google Fonts (Inter & Poppins)** serta **Font Awesome 6 (Free Vector Icons)**.

---

## 3. Arsitektur Modul & Struktur Folder Proyek
Proyek ini mengadopsi pola **Domain-Driven Directory Structure**, di mana kode dipisahkan berdasarkan peran (*Role-Based Modules*):

```text
c:\laragon\www\lapak-chicken-seturan\
├── config\                 # Layer Konfigurasi Global
│   ├── config.php          # Definisi Konstanta, Dynamic BASE_URL Auto-Detect, Global Pathing
│   └── database.php        # Singleton PDO Database Connection & Auto Sync Helper
├── database\               # Skema Relasional & Seeding Data
│   ├── schema.sql          # DDL (Data Definition Language) 15 Tabel Utama + Constraints
│   └── seed.sql            # DML (Data Manipulation Language) Data Awal / Mock Data
├── includes\               # Global Helpers & Reusable UI Components
│   ├── functions.php       # Kumpulan Helper: DB, Security (CSRF/XSS), Cart, RBAC Auth, Formatting
│   ├── header.php          # Global Navigation Bar, Dynamic Cart Counter, & Mobile Drawer
│   ├── footer.php          # Global Footer & Corporate Information
│   └── sidebar-*.php       # Navigasi Sidebar Khusus Admin & Kasir
├── customer\               # Modul Pelanggan (B2C Storefront)
│   ├── menu.php            # Katalog Menu Dinamis, Filter Kategori, Kustomisasi Modal, Ala Carte
│   ├── cart.php            # Keranjang Belanja, Kalkulasi Subtotal & Pajak
│   ├── checkout.php        # Formulir Pengiriman & Pemilihan Tipe Pesanan (Dine-in/Takeaway/Delivery)
│   ├── order-status.php    # Halaman Konfirmasi Pesanan Selesai / Instruksi Pembayaran
│   ├── track-order.php     # Pelacakan Live Status Pesanan oleh Pelanggan
│   ├── profile.php         # Manajemen Akun & Riwayat Pemesanan Pribadi
│   └── help.php            # Pusat Bantuan / FAQ Pelanggan
├── kasir\                  # Modul Kasir / Point of Sales (POS System)
│   ├── pos.php             # Antarmuka Kasir Grid Menu Cepat & Keranjang Kasir
│   ├── process.php         # Pemrosesan Checkout POS & Pencatatan Kasir
│   └── receipt.php         # Generator Struk Thermal Digital (Cetak Langsung)
├── dapur\                  # Modul Dapur / Kitchen Display System (KDS)
│   └── index.php           # Layar KDS Monitoring Pesanan & Status Masakan secara Real-Time
├── admin\                  # Modul Backoffice / Manajerial Admin
│   ├── index.php           # Dashboard Analitik Penjualan & Performa Cabang
│   ├── branches/           # CRUD Master Cabang Restoran
│   ├── categories/         # CRUD Kategori Menu
│   ├── menus/              # CRUD Menu, Stok, Harga, & Gambar
│   ├── sauces/             # CRUD Pilihan Saus & Ekstra Biaya
│   ├── orders/             # Manajemen Seluruh Transaksi & Update Status Manual
│   ├── reports/            # Generator Laporan Keuangan & Penjualan
│   ├── banners/            # Pengaturan Banner Promo Halaman Depan
│   ├── settings/           # Pengaturan Cabang & Parameter Operasional
│   └── users/              # Manajemen Hak Akses Pengguna (Admin, Kasir, Dapur, Customer)
├── api\                    # Layer REST API / JSON Endpoints (AJAX Handlers)
│   ├── auth.php            # Endpoint Login / Register / Logout AJAX
│   ├── cart.php            # Endpoint Penambahan, Pengurangan, & Hapus Item Keranjang
│   ├── menu.php            # Endpoint Pengambilan Data Menu Dinamis JSON
│   ├── order.php           # Endpoint Pembuatan Pesanan Baru dari Customer maupun Kasir
│   └── status.php          # Endpoint KDS Polling untuk Pembaruan Status Masakan Real-Time
├── assets\                 # Static Resources (CSS Modular, JS Modular, & Fonts)
└── index.php               # Halaman Beranda / Landing Page Utama Lapak Chicken
```

---

## 4. Analisis Skema & Relasi Database (15 Tabel)
Desain database proyek ini sangat matang dan menerapkan normalisasi database hingga tingkat **3NF (Third Normal Form)** untuk meminimalisasi redundansi data:

| Nama Tabel | Peran Utama | Jenis Relasi & Integritas (*Constraints*) |
| :--- | :--- | :--- |
| **`branches`** | Master Data Cabang | Primary Key `id`. Menjadi entitas induk bagi pesanan, jam operasional, dan pengaturan. |
| **`users`** | Akun Pengguna & Hak Akses | Primary Key `id`. Memiliki kolom `role` (`admin`, `kasir`, `dapur`, `customer`). |
| **`categories`** | Pengelompokan Menu | Primary Key `id`. Terhubung ke tabel `menus` (`1-to-Many`). |
| **`sauces`** | Pilihan Saus & Tambahan Harga | Primary Key `id`. Dipakai pada item keranjang (`cart_items`) dan detail pesanan (`order_details`). |
| **`menus`** | Katalog Produk & Stok | Foreign Key `category_id -> categories(id)` dengan **`ON DELETE RESTRICT`** (kategori tidak bisa dihapus jika masih memiliki produk menu aktif). |
| **`carts`** | Wadah Keranjang Belanja Sementara | Foreign Key `user_id -> users(id)` dengan **`ON DELETE CASCADE`** (keranjang otomatis bersih jika akun dihapus). Mendukung *guest cart* berbasis `session_id`. |
| **`cart_items`** | Rincian Item di Keranjang | Foreign Key `cart_id`, `menu_id`, dan `sauce_id` (dengan `ON DELETE SET NULL` pada saus). Menyimpan data kustomisasi `spice_level`, `quantity`, dan `notes`. |
| **`orders`** | Header Transaksi Pemesanan | Foreign Key `branch_id -> branches(id)` (`ON DELETE RESTRICT`) dan `user_id -> users(id)` (`ON DELETE SET NULL`). Menyimpan kode pesanan unik (`order_code`), tipe (`Dine-in/Takeaway/Delivery`), dan status pesanan. |
| **`order_details`**| Rincian Menu pada Pesanan (*Snapshot*) | Foreign Key `order_id -> orders(id)` (**`ON DELETE CASCADE`**) dan `menu_id -> menus(id)` (**`ON DELETE RESTRICT`**). **Keunggulan Desain:** Menyimpan *snapshot* `subtotal` pada saat transaksi, sehingga jika harga menu berubah di masa depan, riwayat transaksi pesanan lama tidak ikut berubah (*historical accuracy*). |
| **`payments`** | Catatan Pembayaran & Metode | Foreign Key `order_id -> orders(id)`. Menyimpakan status (`unpaid`, `paid`) dan waktu pembayaran (`paid_at`). |
| **`reviews`** | Ulasan & Rating Pelanggan | Foreign Key `user_id` dan `order_id` (`ON DELETE SET NULL`). |
| **`settings`** | Pengaturan Kustom per Cabang | Foreign Key `branch_id -> branches(id)` (**`ON DELETE CASCADE`**). Menggunakan komposit unik `(branch_id, key)`. |
| **`operating_hours`**| Jam Buka/Tutup Cabang | Foreign Key `branch_id -> branches(id)` (`ON DELETE CASCADE`). |
| **`banners`** | Gambar Promo Slider | Foreign Key `branch_id -> branches(id)` (`ON DELETE CASCADE`). |
| **`rate_limits`** | Proteksi Keamanan Kecepatan Akses | Primary Key komposit `(ip, action)`. Mencatat jumlah percobaan (`attempts`) untuk mencegah serangan *brute-force*. |

---

## 5. Audit Keamanan Sistem (Security Audit)
Sistem ini telah menerapkan standar keamanan industri web (*OWASP Security Best Practices*) di dalam *core helper* (`includes/functions.php` & `config/database.php`):

1. **Pencegahan SQL Injection (SQLi):**
   * Seluruh interaksi database tanpa terkecuali menggunakan **Prepared Statements (PDO)** dengan *parameter binding* (`$stmt->execute([...])`). Tidak ada penyambungan variabel *string* langsung ke dalam kueri SQL, sehingga sistem 100% kebal terhadap injeksi SQL.
2. **Pencegahan Cross-Site Scripting (XSS):**
   * Setiap *output* data yang bersumber dari database atau input pengguna disaring secara konsisten menggunakan fungsi *wrapper helper* `e($string)`:
     ```php
     function e(?string $string): string {
         return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
     }
     ```
     Hal ini memastikan skrip berbahaya (`<script>alert('xss')</script>`) yang diinput oleh pengguna akan diubah menjadi entitas HTML statis yang tidak bisa dieksekusi browser.
3. **Pencegahan Cross-Site Request Forgery (CSRF):**
   * Setiap sesi pengguna dilengkapi dengan token acak kriptografis 64-karakter (`bin2hex(random_bytes(32))`) yang diverifikasi pada fungsi `csrf_token()` dan `verify_csrf($token)`.
   * Permintaan AJAX melalui *Fetch API* juga diwajibkan menyertakan *header* `X-CSRF-Token` yang divalidasi pada lapisan API (`api/cart.php`, `api/order.php`).
4. **Proteksi Anti Brute-Force & Rate Limiting:**
   * Terdapat implementasi `check_rate_limit($action, $maxAttempts, $decaySeconds)` menggunakan tabel `rate_limits`. Jika alamat IP melebihi batas percobaan (misalnya 5 kali salah login dalam 1 menit), sistem akan memblokir aksi tersebut sementara untuk mencegah pembobolan sandi otomatis.
5. **Role-Based Access Control (RBAC) & Session Management:**
   * Pengelompokan hak akses dikendalikan melalui helper `require_login()` dan `require_role(...)`. Jika seorang pengguna biasa (*customer*) mencoba mengakses URL rahasia seperti `admin/orders/index.php` atau `kasir/pos.php`, sistem akan langsung menolak dan mengarahkan mereka ke halaman login atau beranda dengan pesan peringatan.
6. **Kriptografi Kata Sandi (Password Hashing):**
   * Penyimpanan sandi menggunakan algoritma *hashing* adaptif standar modern `password_hash($pass, PASSWORD_DEFAULT)` (Bcrypt) dan dipastikan validasinya dengan `password_verify()`. Sandi tidak pernah disimpan dalam bentuk teks terang (*plaintext*).

---

## 6. Audit Desain & Responsivitas Antarmuka (UX/UI & Responsive Audit)
* **Pembersihan Konten Fiktif (*No Fake Discounts*):** Sesuai dengan audit terbaru, seluruh elemen pemasaran fiktif (seperti harga coret palsu yang ditambah Rp 10.000, *badge promo spesial* buatan, dan *banner promo diskon*) telah dibersihkan total. Tampilan antarmuka berfokus pada **harga transparan, akurasi data langsung dari database, dan kualitas presentasi produk**.
* **Proteksi Grid Multikolom Universal (`main.css`):** Sistem dilengkapi dengan aturan media query adaptif yang secara otomatis menyusun ulang tata letak grid (*2-columns, 3-columns, 4-columns*) menjadi susunan vertikal tunggal (`1fr`) pada layar seluler (`< 992px`), menjamin tidak ada elemen yang tumpang tindih (*overlapping*) ataupun keluar jalur (*horizontal overflow*).
* **Navigasi Mobile Drawer & Horizontal Category Chips:** Pada perangkat seluler, *navigasi topbar* bertransformasi menjadi *Hamburger Menu* yang membuka panel samping geser (*Smooth Slide Drawer*) dengan efek kaca *backdrop blur*. Pada halaman katalog menu, *sidebar* beralih menjadi cip kategori horisontal geser (*horizontal scrolling chips*) berdesain ergonomis.
* **Auto-Detect BASE_URL:** Konfigurasi pathing kini sepenuhnya dinamis (`config.php`). Terlepas dari apakah proyek diekstrak di folder bernama `lapak-chicken-seturan`, `lapak-chicken-seturans`, ataupun di dalam subfolder berlapis, sistem akan otomatis mengenali *document root* server, mencegah error `404 Not Found` pada aset CSS dan JS.

---

## 7. Kesimpulan untuk Studi Kasus Mata Kuliah
Proyek **Lapak Chicken Seturan** merupakan contoh penerapan **Software Engineering & Web Development** yang sangat komprehensif untuk level perguruan tinggi. Proyek ini membuktikan bahwa:
1. Pembuatan sistem berskala enterprise/komersial (B2C + B2B POS + KDS) tidak selalu bergantung pada *framework* berat, melainkan dapat dicapai dengan sangat elegan melalui fundamental **PHP Native Modular + Vanilla JS ES6 + CSS Custom System**.
2. Penerapan konsep dasar ilmu komputer seperti **Normalisasi Database 3NF**, **Referential Integrity**, **Asynchronous Polling (AJAX)**, dan **Keamanan Siber Layered (SQLi, XSS, CSRF, RBAC)** telah diterapkan secara nyata dan konsisten di seluruh bagian kode.

---
*Laporan ini siap digunakan sebagai bahan presentasi, makalah, atau telaah kritis untuk tugas mata kuliah Pemrograman Web, Rekayasa Perangkat Lunak, maupun Basis Data.*
