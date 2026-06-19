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
// Stock report
const stockReportToggle = document.getElementById("stock-report-toggle");
const stockReportLinks = document.getElementById("stock-report-sub-links");
const stockReportArrow = document.querySelector(".sidebar-arrow");

stockReportToggle.addEventListener("click", () => {
  stockReportLinks.classList.toggle("open");
  stockReportArrow.classList.toggle("rotate");
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
          <input
            type="text"
            name="items[${index}][product_id]"
            value="${productId}"
            class="productId"
            required
            hidden
          />
        </td>
        <td>
          <input
            type="text"
            name="items[${index}][product_name]"
            value="${productName}"
            required
            readonly
          />
        </td>
        <td>
          <input
            type="text"
            name="items[${index}][unit]"
            value="${productUnit}"
            required
            readonly
          />
        </td>
        <td>
          <input
            type="number"
            name="items[${index}][unit_price]"
            value="${productPrice}"
            required
            readonly
          />
        </td>
        <td>
          <input
            type="number"
            name="items[${index}][quantity]"
            value="${productQuantity}"
            class="purchase-quantity"
            required
            readonly
          />
        </td>
        <td>
          <button type="button" class="edit-item" onclick="editItem(this)">
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
          <button type="button" class="update-item hide" onclick="updateItem(this)">
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
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
              />
            </svg>
          </button>
          <button type="button" class="delete-item" onclick="removeItem(this)">
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
        </td>
      </tr>
    `;

    purchaseItemsContainer.insertAdjacentHTML("beforeend", html);
    if (purchaseItemsList.querySelectorAll("tr").length > 0) {
      document.querySelector("#purchase-items-table").classList.remove("hide");
    }

    e.currentTarget.querySelector(".purchase-productQuantity").value = "";
    index++;
  }
}

const purchaseItemsList = document.querySelector(".purchase-items-list");
if (purchaseItemsList?.querySelectorAll("tr").length === 0) {
  document.querySelector("#purchase-items-table").classList.add("hide");
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
  if (purchaseItemsList.querySelectorAll("tr").length === 0) {
    document.querySelector("#purchase-items-table").classList.add("hide");
  }
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
  ////////////////////////////////////////////////////////////
  // Movement Page
  if (document.querySelector("#product-1")) {
    new TomSelect("#product-1", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#product-2")) {
    new TomSelect("#product-2", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#product-3")) {
    new TomSelect("#product-3", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });

    if (document.querySelector("#warehouse-1")) {
      new TomSelect("#warehouse-1", {
        create: false,
        sortField: {
          field: "text",
          direction: "asc",
        },
      });
    }
  }
  if (document.querySelector("#warehouse-2")) {
    new TomSelect("#warehouse-2", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }

  if (document.querySelector("#warehouse-3")) {
    new TomSelect("#warehouse-3", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }

  if (document.querySelector("#warehouse-4")) {
    new TomSelect("#warehouse-4", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  /////////////////////////////////////////////////////

  if (document.querySelector("#category")) {
    new TomSelect("#category", {
      create: false,
      sortField: {
        field: "text",
        direction: "asc",
      },
    });
  }
  if (document.querySelector("#stock-status")) {
    new TomSelect("#stock-status", {
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
  if (
    !document.querySelector(".stock-details-tab").classList.contains("active")
  ) {
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  document.querySelector(".stock-details-tab").classList.add("active");
  document.querySelector(".movements-summary-tab").classList.remove("active");
  document.querySelector(".stock-details-tab-content").classList.remove("hide");
  document
    .querySelector(".movements-summary-tab-content")
    .classList.add("hide");
}

function showStockMovementsSummary() {
  if (
    !document
      .querySelector(".movements-summary-tab")
      .classList.contains("active")
  ) {
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  document.querySelector(".movements-summary-tab").classList.add("active");
  document.querySelector(".stock-details-tab").classList.remove("active");
  document
    .querySelector(".movements-summary-tab-content")
    .classList.remove("hide");
  document.querySelector(".stock-details-tab-content").classList.add("hide");
}
