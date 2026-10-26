$(document).ready(function () {

  // dropdown se ne zatvara klikom unutra
  $(document).on("click", ".dropdown-menu", function (e) {
    e.stopPropagation();
  });

  // radio (delegirano, radi i za naknadno učitane view-ove)
  $(document).on("change", ".js-check :radio", function () {
    var name = $(this).attr("name");
    $("input[name='" + name + "']").closest(".js-check").removeClass("active");
    if ($(this).is(":checked")) {
      $(this).closest(".js-check").addClass("active");
    }
  });

  // checkbox
  $(document).on("change", ".js-check :checkbox", function () {
    $(this).closest(".js-check").toggleClass("active", $(this).is(":checked"));
  });

  // contact forma
  $(document).on("submit", "#contact-form", function (e) {
    e.preventDefault();
    alert("Message submitted!");
  });

  // signin forma
  $(document).on("submit", "#signin-form", function (e) {
    e.preventDefault();
    alert("Sign in submitted!");
  });

  // register forma
  $(document).on("submit", "#register-form", function (e) {
    e.preventDefault();
    alert("Registration submitted!");
  });

  // place order (checkout)
  $(document).on("click", "#place-order", function () {
    var form = document.getElementById("checkout-form");
    if (form.reportValidity()) {
      window.location.hash = "#order-complete";
    }
  });

  // tooltip se mora ponovo inicijalizirati nakon svakog view-a
  function initTooltips() {
    if ($('[data-toggle="tooltip"]').length > 0) {
      $('[data-toggle="tooltip"]').tooltip();
    }
  }

  initTooltips();
  document.addEventListener("viewLoaded", initTooltips);
});