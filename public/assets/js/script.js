const modal = document.getElementById("modal");
const categoryUpdateModal = document.getElementById("categoryUpdateModal");
const transferMovementModal = document.getElementById("transferModal");
const adjustmentModal = document.getElementById("adjustmentModal");
const productUpdateModal = document.getElementById("productUpdateModal");
const warehouseUpdateModal = document.getElementById("warehouseUpdateModal");
const purchaseOrderItemsContainer = document.querySelector(
  ".purchase-order-products",
);

/////////////////////////////////////////////////////////////////////////////////
const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("sidebarToggle");
const layout = document.querySelector(".app-layout");

function openModal() {
  modal.classList.add("show");
}
function openTransferModal() {
  transferMovementModal.classList.add("show");
}
function openAdjustmentModal() {
  adjustmentModal.classList.add("show");
}
function openCategoryUpdateModal(button) {
  const categoryId = button.getAttribute("data-categoryId");
  const categoryName = button.getAttribute("data-categoryName");
  document.getElementById("categoryId").value = categoryId;
  document.getElementById("categoryName").value = categoryName;

  categoryUpdateModal.classList.add("show");
}

function openProductUpdateModal(button) {
  const productId = button.getAttribute("data-productId");
  const productName = button.getAttribute("data-productName");
  const productSku = button.getAttribute("data-productSKU");
  const productPrice = button.getAttribute("data-productPrice");
  const productReorder = button.getAttribute("data-productReorder");

  document.getElementById("productId").value = productId;
  document.getElementById("productName").value = productName;
  document.getElementById("productSKU").value = productSku;
  document.getElementById("productPrice").value = productPrice;
  document.getElementById("productReorderLevel").value = productReorder;
  productUpdateModal.classList.add("show");
}

function openWarehouseUpdateModal(button) {
  const warehouseId = button.getAttribute("data-warehouseId");
  const warehouseName = button.getAttribute("data-warehouseName");
  const warehouseLocation = button.getAttribute("data-warehouseLocation");
  document.getElementById("warehouseId").value = warehouseId;
  document.getElementById("warehouseName").value = warehouseName;
  document.getElementById("warehouseLocation").value = warehouseLocation;
  warehouseUpdateModal.classList.add("show");
}

async function deleteCategory(button) {
  const categoryId = button.getAttribute("data-categoryId");
  try {
    const response = await fetch(`/categories/delete`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: categoryId }),
    });
    const data = await response.json();
    console.log(data);
    const categoryStatus = data.data.category_status;
    const categoryStatusEls = document.querySelectorAll(".categoryStatus");
    const categoryActionsEls = document.querySelectorAll(".categoryActions");
    categoryStatusEls.forEach((el) => {
      if (el.getAttribute("data-categoryId") === categoryId) {
        el.textContent = categoryStatus;
      }
    });
    categoryActionsEls.forEach((el) => {
      if (el.getAttribute("data-categoryId") === categoryId) {
        el.style.display = "none";
      }
    });
    // location.reload();
  } catch (error) {
    console.error(error);
  }
}

/*
async function deleteProduct(button) {
  const productId = button.getAttribute("data-productId");
  try {
    const response = await fetch(`/products/delete`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: productId }),
    });
    const data = await response.json();
    console.log(data);
    const productStatus = data.data.product_status;
    const productStatusEls = document.querySelectorAll(".productStatus");
    const productActionsEls = document.querySelectorAll(".productActions");
    console.log(productStatusEls, productActionsEls);
    productStatusEls.forEach((el) => {
      if (el.getAttribute("data-productId") === productId) {
        el.textContent = productStatus;
      }
    });
    productActionsEls.forEach((el) => {
      if (el.getAttribute("data-productId") === productId) {
        el.style.display = "none";
      }
    });
  } catch (error) {
    console.error(error);
  }
}
  */

