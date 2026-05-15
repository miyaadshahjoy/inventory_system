<?php
$movements = $data['movements'] ?? [];
$products = $data['products'] ?? [];
ob_start();
?>
<div class="container">
  <div class="container-header">
    <h1>Stock Movement History</h1>
    <button onclick="openModal()">
      + Add new Movement
    </button>
  </div>

  <!-- TODO: Filter data -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Type</th>
        <th>Direction</th>
        <th>Created By</th>
        <th>Created At</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($movements as $movement): ?>
        <tr>
          <td><?= $movement['id']; ?></td>
          <td><?= $movement['product']; ?></td>
          <td><?= $movement['type']; ?></td>
          <td><?= $movement['direction']; ?></td>
          <td><?= $movement['created_by']; ?></td>
          <td><?php $created_at = new DateTime($movement['created_at']);
          echo $created_at->format('Y-m-d H:i'); ?></td>
          <td><?= $movement['notes']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


<div id="modal" class="modal movement-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Movement</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>


    <form class="movement-form" action="/stock-movements/submit-movement" method="post">
      <!-- Form fields will go here -->
      <!-- Product selection: Dropdown, required -->
      <div class="form-group">

        <div>
          <label for="product_id">Products</label>
          <select name="product_id" id="product_id" required>
            <?php foreach ($products as $product): ?>
              <option value="<?= $product['id'] ?>">
                <?= $product['name'] . " (sku-" . $product['sku'] . ")" ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <!-- Movement type: Dropdown, required -->
        <div>

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
        </div>
      </div>

      <div class="form-group">

        <!-- Quantity: required -->
        <div>

          <label for="quantity">Quantity</label>
          <input type="number" name="quantity" id="quantity" required />
        </div>
        <!-- Notes: Optional -->
        <div>

          <label for="notes">Notes</label>
          <textarea name="notes" id="notes"></textarea>
        </div>
      </div>
      <button type="submit">Create Movement</button>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layouts/layout.php';
?>