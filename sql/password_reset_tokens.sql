CREATE TABLE IF NOT EXISTS PASSWORD_RESET_TOKEN (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    account_type VARCHAR(20) NOT NULL,
    account_identifier VARCHAR(255) NOT NULL,
    reset_email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reset_email (reset_email),
    INDEX idx_reset_identifier (account_type, account_identifier)
);
