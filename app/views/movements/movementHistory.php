<?php
$movements = $data['movements'] ?? [];
$products = $data['products'] ?? [];
$warehouses = $data['warehouses'] ?? [];
$total_movements = $data['total_movements'] ?? 0;
$limit = $data['limit'] ?? 5;
$page = $data['page'] ?? 1;
$total_pages = ceil($total_movements / $limit);

ob_start();
?>
<div class="container">
  <div class="container-header">
    <h2>Stock Movement History</h2>
    <div class="action-buttons">

      <button onclick="openModal()">
        + Add new Movement
      </button>
      <button onclick="openTransferModal()">
        + Add transfer Movement
      </button>
      <?php if ($_SESSION['user']['role'] === 'ADMIN'): ?>
        <button onclick="openAdjustmentModal()">
          + Add adjustment Movement
        </button>
      <?php endif; ?>

    </div>
  </div>

  <!-- TODO: Filter data -->
  <!--  
  # Date -> date
  # Product (name and sku) -> product_name, product_sku
  # Warehouse name -> warehouse_name
  # Movement Type  -> movement_type
  # Direction -> direction
  # Quantity -> quantity
  # Resulting stock -> resulting_stock
  # Created by -> created_by
  # Notes -> notes
  -->

  <?php if (empty($movements)): ?>
    <p>No movements found.</p>
  <?php endif; ?>
  <?php if (!empty($movements)): ?>
    <!-- Export data as CSV -->
    <div class="export-data">
      <!-- Export CSV -->
      <?php
      if (isset($_GET['url']))
        unset($_GET['url']);
      if (isset($_GET['page']))
        unset($_GET['page']);
      ?>
      <a href="/stock-movements/export?<?= http_build_query($_GET) ?>">
        <button>
          Export CSV
        </button>
      </a>
    </div>
    <!-- Show movements data in a table -->
    <div class="table-wrapper">

      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Product</th>
            <th>Warehouse</th>
            <th>Type</th>
            <th>Direction</th>
            <th>Quantity</th>
            <th>Resulting Stock</th>
            <th>Created by</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movements as $movement): ?>
            <tr>
              <td><?= $movement['date']; ?></td>
              <td><?= $movement['product_name']; ?> (sku-<?= $movement['product_sku']; ?>)</td>
              <td><?= $movement['warehouse_name']; ?></td>
              <td><?= $movement['movement_type']; ?></td>
              <td><?= $movement['direction']; ?></td>
              <td><?= $movement['quantity']; ?></td>
              <td>
                <?= $movement['resulting_stock']; ?>
              </td>
              <td><?= $movement['created_by']; ?></td>
              <td><?= $movement['notes']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Implementing pagination buttons -->
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="/stock-movements?page=<?= $page - 1 ?>" class="button-pagination">
          <button>
            Prev
          </button>
        </a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="/stock-movements?page=<?= $i ?>" class="button-pagination <?= $page === $i ? 'active' : '' ?>">
          <button>
            <?= $i ?>
          </button>
        </a>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
        <a href="/stock-movements?page=<?= $page + 1 ?>" class="button-pagination">
          <button>
            Next
          </button>
        </a>
      <?php endif; ?>

    </div>


  <?php endif; ?>
</div>

<!-- 
# Create movement modal
-->
<div id="modal" class="modal movement-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Movement</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>


    <form class="form movement-form" action="/stock-movements/form-submit" method="post">
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
            <option value="EXPIRE">Expire</option>
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
      <div>
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="5"></textarea>
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

        <!-- Movement type -->
        <div>
          <label for="movement_type">Movement Type</label>
          <select name="movement_type" id="movement_type" disabled>
            <option value="">Transfer</option>
          </select>
        </div>

        <!-- Quantity: required -->
        <div>

          <label for="quantity">Quantity</label>
          <input type="number" name="quantity" id="quantity" required />
        </div>
      </div>

      <!-- Notes: Optional -->
      <div>
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="5"></textarea>
      </div>
      <button type="submit">Create Transfer Movement</button>
    </form>
  </div>
</div>


<!-- Create adjustment movement modal -->
<div id="adjustmentModal" class="modal adjustment-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Adjustment Movement</h3>
      <span class="close" onclick="closeAdjustmentModal()">×</span>
    </div>


    <form class="form adjustment-form" action="/stock-movements/adjustment/form-submit" method="post">
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
            <option value="ADJUSTMENT_IN">Adjustment-in</option>
            <option value="ADJUSTMENT_OUT">Adjustment-out</option>
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
      <div>
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="5" required></textarea>
      </div>
      <button type="submit">Create Adjustment </button>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layouts/layout.php';
?>