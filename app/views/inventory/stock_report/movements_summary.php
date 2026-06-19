<?php
$stock_movements_summary = $data["stock_movements_summary"] ?? [];
$warehouses = $data["warehouses"] ?? [];

$total_movements_summary = $data["total_movements_summary"] ?? null;
$limit = $data["limit"] ?? 10;
$page = $data["page"] ?? 1;

$total_pages = ceil($total_movements_summary / $limit);

$current_page = $page ?? 1;
$query_params = $_GET;

# Previous page
$query_params["page"] = $current_page > 1 ? $current_page - 1 : $current_page;

$prev_url = http_build_query($query_params);

# Next page
$query_params["page"] =
    $current_page < $total_pages ? $current_page + 1 : $current_page;

$next_url = http_build_query($query_params);

ob_start();
?>

<div class="container">
    <div class="container-header">
        <h2>Movements Summary</h2>
    </div>

    
    <!-- # Stock Movement Summary -->
    <div class="movements-summary-content">


        
        <!-- Stock Movements Summary: Filters -->
        <!-- 
            # Filters 
        - product_search
        - start_date
        - end_date
        - warehouse
        - movement_type
    -->
        <form action="/stock-report/movements-summary" method="get" class="movements-summary-filters-container">
            <!-- Stock Movements Summary: Product Search -->
            <div action="/stock-report/movements-summary" method="get" class="product-search">
                <input type="text" name="product_search" id="product-search" placeholder="Search products by name or SKU" >
        
                <button type="submit">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6"
                        height="24px"
                        width="24px"
                        >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                        />
                    </svg>
                </button>
        
            </div>
            <div class="filter-controllers">

                <!-- Date range filter -->
                <div class="date-filter" >
                    <h4>Filter by date</h4>
                    <div class="date-range">
                        <input type="date" name="start_date" id="start-date">
                        <input type="date" name="end_date" id="end-date">
                    </div>
                </div>
                <!-- Warehouse filter -->
                <div>
                    <h4>Filter by warehouse</h4>
                    <select name="warehouse_id" id="warehouse">
                        <option value="">Select warehouse</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse["id"] ?>">
                                <?= htmlspecialchars($warehouse["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Movement Type filter -->
                <div>
                    <h4>Filter by movement type</h4>
                    <select name="movement_type" id="movement-type">
                        <option value="">Select movement type</option>
                        <option value="STOCK_IN">Stock In</option>
                        <option value="STOCK_OUT">Stock Out</option>
                        <option value="TRANSFER_IN">Transfer In</option>
                        <option value="TRANSFER_OUT">Transfer Out</option>
                        <option value="ADJUSTMENT_IN">Adjustment In</option>
                        <option value="ADJUSTMENT_OUT">Adjustment Out</option>
                        <option value="RETURN">Return</option>
                        <option value="DAMAGE">Damage</option>
                        <option value="EXPIRE">Expire</option>
                        <option value="PURCHASE">Purchase</option>
                        
                    </select>
                </div>

                <button type="submit">Apply Filter</button>
            </div>
        </form>

        <!-- Active Filters -->
        <div class="active-filters">
            <!-- Search -->
            <?php if (
                isset($_GET["product_search"]) &&
                !empty($_GET["product_search"])
            ): ?>
                <div class="filters-tag">
                    <span>
                        Search: <?= htmlspecialchars($_GET["product_search"]) ?>
                        <a href="/stock-report/movements-summary?<?= ProductService::createUrlWithout(
                            ["product_search"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Start Date -->
            <?php if (
                isset($_GET["start_date"]) &&
                !empty($_GET["start_date"])
            ): ?>
                <div class="filters-tag">
                    <span>
                        Start Date: <?= htmlspecialchars($_GET["start_date"]) ?>
                        <a href="/stock-report/movements-summary?<?= ProductService::createUrlWithout(
                            ["start_date"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>

            <!-- End Date -->
            <?php if (isset($_GET["end_date"]) && !empty($_GET["end_date"])): ?>
                <div class="filters-tag">
                    <span>
                        End Date: <?= htmlspecialchars($_GET["end_date"]) ?>
                        <a href="/stock-report/movements-summary?<?= ProductService::createUrlWithout(
                            ["end_date"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Warehouse -->
            <?php if (
                isset($_GET["warehouse_id"]) &&
                !empty($_GET["warehouse_id"])
            ): ?>
                <div class="filters-tag">
                    <span>
                        Warehouse:
                        <?php
                        $filteredWarehouses = array_filter(
                            $warehouses,
                            fn($warehouse) => $warehouse["id"] ===
                                (int) htmlspecialchars($_GET["warehouse_id"]),
                        );
                        $warehouseName = !empty($filteredWarehouses)
                            ? array_values($filteredWarehouses)[0]["name"]
                            : "";
                        echo $warehouseName;
                        ?>
                        <a href="/stock-report/movements-summary?<?= ProductService::createUrlWithout(
                            ["warehouse_id"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Movement Type -->
            <?php if (
                isset($_GET["movement_type"]) &&
                !empty($_GET["movement_type"])
            ): ?>
                <div class="filters-tag">
                    <span>
                        Movement Type: <?= htmlspecialchars(
                            $_GET["movement_type"],
                        ) ?>
                        <a href="/stock-report/movements-summary?<?= ProductService::createUrlWithout(
                            ["movement_type"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>
            
        </div>

        <!-- Total filtered records -->
        <?php if (
            in_array("product_search", array_keys($_GET)) ||
            in_array("start_date", array_keys($_GET)) ||
            in_array("end_date", array_keys($_GET)) ||
            in_array("warehouse_id", array_keys($_GET)) ||
            in_array("movement_type", array_keys($_GET))
        ): ?>

            <div class="total-records">
                <span>
                    Total filtered records: <?= $total_movements_summary ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Reset Filters -->
        <?php if (
            in_array("product_search", array_keys($_GET)) ||
            in_array("start_date", array_keys($_GET)) ||
            in_array("end_date", array_keys($_GET)) ||
            in_array("warehouse_id", array_keys($_GET)) ||
            in_array("movement_type", array_keys($_GET))
        ): ?>
            <a href="/stock-report/movements-summary" class="reset-filters">
                <button >
                    Reset Filters
                </button>
            </a>
        <?php endif; ?>

        <!-- TODO: Export CSV -->


        <!-- Stock Movement Summary List -->
        <?php if (empty($stock_movements_summary)): ?>
            <div>No stock movement summary found.</div>
        <?php endif; ?>            
        <?php if (!empty($stock_movements_summary)): ?>
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
        <?php endif; ?>

        <!-- # PAGINATION -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="/stock-report/movements-summary?<?= $prev_url ?>" class="button-pagination">
                    <button>
                        Prev
                    </button>
                </a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                <?php
                $query_params["page"] = $i;
                $page_url = http_build_query($query_params);
                ?>
                <a href="/stock-report/movements-summary?<?= $page_url ?>" class="button-pagination <?= $page ===
$i
    ? "active"
    : "" ?>">
                    <button>
                        <?= $i ?>
                    </button>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="/stock-report/movements-summary?<?= $next_url ?>" class="button-pagination">
                    <button>
                        Next
                    </button>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . "/../../layouts/layout.php";


?>
