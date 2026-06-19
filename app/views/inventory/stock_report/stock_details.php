<?php
$current_stock_details = $data["current_stock_details"] ?? [];
$categories = $data["categories"] ?? [];
$warehouses = $data["warehouses"] ?? [];

$total_stock_details = $data["total_stock_details"] ?? null;
$limit = $data["limit"] ?? 10;
$page = $data["page"] ?? 1;

$total_pages = ceil($total_stock_details / $limit);

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
        <h2>Stock Details</h2>
    </div>

    
    <!-- # Current Stock Details -->
    <div class="stock-details-content">

        <!-- Current Stock Details: Filters -->
        <form action="/stock-report/stock-details" method="get" class="stock-details-filters-container">
            <!-- Current Stock Details: Product Search -->
            <div class="product-search">
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
                <!-- 
                    <div>
                        
                        <h4>Filter by stock status</h4>
                        <select name="stock_status" id="stock-status">
                            <option value="">Select stock status</option>
                            <option value="OUT">Out of Stock</option>
                            <option value="LOW">Low Stock</option>
                            <option value="OK">In Stock</option>
                        </select>
                    </div>
                -->
                <!-- Sort By filter  -->
                <!-- # product | received | sold | returned | damaged | expired | current_stock -->
                <div>
                    <h4>Sort by</h4>
                    <div class="form-group">

                        <div>
                            
                            <select name="sort_by">
                                <option value="">Select sort option</option>
                                <option value="product">Product</option>
                                <option value="received">Received</option>
                                <option value="sold">Sold</option>
                                <option value="returned">Returned</option>
                                <option value="damaged">Damaged</option>
                                <option value="expired">Expired</option>
                                <option value="current_stock">Current Stock</option>
                            </select>
                        </div>
                        <select name="sort_order">
                            <option value="ASC">Ascending</option>
                            <option value="DESC">Descending</option>
                        </select>
                    </div>
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
                        <a href="/stock-report/stock-details?<?= ProductService::createUrlWithout(
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
                        <a href="/stock-report/stock-details?<?= ProductService::createUrlWithout(
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
                        <a href="/stock-report/stock-details?<?= ProductService::createUrlWithout(
                            ["warehouse_id"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Stock Status -->
            

            <!-- Sort By -->
            <?php if (isset($_GET["sort_by"]) && !empty($_GET["sort_by"])): ?>
                <div class="filters-tag">
                    <span>
                        Sort By: <?= htmlspecialchars($_GET["sort_by"]) ?>
                        <a href="/stock-report/stock-details?<?= ProductService::createUrlWithout(
                            ["sort_by"],
                        ) ?>">❌</a>
                    </span>
                </div>
            <?php endif; ?>
            
        </div>

        <!-- Total filtered records -->
        <?php if (
            in_array("product_search", array_keys($_GET)) ||
            in_array("category_id", array_keys($_GET)) ||
            in_array("warehouse_id", array_keys($_GET))
            // in_array("stock_status", array_keys($_GET))
        ): ?>

            <div class="total-records">
                <span>
                    Total filtered records: <?= $total_stock_details ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Reset filters -->
        <?php if (
            in_array("product_search", array_keys($_GET)) ||
            in_array("category_id", array_keys($_GET)) ||
            in_array("warehouse_id", array_keys($_GET))
            // in_array("stock_status", array_keys($_GET))
        ): ?>

            <a href="/stock-report/stock-details" class="reset-filters">
                <button>
                    Reset Filters
                </button>
            </a>
        <?php endif; ?>

        <!-- TODO: Export CSV -->



        <!-- Current Stock Details List -->
        <?php if (empty($current_stock_details)): ?>
            <div>No current stock details found.</div>
        <?php endif; ?>
        <?php if (!empty($current_stock_details)): ?>
            <div class="table-wrapper">
            <h3>Current Stock Details</h3>
            <!-- Product | SKU | Category | Warehouse | Opening Stock | Received | Sold | Transfered In | Transfered Out | Adjusted In | Adjusted Out | Returned | Expired | Damaged | Current Stock | Unit Cost | Stock Value -->
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Opening Stock</th>
                        <th>Received</th>
                        <th>Sold</th>
                        <th>Transfered In</th>
                        <th>Transfered Out</th>
                        <th>Adjusted In</th>
                        <th>Adjusted Out</th>
                        <th>Returned</th>
                        <th>Expired</th>
                        <th>Damaged</th>
                        <th>Current Stock</th>
                        <th>Unit Cost</th>
                        <th>Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_stock_details as $details): ?>
                        <tr>
                            <td><?= $details["product"] ?></td>
                            <td><?= $details["sku"] ?></td>
                            <td><?= $details["category"] ?></td>
                            <td><?= $details["warehouse"] ?></td>
                            <td><?= $details["opening_stock"] ?></td>
                            <td><?= $details["received"] ?></td>
                            <td><?= $details["sold"] ?></td>
                            <td><?= $details["transfered_in"] ?></td>
                            <td><?= $details["transfered_out"] ?></td>
                            <td><?= $details["adjusted_in"] ?></td>
                            <td><?= $details["adjusted_out"] ?></td>
                            <td><?= $details["returned"] ?></td>
                            <td><?= $details["expired"] ?></td>
                            <td><?= $details["damaged"] ?></td>

                            <td><?= $details["current_stock"] ?></td>
                            <td><?= $details["unit_cost"] ?></td>
                            <td><?= $details["stock_value"] ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>

        <!-- # PAGINATION -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="/stock-report/stock-details?<?= $prev_url ?>" class="button-pagination">
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
                <a href="/stock-report/stock-details?<?= $page_url ?>" class="button-pagination <?= $page ===
$i
    ? "active"
    : "" ?>">
                    <button>
                        <?= $i ?>
                    </button>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="/stock-report/stock-details?<?= $next_url ?>" class="button-pagination">
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
