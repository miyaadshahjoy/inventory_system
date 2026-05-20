<?php
$overview_data = $data['inventory_overview_data'] ?? [];
$total_skus = $data['total_skus'] ?? 0;
$total_stock = $data['total_stocks'] ?? 0;
$total_stock_value = $data['total_stock_value'] ?? 0;
$total_low_stocks = $data['total_low_stocks'] ?? 0;
$total_out_stocks = $data['total_out_stocks'] ?? 0;
$total_movements_today = $data['total_movements_today'] ?? 0;
ob_start();

?>

<div class="container">
    <div class="container-header">
        <h2>Iventory Overview</h2>
    </div>

    <div class="overview-cards">
        <div class="card">
            <div class="card-heading">Total SKUs</div>
            <div class="value"><?= $total_skus ?></div>
        </div>
        <div class="card">
            <div class="card-heading">Total Stocks</div>
            <div class="value"><?= $total_stock ?></div>
        </div>
        <div class="card">
            <div class="card-heading">Total Stock Value</div>
            <div class="value"><?= $total_stock_value ?></div>
        </div>
        <div class="card">
            <div class="card-heading">Total Low Stocks</div>
            <div class="value"><?= $total_low_stocks ?></div>
        </div>
        <div class="card">
            <div class="card-heading">Total Out Stocks</div>
            <div class="value"><?= $total_out_stocks ?></div>
        </div>
        <div class="card">
            <div class="card-heading">Total Movements Today</div>
            <div class="value">
                <?= $total_movements_today ?>
            </div>
        </div>
    </div>

    <!-- product_name 
  sku
  product_category 
  warehouse 
  stock 
  status 
  reorder_level 
  last_movement_date  -->
    <?php if (empty($overview_data)): ?>
        <div>
            No data available
        </div>
    <?php endif; ?>
    <?php if (!empty($overview_data)): ?>
        <div class="overview-table">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Reorder Level</th>
                        <th>Last Movement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overview_data as $data): ?>
                        <tr>
                            <td>
                                <?= $data['product_name'] ?>
                            </td>
                            <td>
                                <?= $data['sku'] ?>
                            </td>
                            <td>
                                <?= $data['product_category'] ?>
                            </td>
                            <td>
                                <?= $data['warehouse'] ?>
                            </td>
                            <td>
                                <?= $data['stock'] ?>
                            </td>
                            <td class="status <?= $data['status'] ?>">
                                <?= $data['status'] ?>
                            </td>
                            <td>
                                <?= $data['reorder_level'] ?>
                            </td>
                            <td>
                                <?= $data['last_movement_date'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>




<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>