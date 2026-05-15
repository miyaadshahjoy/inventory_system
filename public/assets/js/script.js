const modal = document.getElementById("modal");
const categoryUpdateModal = document.getElementById("categoryUpdateModal");

function openModal() {
  modal.classList.add("show");
}
function openCategoryUpdateModal() {
  categoryUpdateModal.classList.add("show");
}
function closeModal() {
  modal.classList.remove("show");
}
function closeCategoryUpdateModal() {
  categoryUpdateModal.classList.remove("show");
}

window.addEventListener("click", function (event) {
  if (event.target === modal) {
    closeModal();
  }
  if (event.target === categoryUpdateModal) {
    closeCategoryUpdateModal();
  }
});

window.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeModal();
    closeCategoryUpdateModal();
  }
});
