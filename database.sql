DROP DATABASE IF EXISTS shopstore_db;
CREATE DATABASE shopstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopstore_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    ordered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sessions (
    sid VARCHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@shopstore.local', 'PLACEHOLDER', 'admin'),
    ('ahmed', 'ahmed@shopstore.local', 'PLACEHOLDER', 'customer'),
    ('jalil', 'jalil@shopstore.local',   'PLACEHOLDER', 'customer');

INSERT INTO products (name, description, price) VALUES
    ('Wireless Mouse',    'wireless mouse with USB receiver.', 11.5),
    ('Mechanical Keyboard',' mechanical keyboard with red switches.',  73.99),
    ('USB-C cable',         'USB-C cable 3M.',  9.33),
    ('HD Webcam',         '1080p webcam with built-in microphone.', 45),
    ('Laptop Stand',      'laptop stand for desk use.',     21);
