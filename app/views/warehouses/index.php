<?php

$warehouses = $data["warehouses"] ?? [];
ob_start();

# Start the output buffer
?>

<!-- Showing warehouse list -->
<div class="container">
  <div class="container-header">
    <h2>Warehouses</h2>
    <!-- Add new warehouse button -->
    <button onclick="openModal()">+ Add new warehouse</button>
  </div>
  <div>
    <?php if (empty($warehouses)): ?>
      <div>
        No warehouses available
      </div>
    <?php endif; ?>
    <?php if (!empty($warehouses)): ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Location</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warehouses as $warehouse): ?>
              <tr>
                <td><?= $warehouse["name"] ?></td>
                <td><?= $warehouse["location"] ?></td>
                <td data-warehouseId="<?= $warehouse[
                    "id"
                ] ?>" class="warehouseStatus">
                  <?= $warehouse["warehouse_status"] ?>
                </td>
                <td>
                  <div data-warehouseId="<?= $warehouse["id"] ?>"
                    class="actions warehouseActions <?= $warehouse[
                        "warehouse_status"
                    ] === "INACTIVE"
                        ? "hide"
                        : "" ?>">
                    <button data-warehouseId="<?= $warehouse[
                        "id"
                    ] ?>" data-warehouseName="<?= $warehouse["name"] ?>"
                      data-warehouseLocation="<?= $warehouse[
                          "location"
                      ] ?>" onclick="openWarehouseUpdateModal(this)">
                      Edit
                    </button>
                    <a href="/warehouses/delete?id=<?= $warehouse["id"] ?>">
                      <button>
                        Delete
                      </button>
                    </a>
                    
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!--
# ADD NEW WAREHOUSE
  -->
<!-- Modal Window for adding new warehouse -->
<div id="modal" class="modal modal-narrow warehouse-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Warehouse</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>
    <form class="warehouse-form" action="warehouses/form-submit" method="post">
      <div>
        <label>Name</label>
        <input type="text" name="name" placeholder="Enter warehouse name" required />
      </div>
      <div>
        <label>Location</label>
        <input type="text" name="location" placeholder="Enter warehouse location" required />
      </div>

      <button type="submit">Create Warehouse</button>
    </form>
  </div>
</div>

<!-- 
# UPDATE WAREHOUSE
  -->
<!-- Modal Window for updating warehouse -->
<div id="warehouseUpdateModal" class="modal modal-narrow warehouse-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Update Warehouse</h3>
      <span class="close" onclick="closeWarehouseUpdateModal()">×</span>
    </div>
    <form class="warehouse-form" action="warehouses/update/form-submit" method="post">
      <input type="text" name="id" id="warehouseId" hidden />
      <div>
        <label>Name</label>
        <input type="text" name="name" id="warehouseName" placeholder="Enter warehouse name" required />
      </div>
      <div>
        <label>Location</label>
        <input type="text" name="location" id="warehouseLocation" placeholder="Enter warehouse location" required />
      </div>

      <button type="submit">Update Warehouse</button>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean(); # Get the buffered content and clean the buffer
require_once __DIR__ . "/../layouts/layout.php";


?>
