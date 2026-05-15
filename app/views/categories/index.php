<?php
$categories = $data['categories'] ?? [];
ob_start();
?>
<div class="container">

  <div class="container-header">

    <h2>Product Categories</h2>
    <button onclick="openModal()">+ Add new category</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>Category Name</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $category): ?>
        <tr>
          <td><?= $category['name'] ?></td>
          <td><?= $category['categories_status'] ?></td>
          <td><?php $created_at = new DateTime($category['created_at']);
          echo $created_at->format('Y-m-d H:i'); ?></td>
          <td>
            <button onclick="openCategoryUpdateModal(<?= $category['id'] ?>)">Update</button>
            <button onclick="openCategoryDeleteModal(<?= $category['id'] ?>)">Delete</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>

<!-- Modal for adding a new category -->
<div id="modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Category</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>
    <form action="/categories/form-submit" method="post">
      <!-- <label for="name">Category Name</label> -->
      <input type="text" id="categoryName" name="name" placeholder="Enter category name" required />
      <button type="submit">Add Category</button>
    </form>
  </div>
</div>


<!-- Modal for updating a category -->
<div id="categoryUpdateModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Update Category</h3>
      <span class="close" onclick="closeCategoryUpdateModal()">×</span>
    </div>
    <form action="/categories/update/form-submit" method="post">
      <!-- <label for="name">Category Name</label> -->
      <input type="text" id="categoryName" name="name" placeholder="Enter category name" required />
      <button type="submit">Update Category</button>
    </form>
  </div>
</div>



<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>