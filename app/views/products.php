<?php
$products = $data['products'] ?? [];
$categories = $data['categories'] ?? [];
?>

<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products</title>

    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background: #f5f6fa;
      }

      h2 {
        margin-bottom: 10px;
      }

      .container {
        max-width: 1200px;
        margin: auto;
      }

      .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      button {
        padding: 8px 12px;
        border: none;
        background: #2f80ed;
        color: white;
        cursor: pointer;
        border-radius: 4px;
      }

      button:hover {
        background: #1c60c7;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        margin-top: 15px;
      }

      th,
      td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
      }

      th {
        background: #fafafa;
      }

      .table-wrapper {
        overflow-x: auto;
      }

      /* Modal */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
      }

      .modal-content {
        background: white;
        padding: 20px;
        width: 100%;
        max-width: 400px;
        border-radius: 6px;
      }

      .modal-content input,
      .modal-content select {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
      }

      .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .close {
        cursor: pointer;
        font-size: 18px;
      }

      @media (max-width: 600px) {

        th,
        td {
          padding: 8px;
          font-size: 12px;
        }
      }
    </style>
  </head>

  <body>
    <div class="container">

      <div class="header">
        <h2>Products</h2>
        <button onclick="openModal()">+ Add Product</button>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Category</th>
              <th>Stock</th>
              <th>Price</th>
              <th>Unit</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
                  <?php foreach ($products as $product): ?>
              <tr>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td><?= htmlspecialchars($product['sku']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= $product['current_stock'] ?></td>
                <td><?= $product['price'] ?></td>
                <td><?= $product['unit'] ?></td>
                <td><?= $product['status'] ?></td>
                <td><?= $product['created_at'] ?></td>
                <td>
                  <button>Edit</button>
                  <button style="background:#eb5757;">Delete</button>
                </td>
              </tr>
                  <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Create Product</h3>
          <span class="close" onclick="closeModal()">×</span>
        </div>

        <form action="products/submit" method="post">
          <label>Product Name</label>
          <input type="text" name="name" required />

          <label>Category</label>
          <select name="category_id" required>
            <option value="">Select</option>
                <?php foreach ($categories as $category): ?>
              <option value="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
              </option>
                <?php endforeach; ?>
          </select>

          <label>SKU</label>
          <input type="text" name="sku" required />

          <label>Price</label>
          <input type="number" name="price" step="0.01" required />

          <label>Unit</label>
          <select name="unit" required>
            <option value="">Select</option>
            <option value="KG">KG</option>
            <option value="PCS">PCS</option>
            <option value="BOX">BOX</option>
          </select>

          <button type="submit">Create</button>
        </form>
      </div>
    </div>

    <script>
      function openModal() {
        document.getElementById('modal').style.display = 'flex';
      }

      function closeModal() {
        document.getElementById('modal').style.display = 'none';
      }

      window.onclick = function (e) {
        const modal = document.getElementById('modal');
        if (e.target === modal) {
          closeModal();
        }
      }
    </script>

  </body>

</html>