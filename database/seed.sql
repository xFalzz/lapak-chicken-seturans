SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO branches (id, name, address, phone) VALUES
(1, 'Lapak Chicken Seturan Pusat', 'Jl. Seturan Raya No. 12, Caturtunggal, Depok, Sleman, Yogyakarta', '081234567890'),
(2, 'Lapak Chicken Seturan Babarsari', 'Jl. Babarsari No. 88, Caturtunggal, Depok, Sleman, Yogyakarta', '081234567891');

INSERT INTO users (id, name, email, phone, password, role) VALUES
(1, 'Owner Lapak Chicken', 'admin@lapakchicken.com', '081111111111', '$2y$10$GodVc49OfUfghRCluEdMbehspORova9VJgIZjvFpSh9yKZ2UG1CWm', 'admin'),
(2, 'Kasir Seturan', 'kasir@lapakchicken.com', '082222222222', '$2y$10$GodVc49OfUfghRCluEdMbehspORova9VJgIZjvFpSh9yKZ2UG1CWm', 'kasir'),
(3, 'Dapur Seturan', 'dapur@lapakchicken.com', '083333333333', '$2y$10$GodVc49OfUfghRCluEdMbehspORova9VJgIZjvFpSh9yKZ2UG1CWm', 'dapur'),
(4, 'Nabila Customer', 'nabila@example.com', '084444444444', '$2y$10$GodVc49OfUfghRCluEdMbehspORova9VJgIZjvFpSh9yKZ2UG1CWm', 'customer'),
(5, 'Raka Customer', 'raka@example.com', '085555555555', '$2y$10$GodVc49OfUfghRCluEdMbehspORova9VJgIZjvFpSh9yKZ2UG1CWm', 'customer');

INSERT INTO categories (id, name, slug, icon, is_active) VALUES
(1, 'Ayam', 'ayam', 'fa-solid fa-drumstick-bite', 1),
(2, 'Burger', 'burger', 'fa-solid fa-burger', 1),
(3, 'Minuman', 'minuman', 'fa-solid fa-cup-straw', 1),
(4, 'Snack', 'snack', 'fa-solid fa-cookie-bite', 1),
(5, 'Paket Hemat', 'paket-hemat', 'fa-solid fa-box', 1);

INSERT INTO sauces (id, name, price_extra, is_active) VALUES
(1, 'Original', 0, 1),
(2, 'Pedas', 1000, 1),
(3, 'BBQ', 2000, 1),
(4, 'Keju', 3000, 1),
(5, 'Sambal Matah', 2500, 1);

INSERT INTO menus (id, category_id, name, slug, description, price, is_active) VALUES
(1, 1, 'Ayam Crispy Original', 'ayam-crispy-original', 'Ayam goreng renyah khas Lapak Chicken.', 15000, 1),
(2, 1, 'Ayam Geprek Seturan', 'ayam-geprek-seturan', 'Ayam geprek pedas dengan sambal bawang.', 18000, 1),
(3, 1, 'Chicken Wings', 'chicken-wings', 'Sayap ayam crispy dengan pilihan saus.', 17000, 1),
(4, 1, 'Chicken Popcorn', 'chicken-popcorn', 'Potongan ayam kecil renyah untuk ngemil.', 16000, 1),
(5, 2, 'Classic Chicken Burger', 'classic-chicken-burger', 'Burger ayam crispy, lettuce, dan saus spesial.', 22000, 1),
(6, 2, 'Cheese Chicken Burger', 'cheese-chicken-burger', 'Burger ayam crispy dengan keju leleh.', 25000, 1),
(7, 2, 'Double Chicken Burger', 'double-chicken-burger', 'Dua patty ayam crispy untuk lapar serius.', 32000, 1),
(8, 3, 'Es Teh Manis', 'es-teh-manis', 'Teh manis dingin segar.', 5000, 1),
(9, 3, 'Lemon Tea', 'lemon-tea', 'Teh lemon dingin dengan rasa ringan.', 8000, 1),
(10, 3, 'Air Mineral', 'air-mineral', 'Air mineral botol.', 4000, 1),
(11, 4, 'French Fries', 'french-fries', 'Kentang goreng gurih.', 12000, 1),
(12, 4, 'Onion Ring', 'onion-ring', 'Bawang bombai crispy.', 13000, 1),
(13, 5, 'Paket Hemat 1', 'paket-hemat-1', 'Ayam crispy, nasi, dan es teh.', 22000, 1),
(14, 5, 'Paket Hemat 2', 'paket-hemat-2', 'Ayam geprek, nasi, dan lemon tea.', 26000, 1),
(15, 5, 'Paket Rame', 'paket-rame', 'Empat ayam crispy, empat nasi, dan empat minuman.', 85000, 1);

INSERT INTO operating_hours (branch_id, day_of_week, open_time, close_time, is_closed)
SELECT b.id, d.day, '10:00:00', '22:00:00', 0
FROM branches b
JOIN (
  SELECT 0 day UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) d;

INSERT INTO settings (branch_id, `key`, `value`) VALUES
(1, 'tax_rate', '0.10'), (1, 'min_order', '15000'), (1, 'whatsapp', '081234567890'),
(2, 'tax_rate', '0.10'), (2, 'min_order', '15000'), (2, 'whatsapp', '081234567891');

INSERT INTO banners (branch_id, title, image, is_active) VALUES
(1, 'Promo Geprek Pedas', 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?auto=format&fit=crop&w=1200&q=80', 1),
(1, 'Paket Hemat Anak Kost', 'https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=1200&q=80', 1),
(2, 'Burger Crispy Babarsari', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1200&q=80', 1),
(2, 'Wings Party', 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?auto=format&fit=crop&w=1200&q=80', 1);

INSERT INTO orders (id, branch_id, user_id, order_code, customer_name, customer_phone, order_type, status, total, created_at) VALUES
(1, 1, 4, 'LCS1-20250601-0001', 'Nabila Customer', '084444444444', 'takeaway', 'completed', 46200, NOW() - INTERVAL 2 DAY),
(2, 1, NULL, 'LCS1-20250601-0002', 'Dimas', '086666666666', 'dine_in', 'completed', 28600, NOW() - INTERVAL 1 DAY),
(3, 2, 5, 'LCS2-20250601-0001', 'Raka Customer', '085555555555', 'delivery', 'completed', 93500, NOW() - INTERVAL 3 HOUR);

INSERT INTO order_details (order_id, menu_id, sauce_id, quantity, subtotal) VALUES
(1, 2, 2, 2, 38000),
(1, 8, NULL, 1, 5000),
(2, 13, 1, 1, 22000),
(2, 8, NULL, 1, 5000),
(3, 15, 5, 1, 87500);

INSERT INTO payments (order_id, payment_method, payment_status, amount_paid, paid_at) VALUES
(1, 'QRIS', 'paid', 46200, NOW() - INTERVAL 2 DAY),
(2, 'Cash', 'paid', 30000, NOW() - INTERVAL 1 DAY),
(3, 'Transfer Bank', 'paid', 93500, NOW() - INTERVAL 2 HOUR);

INSERT INTO reviews (user_id, order_id, rating, comment) VALUES
(4, 1, 5, 'Ayamnya crispy dan pedasnya pas.'),
(5, 3, 4, 'Paket rame cocok buat kantor.');

SET FOREIGN_KEY_CHECKS = 1;
