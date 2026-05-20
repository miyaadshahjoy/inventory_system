CREATE TABLE stock_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (product_id, warehouse_id)
);


ALTER TABLE stock_snapshots
ADD FOREIGN KEY(product_id)
REFERENCES products(id)
ON DELETE CASCADE;

ALTER TABLE stock_snapshots
ADD FOREIGN KEY(warehouse_id)
REFERENCES warehouses(id)
ON DELETE CASCADE;
