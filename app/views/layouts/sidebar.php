<?php
/*
$current_uri = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH);

function isActive(string $path, string $current_uri): string
{
  return str_starts_with($current_uri, $path) ? "sidebar-active" : "";
  }
  */

$current_uri = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH);
function isActive(string $path, string $current_uri): string
{
    return $current_uri === $path || str_starts_with($current_uri, $path . "/")
        ? "sidebar-active"
        : "";
}
?>

<aside id="sidebar" class="sidebar">
  <div class="sidebar-logo">
    <h2>InventorySys</h2>
  </div>

  <nav class="sidebar-nav">
    <!-- OVERVIEW -->
    <div class="sidebar-section-title">OVERVIEW</div>

    <a href="/dashboard" class="sidebar-link <?= isActive(
        "/dashboard",
        $current_uri,
    ) ?>">
      Dashboard
    </a>

    <!-- INVENTORY -->
    <div class="sidebar-section-title">INVENTORY</div>

    <a href="/stock-movements" class="sidebar-link <?= isActive(
        "/stock-movements",
        $current_uri,
    ) ?>">
      Stock Movements
    </a>

    <a href="/inventory-overview" class="sidebar-link <?= isActive(
        "/inventory-overview",
        $current_uri,
    ) ?>">
      Inventory Overview
    </a>

    <!-- Stock Report -->
    <div class="sidebar-group">

      <button type="button" class="sidebar-link sidebar-toggle-link" id="stock-report-toggle">
        Stock Report
        <span class="sidebar-arrow">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6"
            width="24"
            height="24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="m19.5 8.25-7.5 7.5-7.5-7.5"
            />
          </svg>
        </span>
      </button>

      <div class="sidebar-sub-links" id="stock-report-sub-links">

        <a href="/stock-report/stock-details" class="sidebar-sub-link <?= isActive(
            "/stock-report/stock-details",
            $current_uri,
        ) ?>">
          Stock Details
        </a>

        <a href="/stock-report/movements-summary" class="sidebar-sub-link <?= isActive(
            "/stock-report/movements-summary",
            $current_uri,
        ) ?>">
          Movements Summary
        </a>

      </div>

    </div>

    <!-- PROCUREMENT -->
    <div class="sidebar-section-title">PROCUREMENT</div>
    <!-- Purchase Orders -->
    <a href="/purchase-orders" class="sidebar-link <?= isActive(
        "/purchase-orders",
        $current_uri,
    ) ?>">
      Purchase Orders
    </a>
    <!-- Purchase Items -->
    <a href="/purchase-items" class="sidebar-link <?= isActive(
        "/purchase-items",
        $current_uri,
    ) ?>">
      Purchase Items
    </a>

    <!-- Suppliers -->
    <a href="/suppliers" class="sidebar-link <?= isActive(
        "/suppliers",
        $current_uri,
    ) ?>">
      Suppliers
    </a>

    <div class="sidebar-section-title">CATALOG</div>

    <a href="/products" class="sidebar-link <?= isActive(
        "/products",
        $current_uri,
    ) ?>">
      Products
    </a>

    <a href="/categories" class="sidebar-link <?= isActive(
        "/categories",
        $current_uri,
    ) ?>">
      Categories
    </a>

    <a href="/warehouses" class="sidebar-link <?= isActive(
        "/warehouses",
        $current_uri,
    ) ?>">
      Warehouses
    </a>

    <div class="sidebar-section-title">SYSTEM</div>
    <?php if ($_SESSION["user"]["role"] === "ADMIN"): ?>
      <a href="/users" class="sidebar-link <?= isActive(
          "/users",
          $current_uri,
      ) ?>"> Users & Roles </a>
    <?php endif; ?>
    <a href="/logout" class="sidebar-link <?= isActive(
        "/logout",
        $current_uri,
    ) ?>">Logout</a>
  </nav>
</aside>

<?php if (
    str_starts_with($current_uri, "/stock-report/stock-details") ||
    str_starts_with($current_uri, "/stock-report/movements-summary")
): ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document
      .getElementById("stock-report-sub-links")
      .classList.add("open");

    document
      .querySelector(".sidebar-arrow")
      .classList.add("rotate");
});
</script>

<?php endif; ?>
