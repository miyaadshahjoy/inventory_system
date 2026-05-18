<?php
$categories = $data['categories'] ?? [];
ob_start();
?>

<!-- Showing category list -->
<div class="container">
  <div class="container-header">
    <h2>Product Categories</h2>
    <!-- Add new category button -->
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
          <td class="categoryStatus" data-categoryID="<?= $category['id'] ?>"><?= $category['categories_status'] ?></td>
          <td><?php $created_at = new DateTime($category['created_at']);
          echo $created_at->format('Y-m-d H:i'); ?></td>
          <td>
            <div class="categoryActions <?= $category['categories_status'] === 'INACTIVE' ? 'hide' : '' ?>"
              data-categoryId="<?= $category['id'] ?>">

              <button data-categoryId="<?= htmlspecialchars($category['id']) ?>"
                data-categoryName="<?= htmlspecialchars($category['name']) ?>"
                onclick="openCategoryUpdateModal(this)">Edit</button>
              <button data-categoryId="<?= htmlspecialchars($category['id']) ?>"
                onclick="deleteCategory(this)">Delete</button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 
# ADD NEW CATEGORY 
-->
<!-- Modal for adding a new category -->
<div id="modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Create Category</h3>
      <span class="close" onclick="closeModal()">×</span>
    </div>
    <form class="category-form" action="/categories/form-submit" method="post">
      <div>

        <label for="name">Category Name</label>
        <input type="text" id="" name="name" placeholder="Enter category name" required />
      </div>
      <button type="submit">Add Category</button>
    </form>
  </div>
</div>

<!-- 
# UPDATE CATEGORY
-->
<!-- Modal for updating a category -->
<div id="categoryUpdateModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Update Category</h3>
      <span class="close" onclick="closeCategoryUpdateModal()">×</span>
    </div>
    <form class="category-form" action="/categories/update/form-submit" method="post">
      <input type="text" name="id" id="categoryId" hidden>
      <div>

        <label for="name">Category Name</label>
        <input type="text" id="categoryName" name="name" placeholder="Enter category name" required />
      </div>
      <button type="submit">Update Category</button>
    </form>
  </div>
</div>



<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>