<?php
$movements = $data['movements'] ?? [];
ob_start();
?>

<h1>Stock Movement History</h1>
<button onclick="window.location.href = '/stock-movements/create'">
  New Movement
</button>
<!-- TODO: Filter data -->
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Product</th>
      <th>Type</th>
      <th>Direction</th>
      <th>Created By</th>
      <th>Created At</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($movements as $movement): ?>
      <tr>
        <td><?= $movement['id']; ?></td>
        <td><?= $movement['product']; ?></td>
        <td><?= $movement['type']; ?></td>
        <td><?= $movement['direction']; ?></td>
        <td><?= $movement['created_by']; ?></td>
        <td><?= $movement['created_at']; ?></td>
        <td><?= $movement['notes']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/../layouts/layout.php';
?>