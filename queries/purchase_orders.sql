CREATE TABLE purchase_orders(
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(255) NOT NULL UNIQUE,
    supplier_id INT NOT NULL, 
    ordered_by INT NOT NULL, 
    po_status ENUM('PENDING', 'APPROVED', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED') DEFAULT 'PENDING',
    notes TEXT,
    expected_delivery_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);


ALTER TABLE purchase_orders
ADD FOREIGN KEY(supplier_id)
REFERENCES suppliers(id)
ON UPDATE CASCADE;

ALTER TABLE purchase_orders
ADD FOREIGN KEY(ordered_by)
REFERENCES users(id)
ON UPDATE CASCADE;

CREATE INDEX idx_po_number
ON purchase_orders(po_number);

CREATE INDEX idx_po_status
ON purchase_orders(po_status);

CREATE INDEX idx_supplier_id
ON purchase_orders(supplier_id);

