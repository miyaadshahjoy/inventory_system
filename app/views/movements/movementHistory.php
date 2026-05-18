<?php
$movements = $data['movements'] ?? [];
$products = $data['products'] ?? [];
$warehouses = $data['warehouses'] ?? [];
ob_start();
?>
<div class="container">
  <div class="container-header">
    <h1>Stock Movement History</h1>
    <div>

      <button onclick="openModal()">
        + Add new Movement
      </button>
      <button onclick="openTransferModal()">
        + Add transfer Movement
      </button>
    </div>
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

<!-- Create movement modal -->
<div id="modal" class="modal movement-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Movement</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>


    <form class="movement-form" action="/stock-movements/form-submit" method="post">
      <!-- Form fields will go here -->
      <!-- Product selection: Dropdown, required -->
      <div class="form-group">

        <div>
          <label for="product_id">Products</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select product</option>
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
            <option value="">Select movement type</option>
            <option value="STOCK_IN">Stock-in</option>
            <option value="STOCK_OUT">Stock-out</option>
            <option value="ADJUSTMENT_IN">Adjustment-in</option>
            <option value="ADJUSTMENT_OUT">Adjustment-out</option>
            <option value="EXPIRE">Expire</option>
            <option value="RETURN">Return</option>
            <option value="DAMAGE">Damage</option>
          </select>
        </div>
      </div>

      <div class="form-group">

        <!-- Warehouse selection: Dropdown, required -->
        <div>

          <label for="warehouse_id">Warehouse</label>
          <select name="warehouse_id" id="warehouse_id">
            <option value="">Select warehouse</option>
            <?php foreach ($warehouses as $warehouse): ?>
              <option value="<?= $warehouse['id'] ?>">
                <?= $warehouse['name'] ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <!-- Quantity: required -->
        <div>

          <label for="quantity">Quantity</label>
          <input type="number" name="quantity" id="quantity" required />
        </div>
      </div>
      <!-- Notes: Optional -->
      <div class="form-group">

        <div>

          <label for="notes">Notes</label>
          <textarea name="notes" id="notes"></textarea>
        </div>
      </div>
      <button type="submit">Create Movement</button>
    </form>
  </div>
</div>



<!-- Create transfer movement modal -->
<div id="transferModal" class="modal transfer-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Transfer Movement</h3>
      <span class="close" onclick="closeTransferModal()">×</span>
    </div>


    <form class="transfer-form" action="/stock-movements/transfer/form-submit" method="post">
      <!-- Form fields will go here -->
      <!-- Product selection: Dropdown, required -->
      <div class="form-group">

        <div>
          <label for="product_id">Products</label>
          <select name="product_id" id="product_id" required>
            <option value="">Select product</option>
            <?php foreach ($products as $product): ?>
              <option value="<?= $product['id'] ?>">
                <?= $product['name'] . " (sku-" . $product['sku'] . ")" ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <div>

          <label for="movement_type">Movement Type</label>
          <select name="movement_type" id="movement_type" disabled>
            <option value="">Transfer</option>

          </select>
        </div>
      </div>

      <div class="form-group">
        <div>
          <label for="from_warehouse">From Warehouse</label>
          <select name="from_warehouse" id="from_warehouse" required>
            <option value="">Select source warehouse</option>
            <?php foreach ($warehouses as $warehouse): ?>
              <option value="<?= $warehouse['id'] ?>">
                <?= $warehouse['name'] ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div>
          <label for="to_warehouse">To Warehouse</label>
          <select name="to_warehouse" id="to_warehouse" required>
            <option value="">Select destination warehouse</option>
            <?php foreach ($warehouses as $warehouse): ?>
              <option value="<?= $warehouse['id'] ?>">
                <?= $warehouse['name'] ?>
              </option>
            <?php endforeach ?>
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
      <button type="submit">Create Transfer Movement</button>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layouts/layout.php';
?>