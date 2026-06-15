<?php

ob_start();
$warehouses = $data["warehouses"] ?? [];

$purchase_order = $data["purchase_order"] ?? null;
$products_ordered = $data["products_ordered"] ?? 0;
$total_quantity = $data["total_quantity"] ?? 0;
$received_quantity = $data["received_quantity"] ?? 0;
$total_cost = $data["total_cost"] ?? 0;

$purchase_order_items = $data["purchase_order_items"] ?? [];
?>

<div class="container">
    <div class="container-header">
        <h2>Purchase Order Details</h2>
        <!-- Back button -->
        <button onclick="window.history.back()">Back</button>
    </div>
    <div class="purchase-order-header">

        <div class="purchase-order-header-contents">
            <!-- 
            # PO Number | Status | Supplier | Expected Delivery | Created By | Created At | Created At
            -->
            <div><strong>PO Number:</strong>
                <?= htmlspecialchars($purchase_order["po_number"]) ?>
            </div>
            <div><strong>Status:</strong>
                <?= htmlspecialchars($purchase_order["status"]) ?>
            </div>
            <div><strong>Supplier:</strong>
                <?= htmlspecialchars($purchase_order["supplier"]) ?>
            </div>
            <div><strong>Expected Delivery:</strong>
                <?= htmlspecialchars($purchase_order["expected_delivery"]) ?>
            </div>
            <div><strong>Created By:</strong>
                <?= htmlspecialchars($purchase_order["created_by"]) ?>
            </div>
            <div><strong>Created At:</strong>
                <?= htmlspecialchars($purchase_order["created_at"]) ?>
            </div>
            <div><strong>Updated At:</strong>
                <?= htmlspecialchars($purchase_order["updated_at"]) ?>
            </div>
        </div>
        <div class="purchase-order-notes">
            <h3>Notes</h3>
            <p>
                <?= htmlspecialchars($purchase_order["notes"]) ?:
                    "No notes available." ?>
            </p>
        </div>
    </div>



    <div class="purchase-order-statistics">

        <div class="purchase-order-card">
            <h3>Products Ordered</h3>
            <p><?= $products_ordered ?></p>
        </div>
        <div class="purchase-order-card">
            <h3>Total Quantity</h3>
            <p><?= $total_quantity ?></p>
        </div>
        <div class="purchase-order-card">
            <h3>Received Quantity</h3>
            <p><?= $received_quantity ?></p>
        </div>
        <div class="purchase-order-card">
            <h3>Total Cost</h3>
            <p>
                <?= $total_cost ?>
            </p>
        </div>

    </div>

    <div class="receiveable-items">
        <h3>Purchase Order Items</h3>
        <div class="table-wrapper">
            <table>
                <!-- 
                    # Product | Ordered | Received | Remaining | Unit Price | Line Total            
                -->
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Ordered</th>
                        <th>Received</th>
                        <th>Remaining</th>
                        <th>Unit Price</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchase_order_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item["product"]) ?></td>
                            <td>
                                <?= htmlspecialchars($item["ordered"]) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($item["received"]) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($item["ordered"]) -
                                    htmlspecialchars($item["received"]) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($item["unit_price"]) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($item["line_total"]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

            </table>
        </div>
    </div>
    <!-- TODO: Receiving History -->
    <!-- TODO: Timeline -->
    <div class="purchase-order-actions">
        <!-- # approve | receive | cancel | print PO -->
        <?php if (
            $_SESSION["user"]["role"] === "ADMIN" &&
            $purchase_order["status"] === "PENDING"
        ): ?>
            <a href="approve?id=<?= $purchase_order["id"] ?>">

                <button>Approve</button>
            </a>

        <?php endif; ?>
        <?php if (
            $_SESSION["user"]["role"] === "ADMIN" &&
            ($purchase_order["status"] === "PENDING" ||
                $purchase_order["status"] === "APPROVED")
        ): ?>
            <a href="cancel?id=<?= $purchase_order["id"] ?>">
                <button>Cancel</button>

            </a>

        <?php endif; ?>
        <?php if (
            $purchase_order["status"] === "APPROVED" ||
            $purchase_order["status"] === "PARTIALLY_RECEIVED"
        ): ?>

            <button onclick="openModal()">Receive</button>

        <?php endif; ?>


        <!-- <button>Print PO</button> -->
    </div>
</div>

<!-- 
# Receive purchase items
-->
<div id="modal" class="modal receiveable-order-modal modal-wide">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Receive Items</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>

        <form action="receive-items/form-submit" method="post" class="form receiveable-form">

            <div class="table-wrapper">
                <!-- 
                    # Product | Ordered | Received | Receive Now
                 -->
                <table class="receivedable-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Ordered</th>
                            <th>Received</th>
                            <th>Warehouse</th>
                            <th>Receive Now</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--  -->
                        <input type="text" name="purchase_order_id"
                            value="<?= htmlspecialchars(
                                $item["purchase_order_id"],
                            ) ?>" hidden>
                        <?php foreach ($purchase_order_items as $item): ?>
                            
                            <tr class="<?= $item["received"] ===
                            $item["ordered"]
                                ? "hide"
                                : "" ?>">
                                <input type="text" name="items[<?= $item[
                                    "id"
                                ] ?>][product_id]"
                                    value="<?= htmlspecialchars(
                                        $item["product_id"],
                                    ) ?>" hidden>
                                <!-- Product Name -->
                                <td>
                                    <input type="text" name="" value="<?= htmlspecialchars(
                                        $item["product"],
                                    ) ?>" readonly>
                                </td>
                                <!-- Ordered Quantity -->
                                <td>
                                    <input type="number" name="items[<?= $item[
                                        "id"
                                    ] ?>][order_quantity]"
                                        value="<?= htmlspecialchars(
                                            $item["ordered"],
                                        ) ?>" readonly>
                                </td>
                                <!-- Received Quantity -->
                                <td>
                                    <input type="number" name="items[<?= $item[
                                        "id"
                                    ] ?>][received_quantity]"
                                        value="<?= htmlspecialchars(
                                            $item["received"],
                                        ) ?>" readonly>
                                </td>
                                <!-- Warehouse -->
                                    <td>
                                    <select name="items[<?= $item[
                                        "id"
                                    ] ?>][warehouse_id]" >
                                        <option value="">Select warehouse</option>
                                        <?php foreach (
                                            $warehouses
                                            as $warehouse
                                        ): ?>
                                            <option value="<?= $warehouse[
                                                "id"
                                            ] ?>">
                                                <?= $warehouse["name"] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    </td>
                                <!-- Receive Now -->
                                <td>
                                    <input type="number" name="items[<?= $item[
                                        "id"
                                    ] ?>][receive_now]" value="0">
                                </td>
                                <!-- <td><input type="number" name="receive_now"
                                    value="<?= htmlspecialchars(
                                        $item["ordered"],
                                    ) - htmlspecialchars($item["received"]) ?>">
                            </td> -->
                            </tr>
                            
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <button type="submit">Receive</button>
        </form>

    </div>
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layouts/layout.php";


?>
