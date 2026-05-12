CREATE TABLE stock_movements(
    id INT AUTO_INCREMENT PRIMARY KEY, 
    product_id INT NOT NULL,
    direction ENUM('IN', 'OUT') NOT NULL,
    movement_type ENUM (
        'STOCK_IN',
        'STOCK_OUT',
        'TRANSFER_IN',
        'TRANSFER_OUT',
        'ADJUSTMENT',
        'RETURN',
        'DAMAGE',
        'EXPIRE'
    ) NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),

    transfer_group_id VARCHAR(255),
    notes VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);