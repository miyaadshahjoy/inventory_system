<?php
const RECORDS_PER_PAGE = 10;
class StockReportController
{
    /*
    public function index()
    {
        $stock_details_filter = StockReportService::getStockDetailsFilter();
        $movements_summary_filter = StockReportService::getMovementsSummaryFilter();
        $data = [
            "page" => isset($_GET["page"]) ? (int) $_GET["page"] : 1,
            "limit" => RECORDS_PER_PAGE,
            "total_stock_details" => StockReportService::getTotalStockDetails(),
            "total_movements_summary" => StockReportService::getTotalMovementsSummary(),
            "current_stock_details" => StockReportService::getCurrentStockDetails(
                $stock_details_filter,
            ),
            "stock_movements_summary" => StockReportService::getStockMovementSummary(
                $movements_summary_filter,
            ),
            "categories" => CategoryService::getAllActiveCategories(),
            "warehouses" => WarehouseService::getAllActiveWarehouses(),
        ];
        require_once __DIR__ . "/../views/inventory/stock_report.php";
    }
    */

    /*
    public function getCurrentStockDetails()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            $this->sendHttpResponse(405, "error", "Invalid request method.");
        }

        $filter_data = StockReportService::getStockDetailsFilter();
        $stock_details = StockReportService::getCurrentStockDetails(
            $filter_data,
        );

        if (empty($stock_details)) {
            $this->sendHttpResponse(404, "error", "No stock details found.");
        }

        # ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Fetched all stock details successfully.",
            "result" => count($stock_details),

            "data" => [
                "stock_details" => $stock_details,
            ],
        ]);
        exit();
        # ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    }
        */

    public function getCurrentStockDetails()
    {
        $stock_details_filter = StockReportService::getStockDetailsFilter();
        [
            "results" => $current_stock_details,
            "length" => $total_stock_details,
        ] = StockReportService::getCurrentStockDetails($stock_details_filter);

        $data = [
            "page" => isset($_GET["page"]) ? (int) $_GET["page"] : 1,
            "limit" => RECORDS_PER_PAGE,
            "total_stock_details" => $total_stock_details,
            "current_stock_details" => $current_stock_details,
            "categories" => CategoryService::getAllActiveCategories(),
            "warehouses" => WarehouseService::getAllActiveWarehouses(),
        ];
        require_once __DIR__ .
            "/../views/inventory/stock_report/stock_details.php";
    }
    public function getStockMovementsSummary()
    {
        $movements_summary_filter = StockReportService::getMovementsSummaryFilter();
        $stock_movements_summary = StockReportService::getStockMovementSummary(
            $movements_summary_filter,
        )["results"];
        $total_movements_summary = StockReportService::getStockMovementSummary(
            $movements_summary_filter,
        )["length"];
        $data = [
            "page" => isset($_GET["page"]) ? (int) $_GET["page"] : 1,
            "limit" => RECORDS_PER_PAGE,
            "total_movements_summary" => $total_movements_summary,

            "stock_movements_summary" => $stock_movements_summary,
            "warehouses" => WarehouseService::getAllActiveWarehouses(),
        ];
        require_once __DIR__ .
            "/../views/inventory/stock_report/movements_summary.php";
    }

    public function sendHttpResponse(
        int $statusCode,
        string $status,
        string $message,
        array $data = null,
    ) {
        http_response_code($statusCode);
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data ?? [],
        ]);
        exit();
    }
}
