-- ============================================
-- DATABASE: RetroGameHub
-- Description: Multi-seller retro game marketplace
-- ============================================

CREATE DATABASE IF NOT EXISTS RetroGameHub;
USE RetroGameHub;

-- ============================================
-- 1. Users Table
-- Roles: customer, seller, admin
-- ============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    roles JSON NOT NULL DEFAULT '["customer"]',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 2. Seller Applications
-- ============================================
CREATE TABLE seller_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    store_name VARCHAR(100) NOT NULL,
    contact_info VARCHAR(100) NOT NULL,
    payment_info VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================
-- 3. Sellers Table (optional, extra info)
-- ============================================
CREATE TABLE sellers (
    seller_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    store_name VARCHAR(100) NOT NULL,
    contact_info VARCHAR(100),
    payment_info VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================
-- 4. Products Table
-- ============================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    console VARCHAR(50) NOT NULL,
    category VARCHAR(50),
    `condition` ENUM('Mint', 'Used', 'Refurbished') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    demo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);

-- ============================================
-- 5. Customer Addresses
-- ============================================
CREATE TABLE addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100),
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================
-- 6. Orders Table
-- ============================================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_address_id) REFERENCES addresses(address_id) ON DELETE CASCADE
);

-- ============================================
-- 7. Order Items Table
-- ============================================
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);

-- ============================================
-- 8. Order Tracking / Shipping
-- ============================================
CREATE TABLE order_tracking (
    tracking_id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    courier_name VARCHAR(50),
    tracking_number VARCHAR(50),
    status ENUM('pending', 'shipped', 'in_transit', 'delivered') DEFAULT 'pending',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE CASCADE
);

-- ============================================
-- 9. Reviews Table
-- ============================================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    seller_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);

-- ============================================
-- 10. Commissions Table (Optional)
-- ============================================
CREATE TABLE commissions (
    commission_id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    seller_id INT NOT NULL,
    commission_percent DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    commission_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);

-- ============================================
-- 11. Couriers Table (Optional)
-- ============================================
CREATE TABLE couriers (
    courier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(50)
);

-- ============================================
-- Seller Settings Table
-- ============================================
CREATE TABLE seller_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL UNIQUE,
    notify_orders TINYINT DEFAULT 1,
    notify_reviews TINYINT DEFAULT 1,
    notify_messages TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);

-- ============================================
-- DEMO DATA INSERTION
-- ============================================

-- Admin User login: admin@retrohub.com / password123
INSERT INTO users (name, email, password, phone, roles, status) VALUES
('Admin Retro', 'admin@retrohub.com', '$2y$10$yIEcAbmNa3rtgJctM2FqKug6.ZAyWY3Wsy35opoj8nAQCvES.W1cG', '555-0001', '["admin"]', 'active');

-- Seller 1 login: seller1@retrohub.com / seller123
INSERT INTO users (name, email, password, phone, roles, status) VALUES
('RetroKing', 'seller1@retrohub.com', '$2y$10$N5d7qA/S3l1rVAxdslQ0O.f8ttHXrLLmu31/9gZ.ORfM8Z1Su1csK', '555-0002', '["seller"]', 'active');

-- Seller 2 login: seller2@retrohub.com / seller123
INSERT INTO users (name, email, password, phone, roles, status) VALUES
('PixelGames', 'seller2@retrohub.com', '$2y$10$N5d7qA/S3l1rVAxdslQ0O.f8ttHXrLLmu31/9gZ.ORfM8Z1Su1csK', '555-0003', '["seller"]', 'active');

-- Customer 1 login: gamer1@retrohub.com / gamer123
INSERT INTO users (name, email, password, phone, roles, status) VALUES
('GamerOne', 'gamer1@retrohub.com', '$2y$10$dQWe6Rgwp9j1fvw0c6y5AOHde2Dgwbx3QWp0cNwhp9g/1zLFNJEWe', '555-0004', '["customer"]', 'active');

-- Customer 2 login: gamer2@retrohub.com / gamer123
INSERT INTO users (name, email, password, phone, roles, status) VALUES
('GamerTwo', 'gamer2@retrohub.com', '$2y$10$dQWe6Rgwp9j1fvw0c6y5AOHde2Dgwbx3QWp0cNwhp9g/1zLFNJEWe', '555-0005', '["customer"]', 'active');

