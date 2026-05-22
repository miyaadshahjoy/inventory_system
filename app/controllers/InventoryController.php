<?php
const OVERVIEW_PER_PAGE = 10;
class InventoryController
{


    public function index()
    {

        # Get page number
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $total_inventory_overview = InventoryService::getTotalInventoryOverview();
        $limit = OVERVIEW_PER_PAGE;

        $overview_data = InventoryService::getInventoryOverviewData($page, $limit);

        $data = [
            'total_skus' => InventoryService::getTotalSKUs(),
            'total_stocks' => InventoryService::getTotalStock(),
            'total_stock_value' => InventoryService::getTotalStockValue(),
            'total_low_stocks' => InventoryService::getTotalLowStocks(),
            'total_out_stocks' => InventoryService::getTotalOutStocks(),
            'total_movements_today' => InventoryService::getTotalMovementToday(),
            'inventory_overview_data' => $overview_data,
            'total_inventory_overview' => $total_inventory_overview,
            'limit' => $limit,
            'page' => $page
        ];

        require_once __DIR__ . '/../views/inventory/index.php';
    }


    public function exportCSV()
    {

        // $page , $limit
        $page = 1;
        $limit = InventoryService::getTotalInventoryOverview();
        InventoryService::exportCSV($page, $limit);
    }

}