CREATE TABLE purchase_order_items(
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    order_quantity INT NOT NULL CHECK(order_quantity > 0),
    received_quantity INT NOT NULL DEFAULT 0
    CHECK(received_quantity >= 0 AND received_quantity <= order_quantity),
    unit_price DECIMAL(10, 2) NOT NULL CHECK(unit_price > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uk_po_product
    UNIQUE(purchase_order_id, product_id)

);

ALTER TABLE purchase_order_items
ADD FOREIGN KEY(purchase_order_id)
REFERENCES purchase_orders(id)
ON UPDATE CASCADE;

ALTER TABLE purchase_order_items
ADD FOREIGN KEY(product_id)
REFERENCES products(id)
ON UPDATE CASCADE;

CREATE INDEX idx_po_id
ON purchase_order_items(purchase_order_id);

CREATE INDEX idx_product_id
ON purchase_order_items(product_id);