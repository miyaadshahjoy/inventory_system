<?php
require_once __DIR__ . '/../controllers/ProductController.php';
$controller = new ProductController();
$products = $controller->getAllProducts();
print_r($products); ?>

<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Movement</title>
  </head>

  <body>
    <h2>Create Movement</h2>
    <form action="/stock-movements/submit-movement" method="post">
      <!-- Form fields will go here -->
      <!-- Product selection: Dropdown, required -->
      <label for="product_id">Products</label>
      <select name="product_id" id="product_id" required>
        <?php foreach ($products as $product): ?>
          <option value="<?= $product['id'] ?>">
            <?= $product['name'] . " (sku-" . $product['sku'] . ")" ?>
          </option>
        <?php endforeach ?>
      </select>

      <!-- Movement type: Dropdown, required -->
      <label for="movement_type">Movement Type</label>
      <select name="movement_type" id="movement_type" required>
        <option value="STOCK_IN">Stock-in</option>
        <option value="STOCK_OUT">Stock-out</option>
        <option value="TRANSFER_IN">Transfer-in</option>
        <option value="TRANSFER_OUT">Transfer-out</option>
        <option value="ADJUSTMENT_IN">Adjustment-in</option>
        <option value="ADJUSTMENT_OUT">Adjustment-out</option>
        <option value="EXPIRE">Expire</option>
        <option value="RETURN">Return</option>
        <option value="DAMAGE">Damage</option>
      </select>

      <!-- Quantity: required -->
      <label for="quantity">Quantity</label>
      <input type="number" name="quantity" id="quantity" required />
      <!-- Notes: Optional -->
      <label for="notes">Notes</label>
      <textarea name="notes" id="notes"></textarea>
      <button type="submit">Create Movement</button>
    </form>
  </body>

</html>