async function deleteWarehouse(button) {
  const warehouseId = button.getAttribute("data-warehouseId");
  try {
    const response = await fetch(`/warehouses/delete`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: warehouseId }),
    });
    const data = await response.json();
    console.log(data);
    const warehouseStatus = data.data.warehouse_status;
    const warehouseStatusEls = document.querySelectorAll(".warehouseStatus");
    const warehouseActionsEls = document.querySelectorAll(".warehouseActions");
    warehouseStatusEls.forEach((el) => {
      if (el.getAttribute("data-warehouseId") === warehouseId) {
        el.textContent = warehouseStatus;
      }
    });
    console.log(warehouseActionsEls, warehouseStatusEls);
    warehouseActionsEls.forEach((el) => {
      if (el.getAttribute("data-warehouseId") === warehouseId) {
        el.style.display = "none";
      }
    });
  } catch (error) {
    console.error(error);
  }
}

async function deleteUser(button) {
  const userId = button.getAttribute("data-userId");
  try {
    const response = await fetch(`/users/delete`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: userId }),
    });
    const data = await response.json();
    console.log(data);
    const userStatus = data.data.user_status;
    const userStatusEls = document.querySelectorAll(".userStatus");
    const userActionsEls = document.querySelectorAll(".userActions");
    userStatusEls.forEach((el) => {
      if (el.getAttribute("data-userId") === userId) {
        el.textContent = userStatus;
      }
    });
    userActionsEls.forEach((el) => {
      if (el.getAttribute("data-userId") === userId) {
        el.style.display = "none";
      }
    });
  } catch (error) {
    console.error(error);
  }
}

function closeModal() {
  modal.classList.remove("show");
}
function closeTransferModal() {
  transferMovementModal.classList.remove("show");
}

function closeAdjustmentModal() {
  adjustmentModal.classList.remove("show");
}
function closeCategoryUpdateModal() {
  categoryUpdateModal.classList.remove("show");
}

function closeProductUpdateModal() {
  productUpdateModal.classList.remove("show");
}

function closeWarehouseUpdateModal() {
  warehouseUpdateModal.classList.remove("show");
}

function showProductList() {
  document.querySelector(".purchase-order-items").classList.remove("hide");
}

window.addEventListener("click", function (event) {
  if (event.target === modal) {
    closeModal();
  }
  if (event.target === transferMovementModal) {
    closeTransferModal();
  }
  if (event.target === categoryUpdateModal) {
    closeCategoryUpdateModal();
  }
  if (event.target === productUpdateModal) {
    closeProductUpdateModal();
  }

  if (event.target === warehouseUpdateModal) {
    closeWarehouseUpdateModal();
  }

  if (event.target === adjustmentModal) {
    closeAdjustmentModal();
  }
});

window.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeModal();
    closeTransferModal();
    closeAdjustmentModal();
    closeCategoryUpdateModal();
    closeProductUpdateModal();
    closeWarehouseUpdateModal();
  }
});

// Flash message
document.addEventListener("DOMContentLoaded", () => {
  const flashes = document.querySelectorAll(".flash");

  flashes.forEach((flash) => {
    setTimeout(() => {
      flash.style.opacity = "0";
      flash.style.transition = "0.3s";

      setTimeout(() => flash.remove(), 300);
    }, 4000);
  });

  flashes.forEach((flash) => {
    setTimeout(() => {
      flash.style.opacity = "0";
      flash.style.transform = "translateY(-5px)";
      flash.style.transition = "all 0.3s ease";

      setTimeout(() => {
        flash.remove();
      }, 300);
    }, 3000);
  });
});

// Sidebar collapse state management
// Load state
if (localStorage.getItem("sidebar") === "hidden") {
  sidebar.classList.add("hidden");
  layout.classList.add("sidebar-hidden");
}

// Toggle
toggleBtn.addEventListener("click", () => {
  sidebar.classList.toggle("hidden");
  layout.classList.toggle("sidebar-hidden");

  if (sidebar.classList.contains("hidden")) {
    localStorage.setItem("sidebar", "hidden");
  } else {
    localStorage.setItem("sidebar", "visible");
  }
});

//////////////////////////////////////////////////////////
// Purchase order form: dynamic item addition

let i = 0;
const product = document.querySelector("#product");
const quantity = document.querySelector("#quantity");
const unitPrice = document.querySelector("#unit_price");

