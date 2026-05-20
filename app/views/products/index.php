<?php
$products = $data['products'] ?? [];
$categories = $data['categories'] ?? [];
ob_start(); # Start the output buffer
?>

<!-- Showing product list -->
<div class="container">
  <div class="container-header">
    <h2>Products</h2>

    <!-- Add new product button -->
    <button onclick="openModal()">+ Add Product</button>
  </div>

  <div>
    <?php if (empty($products)): ?>
      <p>No products found.</p>
    <?php endif; ?>
    <?php if (!empty($products)): ?>
      <table>
        <!-- 
          # Product name | SKU | Category | Price | Total Stock | Status | Reorder | Updated | Actions 
        -->

        <thead>
          <tr>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Price</th>
            <th>Total Stock</th>
            <th>Status</th>
            <th>Reorder</th>
            <th>Last Updated</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><?= $product['product_name'] ?></td>
              <td><?= $product['sku'] ?></td>
              <td><?= $product['category'] ?></td>
              <td><?= $product['price'] ?></td>
              <td><?= $product['total_stock'] ?></td>
              <td class="productStatus" data-productId="<?= $product['id'] ?>"><?= $product['product_status'] ?></td>
              <td><?= $product['reorder_level'] ?></td>
              <td><?php $updated_at = new DateTime($product['updated_at']);
              echo $updated_at->format('Y-m-d H:i'); ?></td>
              <td>
                <div class="actions productActions <?= $product['product_status'] === 'INACTIVE' ? 'hide' : '' ?>"
                  data-productId="<?= $product['id'] ?>">
                  <button data-productId="<?= $product['id'] ?>" data-productName="<?= $product['product_name'] ?>"
                    data-productSKU="<?= $product['sku'] ?>" data-categoryID="<?= $product['category_id'] ?>"
                    data-productPrice="<?= $product['price'] ?>" data-productReorder="<?= $product['reorder_level'] ?>"
                    data-productUnit="<?= $product['unit'] ?>" onclick=" openProductUpdateModal(this)">Edit</button>
                  <button data-productId="<?= $product['id'] ?>" onclick="deleteProduct(this)">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<!-- 
# ADD NEW PRODUCT
 -->
<!-- Modal Window for adding new product -->
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
          <input type="number" name="price" step="0.5" placeholder="Enter price" required />
        </div>
      </div>

      <div class="form-group">
        <!-- Product reorder level -->
        <div>
          <label>Reorder Level</label>
          <input type="number" name="reorder_level" step="1" placeholder="Enter reorder level" required />
        </div>


        <!-- Product unit -->
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

<!-- 
# UPDATE PRODUCT
-->
<!-- Modal for updating a product -->
<div id="productUpdateModal" class="modal product-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Update Product</h3>
      <span class="close" onclick="closeProductUpdateModal()">×</span>
    </div>
    <form class="product-form" action="/products/update/form-submit" method="post">
      <input type="text" name="id" id="productId" hidden>
      <div class="form-group">
        <div>
          <label>Product Name</label>
          <input type="text" name="name" id="productName" placeholder="Enter product name" required />
        </div>
        <div>

          <label>Category</label>
          <select name="category_id" id="productCategory" required>
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
          <input type="text" name="sku" id="productSKU" placeholder="Enter SKU" required />
        </div>
        <div>
          <label>Price</label>
          <input type="number" name="price" id="productPrice" step="0.5" placeholder="Enter price" required />
        </div>
      </div>

      <div class="form-group">
        <div>
          <label>Reorder Level</label>
          <input type="number" name="reorder_level" id="productReorderLevel" step="1" placeholder="Enter reorder level"
            required />
        </div>
        <div>
          <label>Unit</label>
          <select name="unit" id="productUnit" required>
            <option value="">Select product unit</option>
            <option value="KG">KG</option>
            <option value="PCS">PCS</option>
            <option value="BOX">BOX</option>
          </select>
        </div>

      </div>

      <button type="submit">Update Product</button>
    </form>
  </div>
</div>


<?php
$content = ob_get_clean(); # Get the buffered content and clean the buffer
require_once __DIR__ . '/../layouts/layout.php'; # Include the layout which will use the $content variable
?>