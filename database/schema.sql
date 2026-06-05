SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS operating_hours;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS sauces;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS branches;

CREATE TABLE branches (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  address    TEXT,
  phone      VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) UNIQUE,
  phone      VARCHAR(20) NOT NULL,
  password   VARCHAR(255),
  role       VARCHAR(20) DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(80) NOT NULL,
  slug      VARCHAR(80) UNIQUE NOT NULL,
  icon      VARCHAR(100),
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sauces (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  price_extra DECIMAL(10,2) DEFAULT 0.00,
  is_active   TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menus (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name        VARCHAR(150) NOT NULL,
  slug        VARCHAR(150) UNIQUE,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  is_active   TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE carts (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  session_id VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  cart_id  INT NOT NULL,
  menu_id  INT NOT NULL,
  sauce_id INT,
  quantity INT NOT NULL DEFAULT 1,
  notes    TEXT,
  FOREIGN KEY (cart_id)  REFERENCES carts(id)  ON DELETE CASCADE,
  FOREIGN KEY (menu_id)  REFERENCES menus(id)  ON DELETE CASCADE,
  FOREIGN KEY (sauce_id) REFERENCES sauces(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  branch_id      INT NOT NULL,
  user_id        INT,
  order_code     VARCHAR(20) UNIQUE NOT NULL,
  customer_name  VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  order_type     VARCHAR(20) NOT NULL,
  status         VARCHAR(20) DEFAULT 'pending',
  total          DECIMAL(10,2) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
  FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_details (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_id  INT NOT NULL,
  sauce_id INT,
  quantity INT NOT NULL DEFAULT 1,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id)  REFERENCES orders(id)  ON DELETE CASCADE,
  FOREIGN KEY (menu_id)   REFERENCES menus(id)   ON DELETE RESTRICT,
  FOREIGN KEY (sauce_id)  REFERENCES sauces(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  order_id       INT UNIQUE NOT NULL,
  payment_method VARCHAR(50),
  payment_status VARCHAR(50) DEFAULT 'unpaid',
  amount_paid    DECIMAL(10,2),
  paid_at        TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  order_id   INT,
  rating     TINYINT NOT NULL,
  comment    TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE SET NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  branch_id INT NOT NULL,
  `key`     VARCHAR(100) NOT NULL,
  `value`   TEXT,
  UNIQUE KEY unique_branch_key (branch_id, `key`),
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE operating_hours (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  branch_id   INT NOT NULL,
  day_of_week TINYINT NOT NULL,
  open_time   TIME,
  close_time  TIME,
  is_closed   TINYINT(1) DEFAULT 0,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE banners (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  branch_id INT NOT NULL,
  title     VARCHAR(150),
  image     VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