function addItem() {
  if (product.value === "" || quantity.value === "" || unitPrice.value === "") {
    alert(
      "Please fill in all fields for each item.(product, quantity, unit price)",
    );
    return;
  }

  purchaseOrderItemsContainer.insertAdjacentHTML(
    "beforeend",
    `
    <div class="purchase-order-item">
        <input name="items[${i}][product_id]" value="${product.value}" hidden>
        <input name="items[${i}][product_name]" value="${product.selectedOptions[0]?.dataset.productName}" readonly>
        <input name="items[${i}][quantity]" value="${quantity.value}" readonly>
      
        <input name="items[${i}][unit_price]" value="${unitPrice.value}" readonly>
        <span data-product-id=${product.value} onclick="removeItem(this)">❌</span>
    </div>
    `,
  );

  i++;
  //////////////////////////////////////////////
  product.value = "";
  quantity.value = "";
  unitPrice.value = "";
}

function removeItem(button) {
  const productId = button.dataset.productId;
  const products = document.querySelectorAll(".purchase-order-item");
  products.forEach((product) => {
    if (product.querySelector("span").dataset.productId === productId) {
      product.remove();
    }
  });
}

// Purchase Items form: dynamic item addition
let index = 0;
function addPurchaseItem(e) {
  if (
    e.target.classList.contains("add-item") ||
    (e.target.classList.contains("purchase-productQuantity") &&
      e.key === "Enter")
  ) {
    const purchaseItemsContainer = document.querySelector(
      ".purchase-items-list",
    );

    //////////////////////////////////////////////////////////////////
    const productId = Number(
      e.currentTarget.querySelector(".purchase-productId").textContent,
    );

    const productName = String(
      e.currentTarget.querySelector(".purchase-productName").textContent,
    );
    const productUnit = String(
      e.currentTarget.querySelector(".purchase-productUnit").textContent,
    );
    const productPrice = Number(
      e.currentTarget.querySelector(".purchase-productPrice").textContent,
    );
    const productQuantity = Number(
      e.currentTarget.querySelector(".purchase-productQuantity").value,
    );

    if (productQuantity === 0 || productQuantity < 0) {
      alert("Please enter a valid quantity");
      return;
    }

    const productAlreadyExists = Array.from(
      document.querySelectorAll(".productId"),
    ).some((el) => Number(el.value) === productId);

    if (productAlreadyExists) {
      alert("Product already added");
      return;
    }
    //////////////////////////////////////////////////////////////////////////////
    const html = `
      <tr>
          <td class="hide">  
              <input type="text" name="items[${index}][product_id]" value="${productId}" class="productId" required hidden>
          </td>
          <td>
              <input type="text" name="items[${index}][product_name]" value="${productName}" required readonly>
          </td>
          <td>
              <input type="text" name="items[${index}][unit]" value="${productUnit}" required readonly>
          </td>
          <td>
              <input type="number" name="items[${index}][unit_price]" value="${productPrice}" required readonly>
          </td>
          <td>
              <input type="number" name="items[${index}][quantity]" value="${productQuantity}" class="purchase-quantity" required readonly>
          </td>
          <td>
            <button type="button" class="edit-item" onclick="editItem(this)">
              <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.2799 6.40005L11.7399 15.94C10.7899 16.89 7.96987 17.33 7.33987 16.7C6.70987 16.07 7.13987 13.25 8.08987 12.3L17.6399 2.75002C17.8754 2.49308 18.1605 2.28654 18.4781 2.14284C18.7956 1.99914 19.139 1.92124 19.4875 1.9139C19.8359 1.90657 20.1823 1.96991 20.5056 2.10012C20.8289 2.23033 21.1225 2.42473 21.3686 2.67153C21.6147 2.91833 21.8083 3.21243 21.9376 3.53609C22.0669 3.85976 22.1294 4.20626 22.1211 4.55471C22.1128 4.90316 22.0339 5.24635 21.8894 5.5635C21.7448 5.88065 21.5375 6.16524 21.2799 6.40005V6.40005Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M11 4H6C4.93913 4 3.92178 4.42142 3.17163 5.17157C2.42149 5.92172 2 6.93913 2 8V18C2 19.0609 2.42149 20.0783 3.17163 20.8284C3.92178 21.5786 4.93913 22 6 22H17C19.21 22 20 20.2 20 18V13" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
            </button>
            <button type="button" class="update-item hide" onclick="updateItem(this)">
              <svg width="24px" height="24px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <polyline points="2.75 8.75,6.25 12.25,13.25 4.75"></polyline> </g></svg>
            </button>
            <button type="button" class="delete-item" onclick="removeItem(this)">
              <svg width="24px" height="24px" viewBox="0 0 25 25" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>cross</title> <desc>Created with Sketch Beta.</desc> <defs> </defs> <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage"> <g id="Icon-Set" sketch:type="MSLayerGroup" transform="translate(-467.000000, -1039.000000)" fill="#000000"> <path d="M489.396,1061.4 C488.614,1062.18 487.347,1062.18 486.564,1061.4 L479.484,1054.32 L472.404,1061.4 C471.622,1062.18 470.354,1062.18 469.572,1061.4 C468.79,1060.61 468.79,1059.35 469.572,1058.56 L476.652,1051.48 L469.572,1044.4 C468.79,1043.62 468.79,1042.35 469.572,1041.57 C470.354,1040.79 471.622,1040.79 472.404,1041.57 L479.484,1048.65 L486.564,1041.57 C487.347,1040.79 488.614,1040.79 489.396,1041.57 C490.179,1042.35 490.179,1043.62 489.396,1044.4 L482.316,1051.48 L489.396,1058.56 C490.179,1059.35 490.179,1060.61 489.396,1061.4 L489.396,1061.4 Z M485.148,1051.48 L490.813,1045.82 C492.376,1044.26 492.376,1041.72 490.813,1040.16 C489.248,1038.59 486.712,1038.59 485.148,1040.16 L479.484,1045.82 L473.82,1040.16 C472.257,1038.59 469.721,1038.59 468.156,1040.16 C466.593,1041.72 466.593,1044.26 468.156,1045.82 L473.82,1051.48 L468.156,1057.15 C466.593,1058.71 466.593,1061.25 468.156,1062.81 C469.721,1064.38 472.257,1064.38 473.82,1062.81 L479.484,1057.15 L485.148,1062.81 C486.712,1064.38 489.248,1064.38 490.813,1062.81 C492.376,1061.25 492.376,1058.71 490.813,1057.15 L485.148,1051.48 L485.148,1051.48 Z" id="cross" sketch:type="MSShapeGroup"> </path> </g> </g> </g></svg>
            </button>

          </td>
      </tr>

    `;

    purchaseItemsContainer.insertAdjacentHTML("beforeend", html);

    index++;
  }
}

