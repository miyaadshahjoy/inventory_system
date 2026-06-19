<?php
const OVERVIEW_PER_PAGE = 10;
class InventoryController
{
    public function index()
    {
        # Get page number
        $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
        $limit = OVERVIEW_PER_PAGE;

        $filter_data = InventoryService::getInventoryOverviewFilterData();

        [
            "results" => $overview_data,
            "length" => $total_overview_data,
        ] = InventoryService::getInventoryOverviewData($filter_data);

        $data = [
            "total_skus" => InventoryService::getTotalSKUs(),
            "total_stocks" => InventoryService::getTotalStock(),
            "total_stock_value" => InventoryService::getTotalStockValue(),
            "total_low_stocks" => InventoryService::getTotalLowStocks(),
            "total_out_stocks" => InventoryService::getTotalOutStocks(),
            "total_movements_today" => InventoryService::getTotalMovementToday(),
            "inventory_overview_data" => $overview_data,
            "total_inventory_overview" => $total_overview_data,
            "categories" => CategoryService::getAllActiveCategories(),
            "warehouses" => WarehouseService::getAllActiveWarehouses(),
            "limit" => $limit,
            "page" => $page,
        ];

        require_once __DIR__ . "/../views/inventory/index.php";
    }

    public function exportCSV()
    {
        // $page , $limit
        $page = 1;
        $limit = InventoryService::getTotalInventoryOverview();
        InventoryService::exportCSV($page, $limit);
    }
}
