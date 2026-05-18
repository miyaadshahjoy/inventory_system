<?php

class InventoryController
{

    public function index()
    {

        $data = [
            'total_skus' => InventoryService::getTotalSKUs(),
            'total_stocks' => InventoryService::getTotalStock(),
            'total_stock_value' => InventoryService::getTotalStockValue(),
            'total_low_stocks' => InventoryService::getTotalLowStocks(),
            'total_out_stocks' => InventoryService::getTotalOutStocks(),
            'total_movements_today' => InventoryService::getTotalMovementToday(),
            'inventory_overview_data' => InventoryService::getInventoryOverviewData()
        ];

        require_once __DIR__ . '/../views/inventory/index.php';
    }
}