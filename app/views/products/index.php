<?php
$products = $data['products'] ?? [];
$categories = $data['categories'] ?? [];
ob_start(); # Start the output buffer
?>
<div class="container">

  <div class="header">
    <h2>Products</h2>
    <button onclick="openModal()">+ Add Product</button>
  </div>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Product Name</th>
          <th>SKU</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Price</th>
          <th>Unit</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($products as $product): ?>
          <tr>
            <td><?= htmlspecialchars($product['product_name']) ?></td>
            <td><?= htmlspecialchars($product['sku']) ?></td>
            <td><?= htmlspecialchars($product['category']) ?></td>
            <td><?= $product['current_stock'] ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['unit'] ?></td>
            <td><?= $product['status'] ?></td>
            <td><?= $product['created_at'] ?></td>
            <td>
              <button>Edit</button>
              <button style="background:#eb5757;">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal -->
<div id="modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Product</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>

    <form action="products/submit" method="post">
      <label>Product Name</label>
      <input type="text" name="name" required />

      <label>Category</label>
      <select name="category_id" required>
        <option value="">Select</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category['id'] ?>">
            <?= htmlspecialchars($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>SKU</label>
      <input type="text" name="sku" required />

      <label>Price</label>
      <input type="number" name="price" step="0.01" required />

      <label>Unit</label>
      <select name="unit" required>
        <option value="">Select</option>
        <option value="KG">KG</option>
        <option value="PCS">PCS</option>
        <option value="BOX">BOX</option>
      </select>

      <button type="submit">Create</button>
    </form>
  </div>
</div>


<?php
$content = ob_get_clean(); # Get the buffered content and clean the buffer
require_once __DIR__ . '/../layouts/layout.php'; # Include the layout which will use the $content variable
?>