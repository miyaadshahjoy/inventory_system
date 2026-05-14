<?php
$current_uri = $_SERVER['REQUEST_URI'];

function isActive($path)
{
  global $current_uri;

  return str_contains($current_uri, $path)
    ? 'sidebar-active'
    : '';
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <h2>InventorySys</h2>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-title">MAIN</div>

    <a href="/dashboard" class="sidebar-link <?= isActive('/dashboard') ?>">
      Dashboard
    </a>

    <div class="sidebar-section-title">INVENTORY</div>

    <a href="/inventory" class="sidebar-link <?= isActive('/inventory') ?>">
      Current Inventory
    </a>

    <a href="/stock-movements" class="sidebar-link <?= isActive('/stock-movements') ?>">
      Stock Movements
    </a>

    <div class="sidebar-section-title">CATALOG</div>

    <a href="/products" class="sidebar-link <?= isActive('/products') ?>">
      Products
    </a>

    <a href="/categories" class="sidebar-link <?= isActive('/categories') ?>">
      Categories
    </a>

    <div class="sidebar-section-title">USERS</div>

    <a href="/users" class="sidebar-link <?= isActive('/users') ?>"> Users </a>
  </nav>
</aside>