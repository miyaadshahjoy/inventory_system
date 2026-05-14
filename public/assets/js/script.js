function openModal() {
  document.getElementById("modal").style.display = "flex";
}

function closeModal() {
  document.getElementById("modal").style.display = "none";
}

window.onclick = function (e) {
  const modal = document.getElementById("modal");
  if (e.target === modal) {
    closeModal();
  }
};
