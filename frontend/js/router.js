console.log("router.js loaded");

(function () {
  const app = document.getElementById("app");

  const routes = {
    home: "views/home.html",
    store: "views/store.html",
    about: "views/about.html",
    contact: "views/contact.html",
    signin: "views/signin.html",
    register: "views/register.html",
    cart: "views/cart.html",
    checkout: "views/checkout.html",
    "product-detail": "views/product-detail.html",
    "order-complete": "views/order-complete.html",
    dashboard: "views/dashboard.html",
  };

  function getRoute() {
    return window.location.hash.replace(/^#\/?/, "") || "home";
  }

  async function loadView() {
    const route = getRoute();
    const url = routes[route];

    if (!url) {
      app.innerHTML = '<div class="container padding-y"><h2>404</h2><p>Page not found.</p></div>';
      return;
    }

    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error(res.status + " " + url);
      app.innerHTML = await res.text();
      window.scrollTo(0, 0);
      document.querySelectorAll(".navbar-nav .nav-link").forEach(function (a) {
        a.classList.toggle("active", a.getAttribute("href") === "#" + route);
      });
      document.dispatchEvent(new CustomEvent("viewLoaded", { detail: { route: route } }));
    } catch (err) {
      app.innerHTML = '<div class="container padding-y"><p>Could not load page.</p></div>';
      console.error("Router error:", err);
    }
  }

  document.addEventListener("submit", function (e) {
    if (e.target.id === "search-form") {
      e.preventDefault();
      window.location.hash = "#store";
    }
  });

  window.addEventListener("hashchange", loadView);
  loadView();
})();