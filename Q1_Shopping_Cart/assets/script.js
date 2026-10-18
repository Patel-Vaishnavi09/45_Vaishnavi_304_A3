document.addEventListener("DOMContentLoaded", () => {
  let category = 0;
  const products = document.getElementById("products"),
    pages = document.getElementById("pages"),
    status = document.getElementById("status");
  async function load(page = 1) {
    status.textContent = "Loading products...";
    products.innerHTML = "";
    pages.innerHTML = "";
    try {
      const r = await fetch(
        "products_ajax.php?category=" + category + "&page=" + page,
      );
      const d = await r.json();
      if (!r.ok) throw new Error(d.error || "Request failed");
      products.innerHTML =
        d.html || "<p>No products found in this category.</p>";
      status.textContent = d.total + " product(s)";
      for (let i = 1; i <= d.pages; i++) {
        const b = document.createElement("button");
        b.textContent = i;
        b.className = "page-button" + (i === d.page ? " current" : "");
        b.addEventListener("click", () => load(i));
        pages.appendChild(b);
      }
    } catch (e) {
      status.textContent =
        "Could not load products. Check PHP errors and database setup.";
    }
  }
  document.querySelectorAll(".category-link").forEach((b) =>
    b.addEventListener("click", () => {
      document
        .querySelectorAll(".category-link")
        .forEach((x) => x.classList.remove("active"));
      b.classList.add("active");
      category = Number(b.dataset.category);
      load(1);
    }),
  );
  load(1);
});
