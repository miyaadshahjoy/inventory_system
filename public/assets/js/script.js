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
