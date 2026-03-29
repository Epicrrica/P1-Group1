CREATE TABLE IF NOT EXISTS PRODUCT_REVIEW (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    reviewer_username VARCHAR(255) NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_product_review_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT uq_product_review UNIQUE (product_id, reviewer_username),
    CONSTRAINT fk_product_review_product
        FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS PRODUCT_FAVORITE (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_favorite UNIQUE (product_id, username),
    CONSTRAINT fk_product_favorite_product
        FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS PRODUCT_PURCHASE (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    buyer_username VARCHAR(255) NOT NULL,
    seller_username VARCHAR(255) NOT NULL,
    price_paid DECIMAL(10,2) NOT NULL,
    stripe_session_id VARCHAR(255) DEFAULT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_purchase_stripe_session UNIQUE (stripe_session_id),
    CONSTRAINT fk_product_purchase_product
        FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
        ON DELETE CASCADE
);
