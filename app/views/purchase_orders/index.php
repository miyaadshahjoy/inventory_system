<?php
# /purchase-orders/index.php
$warehouses = $data['warehouses'] ?? [];
$products = $data['products'] ?? [];
$suppliers = $data['suppliers'] ?? [];
$purchase_orders = $data['purchase_orders'] ?? [];



ob_start();
?>

<div class="container">

    <!-- 
    # Container Header 
-->
    <div class="container-header">
        <h2>Purchase Orders</h2>
        <button onclick="openModal()">+ Create new purchase order</button>
    </div>

    <!-- 
        # Show purchase orders list in a table 
    -->

    <!--
     # PO Number | Supplier | Warehouse | Total Items | Total Quantity | Total Cost | Status | Expected Delivery | Created By | Created At | Actions
    -->
    <?php if (empty($purchase_orders)): ?>
        <div>No purchase orders found.</div>
    <?php endif; ?>
    <?php if (!empty($purchase_orders)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Warehouse</th>
                        <th>Total Items</th>
                        <th>Total Quantity</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                        <th>Expected Delivery</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchase_orders as $order): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($order['po_number']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['supplier']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['warehouse']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['total_items']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['total_quantity']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['total_cost']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['status']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['expected_delivery']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['created_by']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['created_at']) ?>
                            </td>
                            <td><a href="/purchase-orders/details?id=<?= $order['id'] ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<!-- 
    # Create purchase order modal
    -->
<div class="modal purchase-modal" id="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create Purchase Order</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>

        <!-- # Purchase order form -->
        <form class="form purchase-form" action="/purchase-orders/form-submit" method="post">

            <!-- # Purchase order header section -->
            <div class="purchase-order-header">

                <div class="form-group">
                    <!-- Supplier selection: Dropdown, required -->
                    <div>
                        <label for="supplier">Supplier</label>
                        <select name="supplier_id" id="supplier" required>
                            <option value="">Select a supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>

                                <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['supplier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Warehouse selection: Dropdown, required -->
                    <div>
                        <label for="warehouse">Warehouse</label>
                        <select name="warehouse_id" id="warehouse" required>
                            <option value="">Select a warehouse</option>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?= $warehouse['id'] ?>">
                                    <?= htmlspecialchars($warehouse['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Expected delivery date: required -->
                <div>
                    <label for="expected_delivery_date">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" required>
                </div>

                <!-- Notes: Optional -->
                <div>
                    <label for="notes">Notes</label>
                    <textarea name="notes" rows="5" id="notes"></textarea>
                </div>

            </div>

            <div class="purchase-order-items">

                <!-- Product selection: Dropdown, required -->
                <div>
                    <label for="product">Product</label>
                    <!-- name="items[0][product_id]" -->
                    <select name="product" id="product">
                        <option value="">Select a product</option>
                        <?php foreach ($products as $product): ?>
                            <option data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <!-- Quantity: required -->
                    <div>
                        <label for="quantity">Quantity</label>
                        <!-- name="items[0][quantity]" -->
                        <input type="number" name="quantity" id="quantity">
                    </div>

                    <!-- Unit price: required -->
                    <div>
                        <label for="unit_price">Unit Price</label>
                        <!-- name="items[0][unit_price]" -->
                        <input type="number" name="unit_price" id="unit_price" step="0.5">
                    </div>
                </div>

                <button type="button" onclick="addItem()">+ Add Product</button>
            </div>

            <div class="purchase-order-products">

            </div>


            <button type="submit">Create Purchase Order</button>

        </form>

    </div>
</div>



<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>