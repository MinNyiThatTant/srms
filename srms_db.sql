CREATE DATABASE srms_db;
USE srms_db;

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    price INT,
    category VARCHAR(100)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_no VARCHAR(50),
    total_price INT,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    item_name VARCHAR(255),
    price INT
);


ALTER TABLE menu ADD COLUMN image VARCHAR(255) DEFAULT 'default.jpg' AFTER price;

ALTER TABLE menu ADD COLUMN category VARCHAR(100) DEFAULT 'traditional food';

INSERT INTO menu (name, price, category, image) VALUES 
('Nasi Goreng', 15000, 'traditional food', 'nasi_goreng.jpg'),
('Mie Goreng', 12000, 'traditional food', 'mie_goreng.jpg'),
('Sate Ayam', 20000, 'traditional food', 'sate_ayam.jpg'),
('Es Teh Manis', 5000, 'beverages', 'es_teh_manis.jpg'),
('Jus Jeruk', 8000, 'beverages', 'jus_jeruk.jpg');

