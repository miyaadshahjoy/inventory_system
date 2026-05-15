<?php
$products = $data['products'] ?? [];
$categories = $data['categories'] ?? [];
ob_start(); # Start the output buffer
?>
<div class="container">

  <div class="container-header">
    <h2>Products</h2>
    <button onclick="openModal()">+ Add Product</button>
  </div>

  <div>
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
            <td><?php $created_at = new DateTime($product['created_at']);
            echo $created_at->format('Y-m-d H:i'); ?></td>
            <td>
              <button>Edit</button>
              <button>Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Window -->
<div id="modal" class="modal product-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Product</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>

    <form class="product-form" action="products/form-submit" method="post">
      <div class="form-group">
        <div>
          <label>Product Name</label>
          <input type="text" name="name" placeholder="Enter product name" required />
        </div>
        <div>

          <label>Category</label>
          <select name="category_id" required>
            <option value="">Select product category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['id'] ?>">
                <?= htmlspecialchars($category['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <div>

          <label>SKU</label>
          <input type="text" name="sku" placeholder="Enter SKU" required />
        </div>
        <div>

          <label>Price</label>
          <input type="number" name="price" step="0.01" placeholder="Enter price" required />
        </div>
      </div>

      <div class="form-group">
        <div>

          <label>Unit</label>

          <select name="unit" required>
            <option value="">Select product unit</option>
            <option value="KG">KG</option>
            <option value="PCS">PCS</option>
            <option value="BOX">BOX</option>
          </select>
        </div>

      </div>
      <button type="submit">Add new product</button>
    </form>
  </div>
</div>


<?php
$content = ob_get_clean(); # Get the buffered content and clean the buffer
require_once __DIR__ . '/../layouts/layout.php'; # Include the layout which will use the $content variable
?>