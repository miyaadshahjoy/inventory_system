<?php

class StockReportController
{
    public function index()
    {
        $data = [
            "current_stock_details" => StockReportService::getCurrentStockDetails(),
            "stock_movements_summary" => StockReportService::getStockMovementSummary(),
        ];
        require_once __DIR__ . "/../views/inventory/stock_report.php";
    }
}
