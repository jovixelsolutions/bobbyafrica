// Scroll to Top
document.addEventListener('DOMContentLoaded', () => {
  const ecommerce_gift_cart_btn = document.body.appendChild(document.createElement('button'));
  ecommerce_gift_cart_btn.className = 'return-to-top-btn';
  ecommerce_gift_cart_btn.innerHTML = '<span class="dashicons dashicons-arrow-up-alt"></span>';

  window.addEventListener('scroll', () =>
    ecommerce_gift_cart_btn.classList.toggle('show', window.scrollY > 300)
  );

  ecommerce_gift_cart_btn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Main Slider Js
jQuery(document).ready(function(){
  var owl = jQuery('.owl-carousel');
    owl.owlCarousel({
    margin: 20,
    nav: false,
    autoplay: true,
    lazyLoad: true,
    autoplayTimeout: 3000,
    loop: true,
    dots: true,
    navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
    responsive: {
      0: {
        items: 1,
        nav: false
      },
      600: {
        items: 1
      },
      1000: {
        items: 1
      }
    },
    autoplayHoverPause: true,
    mouseDrag: true
  });
});