-- Create sellers from seller users
INSERT INTO sellers (user_id, store_name, contact_info, payment_info) VALUES
(2, 'RetroKing Store', 'contact@retroking.com', 'Stripe Account: sk_test_retroking'),
(3, 'PixelGames Store', 'contact@pixelgames.com', 'Stripe Account: sk_test_pixelgames');

-- Insert products for Seller 1 (RetroKing)
INSERT INTO products (seller_id, name, console, category, `condition`, price, stock, image_url, demo_url) VALUES
(1, 'Super Mario Bros', 'NES', 'Action', 'Mint', 20.00, 5, 'uploads/images/mario.jpg', 'uploads/demos/mario.mp4'),
(1, 'Pokemon Red', 'Gameboy', 'RPG', 'Used', 15.00, 3, 'uploads/images/pokemon.jpg', 'uploads/demos/pokemon.mp4'),
(1, 'The Legend of Zelda', 'NES', 'Adventure', 'Mint', 25.00, 2, 'uploads/images/zelda.jpg', 'uploads/demos/zelda.mp4');

-- Insert products for Seller 2 (PixelGames)
INSERT INTO products (seller_id, name, console, category, `condition`, price, stock, image_url, demo_url) VALUES
(2, 'Final Fantasy VII', 'PlayStation', 'RPG', 'Mint', 30.00, 4, 'uploads/images/ff7.jpg', 'uploads/demos/ff7.mp4'),
(2, 'Sonic the Hedgehog', 'Sega', 'Action', 'Used', 10.00, 6, 'uploads/images/sonic.jpg', 'uploads/demos/sonic.mp4'),
(2, 'Street Fighter II', 'SNES', 'Fighting', 'Mint', 22.00, 3, 'uploads/images/sf2.jpg', 'uploads/demos/sf2.mp4');

-- Add customer addresses
INSERT INTO addresses (user_id, recipient_name, address_line1, address_line2, city, state, postal_code, country, phone) VALUES
(4, 'GamerOne', '123 Game Street', 'Apt 4B', 'Los Angeles', 'CA', '90001', 'USA', '555-0004'),
(5, 'GamerTwo', '456 Console Ave', '', 'New York', 'NY', '10001', 'USA', '555-0005');

-- Insert couriers
INSERT INTO couriers (name, phone, email) VALUES
('FedEx Games', '1-800-FEDEX', 'support@fedex.com'),
('UPS Express', '1-800-742-5877', 'support@ups.com'),
('DHL Retro', '1-800-DHL-4444', 'support@dhl.com');

-- Create sample orders
INSERT INTO orders (user_id, total_amount, status, shipping_address_id) VALUES
(4, 45.00, 'delivered', 1),
(5, 30.00, 'shipped', 2);

-- Insert order items
INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES
(1, 1, 1, 1, 20.00),
(1, 2, 1, 1, 15.00),
(1, 3, 1, 1, 10.00),
(2, 4, 2, 1, 30.00);

-- Insert order tracking
INSERT INTO order_tracking (order_item_id, courier_name, tracking_number, status) VALUES
(1, 'FedEx Games', 'FDX123456789', 'delivered'),
(2, 'FedEx Games', 'FDX123456790', 'delivered'),
(3, 'FedEx Games', 'FDX123456791', 'delivered'),
(4, 'UPS Express', 'UPS987654321', 'in_transit');

-- Insert reviews
INSERT INTO reviews (product_id, user_id, seller_id, rating, comment) VALUES
(1, 4, 1, 5, 'Excellent condition! Exactly as described. Fast shipping!'),
(4, 5, 2, 4, 'Good product, arrived a bit late but in perfect condition.');

-- Create commissions
INSERT INTO commissions (order_item_id, seller_id, commission_percent, commission_amount) VALUES
(1, 1, 5.00, 1.00),
(2, 1, 5.00, 0.75),
(3, 1, 5.00, 0.50),
(4, 2, 5.00, 1.50);
