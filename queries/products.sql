CREATE TABLE products(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    sku VARCHAR(255) UNIQUE NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    unit ENUM('PCS', 'KG', 'LITRE', 'METER', 'BOX', 'SET', 'PACK', 'UNIT') NOT NULL,
    reorder_level INT DEFAULT 0,
    product_status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP

);

CREATE INDEX idx_prod_category
ON products(category_id);


CREATE INDEX idx_prod_price
ON products(price);

CREATE INDEX idx_prod_status
ON products(product_status);

CREATE INDEX idx_prod_created
ON products(created_at);

ALTER TABLE products
ADD FOREIGN KEY(category_id)
REFERENCES categories(id)
ON DELETE CASCADE;