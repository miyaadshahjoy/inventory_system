<?php
$current_stock_details = $data["current_stock_details"] ?? [];
$stock_movements_summary = $data["stock_movements_summary"] ?? [];

ob_start();
?>

<div class="container">
    <div class="container-header">
        <h2>Stock Report</h2>
    </div>

    <div class="stock-report-tabs-container">
        <div class="stock-details-tab active" onclick="showCurrentStockDetails(this)">Current Stock Details</div>
        <div class="stock-movements-tab" onclick="showStockMovementsSummary()">Stock Movements Summary</div>
    </div>

    <!-- # TABLE: Current Stock Details -->
    <div class="stock-details-tab-content">

        <div class="table-wrapper">
        <h3>Current Stock Details</h3>
        <!-- Product | SKU | Category | Warehouse | Current Stock | Unit Cost | Stock Value | Reorder Level | Status | Last Movement Date -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Warehouse</th>
                    <th>Current Stock</th>
                    <th>Unit Cost</th>
                    <th>Stock Value</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Last Movement Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($current_stock_details as $details): ?>
                    <tr>
                        <td><?= $details["product"] ?></td>
                        <td><?= $details["sku"] ?></td>
                        <td><?= $details["category"] ?></td>
                        <td><?= $details["warehouse"] ?></td>
                        <td><?= $details["current_stock"] ?></td>
                        <td><?= $details["unit_cost"] ?></td>
                        <td><?= $details["stock_value"] ?></td>
                        <td><?= $details["reorder_level"] ?></td>
                        <td><?= $details["status"] ?></td>
                        <td><?php
                        $date = new DateTime($details["last_movement_date"]);
                        echo $date->format("Y-m-d");
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>


    <!-- # TABLE: Stock Movement Summary -->
    <div class="stock-movements-tab-content">

        <div class="table-wrapper">
        <h3>Stock Movement Summary</h3>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Warehouse</th>
                    <th>Movement Type</th>
                    <th>Quantity</th>
                    <th>Created By</th>
                    <th>Reference</th>
                    <!-- TODO: Add Received and Sold column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stock_movements_summary as $summary): ?>
                    <tr>
                        <td>
                            <?php
                            $date = new DateTime($summary["date"]);
                            echo $date->format("Y-m-d");
                            ?>
                        </td>
                        <td><?= $summary["product"] ?></td>
                        <td><?= $summary["sku"] ?></td>
                        <td><?= $summary["warehouse"] ?></td>
                        <td><?= $summary["movement_type"] ?></td>
                        <td><?= $summary["quantity"] ?></td>
                        <td><?= $summary["created_by"] ?></td>
                        <td><?= $summary["reference"] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layouts/layout.php";


?>
