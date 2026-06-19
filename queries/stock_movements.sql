
CREATE TABLE stock_movements(
    id INT AUTO_INCREMENT PRIMARY KEY, 
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    -- FOR TRANSFER MOVEMENT
    reference_warehouse_id INT, -- For transfer movements, this will reference the other warehouse involved in the transfer
    transfer_group_id VARCHAR(255), -- To link related transfer movements together


    direction ENUM('IN', 'OUT') NOT NULL,
    movement_type ENUM (
        'STARTING_STOCK',
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



            

SELECT
    p.id,
    p.name AS product,
    p.sku,
    p.category_id,
    c.name AS category,
    ss.warehouse_id,
    w.name AS warehouse,

    -- Opening stock
    COALESCE(SUM(
        CASE WHEN sm.movement_type = 'STARTING_STOCK'
        THEN sm.quantity ELSE 0 END
    ),0) AS opening_stock,

    -- Received
    COALESCE(SUM(
        CASE WHEN sm.movement_type IN ('STOCK_IN','PURCHASE')
        THEN sm.quantity ELSE 0 END
    ),0) AS received,

    -- Sold
    COALESCE(SUM(
        CASE WHEN sm.movement_type='STOCK_OUT'
        THEN sm.quantity ELSE 0 END
    ),0) AS sold,

    -- Transfer In
    COALESCE(SUM(
        CASE WHEN sm.movement_type='TRANSFER_IN'
        THEN sm.quantity ELSE 0 END
    ),0) AS transfered_in,

    -- Transfer Out
    COALESCE(SUM(
        CASE WHEN sm.movement_type='TRANSFER_OUT'
        THEN sm.quantity ELSE 0 END
    ),0) AS transfered_out,

    -- Adjusted In
    COALESCE(SUM(
        CASE WHEN sm.movement_type='ADJUSTMENT_IN'
        THEN sm.quantity ELSE 0 END
    ),0) AS adjusted_in,

    -- Adjusted Out
    COALESCE(SUM(
        CASE WHEN sm.movement_type='ADJUSTMENT_OUT'
        THEN sm.quantity ELSE 0 END
    ),0) AS adjusted_out,

    -- Returned
    COALESCE(SUM(
        CASE WHEN sm.movement_type='RETURN'
        THEN sm.quantity ELSE 0 END
    ),0) AS returned,

    -- Damaged
    COALESCE(SUM(
        CASE WHEN sm.movement_type='DAMAGE'
        THEN sm.quantity ELSE 0 END
    ),0) AS damaged,

    -- Expired
    COALESCE(SUM(
        CASE WHEN sm.movement_type='EXPIRE'
        THEN sm.quantity ELSE 0 END
    ),0) AS expired,

    -- Current Stock
    COALESCE(ss.quantity,0) AS current_stock,

    -- Unit Cost 
    p.price AS unit_cost,

    -- Stock value 
    (p.price * COALESCE(ss.quantity,0)) AS stock_value



FROM products p

JOIN stock_snapshots ss
ON ss.product_id = p.id

JOIN categories c
ON c.id = p.category_id

JOIN warehouses w
ON w.id = ss.warehouse_id

LEFT JOIN stock_movements sm
ON sm.product_id = ss.product_id
AND sm.warehouse_id = ss.warehouse_id

GROUP BY
    p.id,
    p.name,
    p.sku,
    p.category_id,
    c.name,
    ss.warehouse_id,
    w.name
