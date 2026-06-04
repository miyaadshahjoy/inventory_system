CREATE TABLE suppliers(
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL, 
    phone VARCHAR(255) UNIQUE NOT NULL, 
    address TEXT NOT NULL,
    supplier_status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
)

CREATE INDEX idx_supplier_name
ON suppliers(supplier_name)

CREATE INDEX idx_supplier_status
ON suppliers(supplier_status);