CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    brand VARCHAR(100) DEFAULT NULL,
    model_name VARCHAR(100) DEFAULT NULL,
    product_condition VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
