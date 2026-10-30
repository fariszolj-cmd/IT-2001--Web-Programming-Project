CREATE DATABASE IF NOT EXISTS fariscommerce
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fariscommerce;

CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  product_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (product_id),
  KEY idx_products_category_id (category_id),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS carts (
  cart_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (cart_id),
  UNIQUE KEY uq_carts_user_id (user_id),
  CONSTRAINT fk_carts_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  order_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  order_date DATE NOT NULL,
  PRIMARY KEY (order_id),
  KEY idx_orders_user_id (user_id),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL,
  PRIMARY KEY (payment_id),
  UNIQUE KEY uq_payments_order_id (order_id),
  CONSTRAINT fk_payments_order
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  item_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  PRIMARY KEY (item_id),
  KEY idx_order_items_order_id (order_id),
  KEY idx_order_items_product_id (product_id),
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (user_id, name, email) VALUES
  (1, 'Amina Hadzic', 'amina@example.com'),
  (2, 'Marko Kovac', 'marko@example.com');

INSERT IGNORE INTO categories (category_id, name) VALUES
  (1, 'Electronics'),
  (2, 'Home and Garden');

INSERT IGNORE INTO products (product_id, category_id, name, price) VALUES
  (1, 1, 'Wireless Headphones', 79.99),
  (2, 1, 'Tablet', 329.00),
  (3, 2, 'Desk Lamp', 34.50);

INSERT IGNORE INTO carts (cart_id, user_id) VALUES
  (1, 1),
  (2, 2);

INSERT IGNORE INTO orders (order_id, user_id, order_date) VALUES
  (1, 1, '2026-10-01'),
  (2, 2, '2026-10-02');

INSERT IGNORE INTO payments (payment_id, order_id, status) VALUES
  (1, 1, 'paid'),
  (2, 2, 'pending');

INSERT IGNORE INTO order_items (item_id, order_id, product_id, quantity) VALUES
  (1, 1, 1, 1),
  (2, 1, 3, 2),
  (3, 2, 2, 1);