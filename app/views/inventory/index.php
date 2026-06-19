<?php
$overview_data = $data["inventory_overview_data"] ?? [];
$total_skus = $data["total_skus"] ?? 0;
$total_stock = $data["total_stocks"] ?? 0;
$total_stock_value = $data["total_stock_value"] ?? 0;
$total_low_stocks = $data["total_low_stocks"] ?? 0;
$total_out_stocks = $data["total_out_stocks"] ?? 0;
$total_movements_today = $data["total_movements_today"] ?? 0;

$categories = $data["categories"] ?? [];
$warehouses = $data["warehouses"] ?? [];

$total_inventory_overview = $data["total_inventory_overview"] ?? 0;
$limit = $data["limit"] ?? 5;
$page = $data["page"] ?? 1;
$total_pages = ceil($total_inventory_overview / $limit);

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
        <h2>Iventory Overview</h2>
    </div>

    <div class="overview-cards-container">
        <div class="overview-card">
            <div class="overview-card-heading">Total SKUs</div>
            <div class="value"><?= $total_skus ?></div>
        </div>
        <div class="overview-card">
            <div class="overview-card-heading">Total Stocks</div>
            <div class="value"><?= $total_stock ?></div>
        </div>
        <div class="overview-card">
            <div class="overview-card-heading">Total Stock Value</div>
            <div class="value"><?= $total_stock_value ?></div>
        </div>
        <div class="overview-card">
            <div class="overview-card-heading">Total Low Stocks</div>
            <div class="value"><?= $total_low_stocks ?></div>
        </div>
        <div class="overview-card">
            <div class="overview-card-heading">Total Out Stocks</div>
            <div class="value"><?= $total_out_stocks ?></div>
        </div>
        <div class="overview-card">
            <div class="overview-card-heading">Total Movements Today</div>
            <div class="value">
                <?= $total_movements_today ?>
            </div>
        </div>
    </div>


    
    <!-- Overview Filters -->
    <form action="/inventory-overview" method="get" class="form overview-filters-container" >
        <!-- Product Search -->
        <div class="product-search">
    
            <input type="text" name="product_search" id="product-search" placeholder="Search products by name or SKU">
    
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

            <!-- Category filter -->
            <div>
                <h4>Filter by category</h4>
                <select name="category_id" id="category">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category["id"] ?>">
                            <?= htmlspecialchars($category["name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
            <!-- Stock Status filter -->
            <div>
                <h4>Filter by stock status</h4>
                <select name="stock_status" id="stock-status">
                    <option value="">Select stock status</option>
                    <option value="LOW">Low</option>
                    <option value="OUT">Out</option>
                    <option value="OK">In Stock</option>
                </select>
            </div>

            <!-- Sort by filter -->
            <div>
                <h4>Sort by</h4>
                <div class="form-group">
                    <div>

                        <select name="sort_by">
                            <option value="">Select sort option</option>
                            <option value="product_name">Product Name</option>
                            <option value="stock">Stock</option>
                            <option  value="last_movement_date">Last Movement Date</option>
                        </select>
                    </div>
                    <div>
                        <select name="sort_order">
                            <option value="ASC">Ascending</option>
                            <option value="DESC">Descending</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit">Apply Filter</button>
        </div>
    </form>

    <!-- Active filters -->
    <div class="active-filters">
        <!-- Search -->
        <?php if (
            isset($_GET["product_search"]) &&
            !empty($_GET["product_search"])
        ): ?>
            <div class="filters-tag">
                <span>
                    Search: <?= htmlspecialchars($_GET["product_search"]) ?>
                    <a href="/inventory-overview?<?= ProductService::createUrlWithout(
                        ["product_search"],
                    ) ?>">❌</a>
                </span>
            </div>
        <?php endif; ?>

        <!-- Category -->
        <?php if (
            isset($_GET["category_id"]) &&
            !empty($_GET["category_id"])
        ): ?>
            <div class="filters-tag">
                <span>
                    Category:
                    <?php
                    $filteredCategories = array_filter(
                        $categories,
                        fn($category) => $category["id"] ===
                            (int) htmlspecialchars($_GET["category_id"]),
                    );
                    $categoryName = !empty($filteredCategories)
                        ? array_values($filteredCategories)[0]["name"]
                        : "";
                    echo $categoryName;
                    ?>
                    <a href="/inventory-overview?<?= ProductService::createUrlWithout(
                        ["category_id"],
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
                    <a href="/inventory-overview?<?= ProductService::createUrlWithout(
                        ["warehouse_id"],
                    ) ?>">❌</a>
                </span>
            </div>
        <?php endif; ?>

        <!-- Stock Status -->
        <?php if (
            isset($_GET["stock_status"]) &&
            !empty($_GET["stock_status"])
        ): ?>
            <div class="filters-tag">
                <span>
                    Stock Status: <?= htmlspecialchars($_GET["stock_status"]) ?>
                    <a href="/inventory-overview?<?= ProductService::createUrlWithout(
                        ["stock_status"],
                    ) ?>">❌</a>
                </span>
            </div>
        <?php endif; ?>

        <!-- Sort by -->
        <?php if (isset($_GET["sort_by"]) && !empty($_GET["sort_by"])): ?>
            <div class="filters-tag">
                <span>
                    Sort By: <?= htmlspecialchars($_GET["sort_by"]) ?>
                    <a href="/inventory-overview?<?= ProductService::createUrlWithout(
                        ["sort_by"],
                    ) ?>">❌</a>
                </span>
            </div>
        <?php endif; ?>

    </div>

    <!-- Total fitered records -->
    <?php if (
        in_array("product_search", array_keys($_GET)) ||
        in_array("category_id", array_keys($_GET)) ||
        in_array("warehouse_id", array_keys($_GET)) ||
        in_array("stock_status", array_keys($_GET)) ||
        in_array("sort_by", array_keys($_GET))
    ): ?>

        <div class="total-records">
            <span>
                Total filtered records: <?= $total_inventory_overview ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Reset filters -->
    <?php if (
        in_array("product_search", array_keys($_GET)) ||
        in_array("category_id", array_keys($_GET)) ||
        in_array("warehouse_id", array_keys($_GET)) ||
        in_array("stock_status", array_keys($_GET)) ||
        in_array("sort_by", array_keys($_GET))
    ): ?>

        <a href="/inventory-overview" class="reset-filters">
            <button>
                Reset Filters
            </button>
        </a>
    <?php endif; ?>

    <!-- 
        product_name 
        sku
        product_category 
        warehouse 
        stock 
        status 
        reorder_level 
        last_movement_date  
    -->
    <?php if (empty($overview_data)): ?>
        <div>
            No data available
        </div>
    <?php endif; ?>
    <?php if (!empty($overview_data)): ?>

        <div class="export-data">
            <!-- Export CSV -->
            <?php
            if (isset($_GET["url"])) {
                unset($_GET["url"]);
            }
            if (isset($_GET["page"])) {
                unset($_GET["page"]);
            }
            ?>
            <a href="/inventory-overview/export?<?= http_build_query($_GET) ?>">
                <button>
                    Export CSV
                </button>
            </a>
        </div>
        <div class="table-wrapper overview-table">
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
                                <?= $data["product_name"] ?>
                            </td>
                            <td>
                                <?= $data["sku"] ?>
                            </td>
                            <td>
                                <?= $data["product_category"] ?>
                            </td>
                            <td>
                                <?= $data["warehouse"] ?>
                            </td>
                            <td>
                                <?= $data["stock"] ?>
                            </td>
                            <td class="status <?= $data["stock_status"] ?>">
                                <?= $data["stock_status"] ?>
                            </td>
                            <td>
                                <?= $data["reorder_level"] ?>
                            </td>
                            <td>
                                <?php if (!empty($data["last_movement_date"])) {
                                    $date = new DateTime(
                                        $data["last_movement_date"],
                                    );
                                    echo $date->format("Y-m-d");
                                } else {
                                    echo "";
                                } ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
        <!-- Implementing pagination buttons -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="/inventory-overview?<?= $prev_url ?>" class="button-pagination">
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
                <a href="/inventory-overview?<?= $page_url ?>" class="button-pagination <?= $page ===
$i
    ? "active"
    : "" ?>">
                    <button>
                        <?= $i ?>
                    </button>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="/inventory-overview?<?= $next_url ?>" class="button-pagination">
                    <button>
                        Next
                    </button>
                </a>
            <?php endif; ?>

        </div>

    <?php endif; ?>
</div>




<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layouts/layout.php";


?>
