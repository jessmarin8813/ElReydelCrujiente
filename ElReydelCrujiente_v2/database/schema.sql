-- Database Schema for ElReydelCrujiente v2

CREATE DATABASE IF NOT EXISTS elreydelcrujiente_v2;
USE elreydelcrujiente_v2;

-- Users & Roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier', 'kitchen') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_usd DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    category VARCHAR(50) NOT NULL, -- e.g., 'burgers', 'drinks', 'sides'
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Combos (Bundles)
CREATE TABLE IF NOT EXISTS combos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_usd DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Combo Items (M-to-N relationship between Combos and Products)
CREATE TABLE IF NOT EXISTS combo_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    combo_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10), -- Can be 'Takeout' or Table #
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    total_usd DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_bs DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    exchange_rate DECIMAL(10, 2) NOT NULL, -- Rate at the time of order
    created_by INT, -- User who created the order (optional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT, -- Nullable if it's a combo
    combo_id INT, -- Nullable if it's a single product
    quantity INT NOT NULL DEFAULT 1,
    price_at_time_usd DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE SET NULL
);

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method ENUM('cash_usd', 'cash_bs', 'pago_movil', 'zelle', 'card') NOT NULL,
    amount_paid DECIMAL(12, 2) NOT NULL, -- In the currency paid
    currency ENUM('USD', 'VES') NOT NULL,
    exchange_rate DECIMAL(10, 2) NOT NULL,
    amount_usd DECIMAL(10, 2) NOT NULL, -- USD equivalent for reporting
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Daily Rates (BCV / Parallel)
CREATE TABLE IF NOT EXISTS daily_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate DECIMAL(10, 2) NOT NULL,
    source VARCHAR(50) DEFAULT 'BCV',
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Initial Data Seeding (Optional)
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Insert initial BCV rate (example: 45.50 Bs per USD)
INSERT INTO daily_rates (rate, source, updated_by) VALUES (45.50, 'BCV', 1);
