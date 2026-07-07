SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO branches (id, name, address, phone) VALUES
(1, 'Lapak Chicken Seturan Pusat', 'Jl. Seturan Raya No. 12, Caturtunggal, Depok, Sleman, Yogyakarta', '081234567890'),
(2, 'Lapak Chicken Seturan Babarsari', 'Jl. Babarsari No. 88, Caturtunggal, Depok, Sleman, Yogyakarta', '081234567891');

INSERT INTO users (id, name, email, phone, password, role) VALUES
(1, 'Owner Lapak Chicken', 'admin@lapakchicken.com', '081111111111', '$2y$10$b3I0eBxNo167yYReummD9edWL9EZM.uDwiTFduzGidMY0Y0xFVM0u', 'admin'),
(2, 'Kasir Seturan', 'kasir@lapakchicken.com', '082222222222', '$2y$10$APSWlhlz4jD/oOVOLOnxhezjHOmm/478CASErM8mcoL20alboBQyO', 'kasir'),
(3, 'Dapur Seturan', 'dapur@lapakchicken.com', '083333333333', '$2y$10$OG.LzW7CefaVSg4dzFnn3e3axR0W6KTh4Jfaafd9xD96bQrkJsoiu', 'dapur'),
(4, 'Nabila Customer', 'nabila@example.com', '084444444444', '$2y$10$PTdNBZf9R61AQJbevuvEO.rIuCWfF8SMZNivaF6rzadPxpzZRui.S', 'customer'),
(5, 'Raka Customer', 'raka@example.com', '085555555555', '$2y$10$PTdNBZf9R61AQJbevuvEO.rIuCWfF8SMZNivaF6rzadPxpzZRui.S', 'customer');

INSERT INTO categories (id, name, slug, icon, is_active) VALUES
(1, 'Ayam & Steak', 'ayam-steak', 'fa-solid fa-drumstick-bite', 1),
(2, 'Special Sauce & New', 'special-sauce', 'fa-solid fa-fire-flame-curved', 1),
(3, 'Menu Paket (Lapchick)', 'menu-paket', 'fa-solid fa-box-open', 1),
(4, 'Topping & Snack', 'topping-snack', 'fa-solid fa-cookie-bite', 1),
(5, 'Minuman', 'minuman', 'fa-solid fa-cup-straw', 1);

INSERT INTO sauces (id, name, price_extra, is_active) VALUES
(1, 'Original', 0, 1),
(2, 'BBQ', 0, 1),
(3, 'Cheese (Keju)', 0, 1),
(4, 'Spicy BBQ', 0, 1),
(5, 'Mushroom', 0, 1),
(6, 'Hot & Spicy (Pedas)', 0, 1),
(7, 'Black Pepper', 0, 1),
(8, 'Sambal Matah', 0, 1),
(9, 'Sambal Bawang (Geprek)', 0, 1),
(10, 'Salted Egg Sauce', 0, 1);

