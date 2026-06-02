//main_categories
CREATE TABLE IF NOT EXISTS main_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

=======================================================

//sub_categories
CREATE TABLE IF NOT EXISTS sub_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    main_category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (main_category_id) REFERENCES main_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sub_category (main_category_id, name)
);

=======================================================
//menu changed
ALTER TABLE menu ADD COLUMN sub_category_id INT NULL;
ALTER TABLE menu ADD COLUMN main_category_id INT NULL;
ALTER TABLE menu ADD FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id) ON DELETE SET NULL;
