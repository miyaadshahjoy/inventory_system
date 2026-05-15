<?php

$current_uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

function isActive(string $path, string $current_uri): string
{
  return str_starts_with($current_uri, $path)
    ? 'sidebar-active'
    : '';
}
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>InventorySys</h2>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-title">OVERVIEW</div>

    <a href="/dashboard" class="sidebar-link <?= isActive('/dashboard', $current_uri) ?>">
      Dashboard
    </a>

    <div class="sidebar-section-title">INVENTORY</div>

    <a href="/stock-movements" class="sidebar-link <?= isActive('/stock-movements', $current_uri) ?>">
      Stock Movements
    </a>


    <div class="sidebar-section-title">CATALOG</div>

    <a href="/products" class="sidebar-link <?= isActive('/products', $current_uri) ?>">
      Products
    </a>

    <a href="/categories" class="sidebar-link <?= isActive('/categories', $current_uri) ?>">
      Categories
    </a>

    <div class="sidebar-section-title">USERS</div>

    <a href="/users" class="sidebar-link <?= isActive('/users', $current_uri) ?>"> Users </a>
  </nav>
</aside>