INSERT INTO menus (id, category_id, name, slug, description, price, image_url, is_active, stock) VALUES
(1, 1, 'Chicken Katsu', 'chicken-katsu', 'Ayam katsu renyah khas Lapak Chicken dengan pilihan saus favoritmu (+4k UP).', 17000, 'img/Menu/Katsu.jpeg', 1, 50),
(2, 1, 'Chicken Pokpok', 'chicken-pokpok', 'Potongan ayam crispy tanpa tulang, renyah di luar lembut di dalam (+3k UP).', 19000, 'img/Menu/Chicken Pok Pok.jpeg', 1, 50),
(3, 1, 'Crispy Chicken Steak', 'crispy-chicken-steak', 'Steak ayam renyah disajikan dengan saus pilihan khas Lapak Chicken.', 23000, 'img/Menu/Crispy Chicken Steak.jpeg', 1, 40),
(4, 1, 'Chicken Pokpok Sambal Matah', 'chicken-pokpok-sambal-matah', 'Chicken pokpok renyah berpadu dengan kesegaran sambal matah khas Bali.', 20000, 'img/Menu/Pok Pok Sambal Matah.jpeg', 1, 35),
(5, 1, 'Chicken Pokpok Geprek Bawang', 'chicken-pokpok-geprek-bawang', 'Chicken pokpok digeprek dengan sambal bawang pedas gurih nampol.', 19000, 'img/Menu/Ayam Geprek.jpeg', 1, 35),
(6, 2, 'Chicken Cordon Bleu', 'chicken-cordon-bleu', 'NEW! Ayam gulung isi keju lumer dan smoked beef yang renyah dan gurih.', 32000, 'img/Menu/Cordon Blue.jpeg', 1, 25),
(7, 2, 'Salted Egg Chicken', 'salted-egg-chicken', 'BEST SELLER! Chicken pokpok disiram saus telur asin gurih creamy + telur ceplok (+3k UP).', 22000, 'img/Menu/Salted Egg.jpeg', 1, 30),
(8, 3, 'Lapchick 1 (Katsu Medium)', 'lapchick-1-katsu-medium', 'Paket Hemat: Nasi + Salad + 1 Slice Chicken Katsu + Pilihan Saus + Es Teh.', 20000, 'img/Menu/Katsu.jpeg', 1, 50),
(9, 3, 'Lapchick 2 (Chicken Katsu)', 'lapchick-2-chicken-katsu', 'Paket Lengkap: Nasi + Salad + Chicken Katsu + Pilihan Saus + Es Teh.', 21000, 'img/Menu/Katsu.jpeg', 1, 50),
(10, 3, 'Lapchick 3 (Chicken Pokpok)', 'lapchick-3-chicken-pokpok', 'Paket Favorit: Nasi + Salad + Chicken Pokpok + Pilihan Saus + Es Teh.', 22000, 'img/Menu/Chicken Pok Pok.jpeg', 1, 50),
(11, 3, 'Lapchick 4 (Pokpok Matah)', 'lapchick-4-pokpok-matah', 'Paket Pedas Segar: Nasi + Salad + Chicken Pokpok Sambal Matah + Es Teh.', 23000, 'img/Menu/Pok Pok Sambal Matah.jpeg', 1, 40),
(12, 3, 'Lapchick 5 (Pokpok Bawang)', 'lapchick-5-pokpok-bawang', 'Paket Geprek: Nasi + Salad + Chicken Pokpok Sambal Bawang (Lvl 1-5) + Es Teh.', 22000, 'img/Menu/Ayam Geprek.jpeg', 1, 40),
(13, 3, 'Lapchick 6 (Crispy Steak)', 'lapchick-6-crispy-steak', 'Paket Steak: Hotplate Crispy Chicken Steak + Salad + Telur + Saus + Es Teh.', 26000, 'img/Menu/Crispy Chicken Steak.jpeg', 1, 30),
(14, 3, 'Lapchick 7 (Salted Egg)', 'lapchick-7-salted-egg', 'Paket Creamy: Nasi + Salad + Telur + Chicken Pokpok Saus Salted Egg + Es Teh.', 25000, 'img/Menu/Salted Egg.jpeg', 1, 30),
(15, 3, 'Lapchick 8 (Cordon Bleu)', 'lapchick-8-cordon-bleu', 'Paket Premium: Nasi / French Fries + Salad + 2 Chicken Cordon Bleu + Es Teh.', 35000, 'img/Menu/Cordon Blue.jpeg', 1, 20),
(16, 4, 'Crispy Chicken Skin', 'crispy-chicken-skin', 'Kulit ayam goreng super renyah dengan bumbu Hot & Spicy yang gurih.', 10000, 'img/Menu/Crispy Chicken Skin.jpeg', 1, 40),
(17, 4, 'French Fries', 'french-fries', 'Kentang goreng renyah disajikan dengan saus Spicy & Mayo.', 12000, '', 1, 40),
(18, 4, '1 Slice Chicken Katsu', '1-slice-chicken-katsu', 'Tambahan 1 potong chicken katsu renyah berbumbu Hot & Spicy.', 10000, '', 1, 40),
(19, 4, 'Egg (Telur)', 'egg-telur', 'Tambahan telur ceplok atau dadar untuk pelengkap hidanganmu.', 5000, '', 1, 100),
(20, 5, 'Es Teh Manis', 'es-teh-manis', 'Teh manis dingin segar alami.', 5000, '', 1, 100),
(21, 5, 'Lemon Tea', 'lemon-tea', 'Teh lemon dingin yang segar dan kaya vitamin C.', 8000, '', 1, 80),
(22, 5, 'Air Mineral', 'air-mineral', 'Air mineral botol segar dan higienis.', 4000, '', 1, 100);

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
(1, 'Promo Salted Egg Best Seller', 'img/Menu/Salted Egg.jpeg', 1),
(1, 'Paket Lapchick Hemat & Lengkap', 'img/Menu/Katsu.jpeg', 1),
(2, 'Crispy Chicken Steak Spesial', 'img/Menu/Crispy Chicken Steak.jpeg', 1),
(2, 'New Chicken Cordon Bleu', 'img/Menu/Cordon Blue.jpeg', 1);

INSERT INTO orders (id, branch_id, user_id, order_code, customer_name, customer_phone, order_type, status, total, created_at) VALUES
(1, 1, 4, 'LCS1-20250601-0001', 'Nabila Customer', '084444444444', 'takeaway', 'completed', 43000, NOW() - INTERVAL 2 DAY),
(2, 1, NULL, 'LCS1-20250601-0002', 'Dimas', '086666666666', 'dine_in', 'completed', 25000, NOW() - INTERVAL 1 DAY),
(3, 2, 5, 'LCS2-20250601-0001', 'Raka Customer', '085555555555', 'delivery', 'completed', 35000, NOW() - INTERVAL 3 HOUR);

INSERT INTO order_details (order_id, menu_id, sauce_id, quantity, subtotal) VALUES
(1, 2, 2, 2, 38000),
(1, 20, NULL, 1, 5000),
(2, 8, 1, 1, 20000),
(2, 20, NULL, 1, 5000),
(3, 15, 10, 1, 35000);

INSERT INTO payments (order_id, payment_method, payment_status, amount_paid, paid_at) VALUES
(1, 'QRIS', 'paid', 43000, NOW() - INTERVAL 2 DAY),
(2, 'Cash', 'paid', 25000, NOW() - INTERVAL 1 DAY),
(3, 'Transfer Bank', 'paid', 35000, NOW() - INTERVAL 2 HOUR);

INSERT INTO reviews (user_id, order_id, rating, comment) VALUES
(4, 1, 5, 'Chicken Pokpok dan saus BBQ-nya mantap banget! Crispy di luar lembut di dalam.'),
(5, 3, 5, 'Chicken Cordon Bleu keju lumernya juara, paket Lapchick 8 puas banget!');

SET FOREIGN_KEY_CHECKS = 1;
