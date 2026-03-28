ALTER TABLE USER 
ADD COLUMN account_status ENUM('pending', 'approved', 'suspended') DEFAULT 'pending';