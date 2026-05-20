CREATE TABLE returns(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    reason TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE returns
ADD FOREIGN KEY (product_id)
REFERENCES products(id)
ON DELETE CASCADE;

ALTER TABLE returns
ADD FOREIGN KEY (warehouse_id)
REFERENCES warehouses(id)
ON DELETE CASCADE;

ALTER TABLE returns
ADD FOREIGN KEY (created_by)
REFERENCES users(id)
ON DELETE CASCADE;

