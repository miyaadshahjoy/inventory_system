
CREATE TABLE stock_movements(
    id INT AUTO_INCREMENT PRIMARY KEY, 
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    -- FOR TRANSFER MOVEMENT
    reference_warehouse_id INT, -- For transfer movements, this will reference the other warehouse involved in the transfer
    transfer_group_id VARCHAR(255), -- To link related transfer movements together


    direction ENUM('IN', 'OUT') NOT NULL,
    movement_type ENUM (
        'STOCK_IN',
        'STOCK_OUT',
        'TRANSFER_IN',
        'TRANSFER_OUT',
        'ADJUSTMENT_IN',
        'ADJUSTMENT_OUT',
        'RETURN',
        'DAMAGE',
        'EXPIRE',
        'PURCHASE'
    ) NOT NULL,
    quantity INT NOT NULL CHECK( quantity > 0),
    resulting_stock INT NOT NULL CHECK( resulting_stock >= 0),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE stock_movements
ADD FOREIGN KEY(product_id)
REFERENCES products(id)
ON DELETE CASCADE;

ALTER TABLE stock_movements
ADD FOREIGN KEY(warehouse_id)
REFERENCES warehouses(id)
ON DELETE CASCADE;

ALTER TABLE stock_movements
ADD FOREIGN KEY(reference_warehouse_id)
REFERENCES warehouses(id)
ON DELETE CASCADE;

ALTER TABLE stock_movements
ADD FOREIGN KEY(created_by)
REFERENCES users(id)
ON DELETE CASCADE;