/*
const productSearchFormEl = document.querySelector(".product-search");

productSearchFormEl.addEventListener("submit", async (e) => {
  e.preventDefault();

  const searchQuery =
    productSearchFormEl.querySelector("#product-search").value;
  try {
    const response = await fetch(
      `/stock-report/stock-details?search=${searchQuery}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      },
    );
    if (response.ok) {
      const data = await response.json();
    }
  } catch (error) {
    console.error(error);
  }
});
*/
