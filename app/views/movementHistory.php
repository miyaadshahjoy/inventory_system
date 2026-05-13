<?php
$movements = $data['movements'] ?? [];
?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Movement History</title>
  </head>

  <body>
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
  </body>

</html>