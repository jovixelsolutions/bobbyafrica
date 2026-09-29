(function(api) {

    api.sectionConstructor['ecommerce-gift-cart-buynow'] = api.Section.extend({
        attachEvents: function() {},
        isContextuallyActive: function() {
            return true;
        }
    });

})(wp.customize);

document.addEventListener("DOMContentLoaded", function () {
    const counters = document.querySelectorAll(".counters-text h2");
  
    counters.forEach(counter => {
      const targetText = counter.textContent.trim();
      const targetNumber = parseInt(targetText.replace("+", ""), 10);
  
      let count = 0;
      const duration = 2000; // total duration in ms
      const stepTime = Math.max(Math.floor(duration / targetNumber), 20); // speed per step
  
      const increment = () => {
        count++;
        if (count <= targetNumber) {
          counter.textContent = count + "+";
          setTimeout(increment, stepTime);
        } else {
          counter.textContent = targetNumber + "+";
        }
      };
  
      increment();
    });
  });  