function editItem(button) {
  button.closest("tr").querySelector(".edit-item").classList.add("hide");
  button.closest("tr").querySelector(".update-item").classList.remove("hide");
  button.closest("tr").querySelector(".delete-item").classList.add("hide");

  button
    .closest("tr")
    .querySelector(".purchase-quantity")
    .removeAttribute("readonly");
  button.closest("tr").querySelector(".purchase-quantity").focus();
}

function updateItem(button) {
  button.closest("tr").querySelector(".edit-item").classList.remove("hide");
  button.closest("tr").querySelector(".update-item").classList.add("hide");
  button.closest("tr").querySelector(".delete-item").classList.remove("hide");

  button
    .closest("tr")
    .querySelector(".purchase-quantity")
    .setAttribute("readonly", true);
}

function removeItem(button) {
  button.closest("tr").remove();
}

function addPurchaseItemEnter(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    addPurchaseItem(e);
    e.target.value = "";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.querySelector("#supplier")) {
    new TomSelect("#supplier", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }

  if (document.querySelector("#purchase-order")) {
    new TomSelect("#purchase-order", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#warehouse")) {
    new TomSelect("#warehouse", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#product")) {
    new TomSelect("#product", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#category")) {
    new TomSelect("#category", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
});

///////////////////////////////////////////////////
function showCurrentStockDetails() {
  document.querySelector(".stock-details-tab").classList.add("active");
  document.querySelector(".stock-movements-tab").classList.remove("active");
  document.querySelector(".stock-details-tab-content").classList.remove("hide");
  document.querySelector(".stock-movements-tab-content").classList.add("hide");
}

function showStockMovementsSummary() {
  document.querySelector(".stock-movements-tab").classList.add("active");
  document.querySelector(".stock-details-tab").classList.remove("active");
  document
    .querySelector(".stock-movements-tab-content")
    .classList.remove("hide");
  document.querySelector(".stock-details-tab-content").classList.add("hide");
}
