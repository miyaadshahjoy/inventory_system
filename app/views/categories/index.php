<?php
$categories = $data["categories"] ?? [];
ob_start();
?>

<!-- Showing category list -->
<div class="container">
  <div class="container-header">
    <h2>Product Categories</h2>
    <!-- Add new category button -->
    <button onclick="openModal()">+ Add new category</button>
  </div>

  <?php if (empty($categories)): ?>
    <p>No categories found.</p>
  <?php endif; ?>

  <?php if (!empty($categories)): ?>
    <div class="table-wrapper">
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
              <td><?= $category["name"] ?></td>
              <td class="categoryStatus" data-categoryID="<?= $category[
                  "id"
              ] ?>"><?= $category["categories_status"] ?></td>
              <td><?php
              $created_at = new DateTime($category["created_at"]);
              echo $created_at->format("Y-m-d H:i");
              ?></td>
              <td>
                <div class="actions categoryActions <?= $category[
                    "categories_status"
                ] === "INACTIVE"
                    ? "hide"
                    : "" ?>"
                  data-categoryId="<?= $category["id"] ?>">

                  <button data-categoryId="<?= htmlspecialchars(
                      $category["id"],
                  ) ?>"
                    data-categoryName="<?= htmlspecialchars(
                        $category["name"],
                    ) ?>"
                    onclick="openCategoryUpdateModal(this)">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="size-6"
                      height="24px"
                      width="24px"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                      />
                    </svg>
                  </button>
                  <a href="/categories/delete?id=<?= $category["id"] ?>">
                    <button>
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6"
                        height="24px"
                        width="24px"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                        />
                      </svg>

                    </button>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- 
# ADD NEW CATEGORY 
  -->
<!-- Modal for adding a new category -->
<div id="modal" class="modal modal-narrow categories-modal">
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
<div id="categoryUpdateModal" class="modal modal-narrow categories-modal">
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
require_once __DIR__ . "/../layouts/layout.php";


?>
