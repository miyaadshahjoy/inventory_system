<?php
$returns = $data['returns'] ?? [];
$products = $data['products'] ?? [];
$warehouses = $data['warehouses'] ?? [];
ob_start();
?>

<!-- 
# SHOWING RETURNS LIST
-->
<div class="container">
    <div class="container-header">
        <h2>Returns</h2>
        <button onclick="openModal()">+ Process Return</button>
    </div>

    <?php if (empty($returns)): ?>
        <p>No returns found.</p>
    <?php endif; ?>
    <?php if (!empty($returns)): ?>

        <!-- 
         Product | Warehouse | Quantity | Reason | Created by | Date
        -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Created by</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($returns as $return): ?>
                    <tr>
                        <td><?= $return['product_name'] ?> (sku-<?= $return['product_sku'] ?>)</td>
                        <td><?= $return['warehouse_name'] ?></td>
                        <td><?= $return['quantity'] ?></td>
                        <td><?= $return['reason'] ?></td>
                        <td><?= $return['created_by'] ?></td>
                        <td><?= $return['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<!-- 
# Create return movement modal
-->
<div id="modal" class="modal return-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Process Return</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>

        <form class="return-form" action="returns/form-submit" method="post">

            <div class="form-group">
                <div>
                    <label for="product_id">Product</label>
                    <select name="product_id" required>
                        <option value="">Select product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>">
                                <?= $product['name'] ?> (sku-
                                <?= $product['sku'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="warehouse_id">Warehouse</label>
                    <select name="warehouse_id" required>
                        <option value="">Select warehouse</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse['id'] ?>">
                                <?= $warehouse['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div>
                    <label for="quantity">Quantity</label>
                    <input type="number" name="quantity" placeholder="Enter quantity" required />
                </div>
                <div>
                    <label for="reason">Reason</label>
                    <textarea name="reason"></textarea>
                </div>
            </div>

            <button type="submit">Process Return</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>