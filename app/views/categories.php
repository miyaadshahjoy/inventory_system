<?php
$categories = $data['categories'] ?? [];
?>

<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Product Categories</title>
  </head>

  <body>
    <h2>Product Categories</h2>
    <button>add new category</button>

    <table>
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Status</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $category): ?>
          <tr>
            <td><?= $category['name'] ?></td>
            <td><?= $category['categories_status'] ?></td>
            <td><?= $category['created_at'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Modal for adding a new category -->
    <form action="/categories/submit" method="post">
      <label for="name">Category Name:</label>
      <input type="text" id="categoryName" name="name" required />
      <button type="submit">Add Category</button>
    </form>
  </body>

</html>