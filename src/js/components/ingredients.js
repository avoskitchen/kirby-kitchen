import ready from "../utils/ready";

class Ingredients {
  constructor(el) {
    this.el = el;

    this.el.addEventListener("click", function(e) {
      const el = e.target.closest(".ingredient");
      el !== null && el.classList.toggle("is-complete");
    });
  }
}


ready(() => {
  const ingredients = document.querySelectorAll(".js-kitchen-ingredients");

  for (let i = 0, l = ingredients.length; i < l; i++) {
    new Ingredients(ingredients[i]);
  }

});
