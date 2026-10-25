(function () {
  function updateCart() {
    const cart = document.querySelector("#app .section-content");
    if (!cart || !document.getElementById("cart-subtotal")) return;

    let subtotal = 0;
    cart.querySelectorAll("tbody tr").forEach(function (row) {
      const input = row.querySelector('input[type="number"]');
      const priceEl = row.querySelector(".price");
      const unitEl = row.querySelector(".price-wrap small");
      if (!input || !priceEl || !unitEl) return;

      const unit = parseFloat(unitEl.textContent.replace(/[^0-9.]/g, "")) || 0;
      const qty = Math.max(1, parseInt(input.value, 10) || 1);
      input.value = qty;

      const total = unit * qty;
      priceEl.textContent = "$" + total.toFixed(2);
      subtotal += total;
    });

    const tax = subtotal * 0.07;
    document.getElementById("cart-subtotal").textContent = "$" + subtotal.toFixed(2);
    document.getElementById("cart-tax").textContent = "$" + tax.toFixed(2);
    document.getElementById("cart-total").textContent = "$" + (subtotal + tax).toFixed(2);
  }

  document.addEventListener("click", function (e) {
    const btn = e.target.closest("#app button[data-action]");
    if (!btn) return;

    const row = btn.closest("tr");
    const input = row && row.querySelector('input[type="number"]');

    if (btn.dataset.action === "remove" && row) {
      row.remove();
    } else if (input && btn.dataset.action === "increase") {
      input.value = (parseInt(input.value, 10) || 1) + 1;
    } else if (input && btn.dataset.action === "decrease") {
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
    }
    updateCart();
  });

  document.addEventListener("change", function (e) {
    if (e.target.matches('#app input[type="number"]')) updateCart();
  });

  document.addEventListener("viewLoaded", function (e) {
    if (e.detail.route === "cart") updateCart();
  });
})();