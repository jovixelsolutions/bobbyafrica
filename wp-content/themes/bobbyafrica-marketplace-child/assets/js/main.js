document.addEventListener('DOMContentLoaded', function () {
  const searchForms = document.querySelectorAll('.marketplace-search');
  const mobileMenuButton = document.querySelector('.marketplace-mobile-menu button');

  searchForms.forEach(function (form) {
    const input = form.querySelector('input[type="search"]');
    if (!input) {
      return;
    }

    input.addEventListener('focus', function () {
      form.classList.add('is-focused');
    });

    input.addEventListener('blur', function () {
      form.classList.remove('is-focused');
    });
  });

  if (mobileMenuButton) {
    mobileMenuButton.addEventListener('click', function () {
      const menu = document.querySelector('.marketplace-mobile-menu .marketplace-nav-links');
      if (!menu) {
        return;
      }
      menu.hidden = !menu.hidden;
    });
  }

  document.querySelectorAll('[data-marketplace-carousel]').forEach(function (carousel) {
    const copySlides = Array.from(carousel.querySelectorAll('[data-carousel-copy]'));
    const imageSlides = Array.from(carousel.querySelectorAll('[data-carousel-image]'));
    const progressBar = carousel.querySelector('.marketplace-hero-progress-bar');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (copySlides.length < 2 || copySlides.length !== imageSlides.length) {
      return;
    }

    let activeIndex = 0;
    let timer = null;
    let userPaused = reducedMotion;

    function showSlide(index) {
      activeIndex = (index + copySlides.length) % copySlides.length;

      copySlides.forEach(function (slide, slideIndex) {
        const active = slideIndex === activeIndex;
        slide.hidden = !active;
        slide.classList.toggle('is-active', active);
      });

      imageSlides.forEach(function (slide, slideIndex) {
        const active = slideIndex === activeIndex;
        slide.hidden = !active;
        slide.classList.toggle('is-active', active);
        const link = slide.querySelector('.marketplace-hero-product');
        if (link) {
          link.setAttribute('tabindex', active ? '0' : '-1');
        }
      });
    }

    function stopRotation() {
      if (timer) {
        window.clearTimeout(timer);
        timer = null;
      }
      carousel.classList.remove('is-rotating');
      if (progressBar) {
        progressBar.style.animation = 'none';
      }
    }

    function startRotation() {
      stopRotation();
      if (userPaused || document.hidden || carousel.matches(':hover') || carousel.contains(document.activeElement)) {
        return;
      }
      carousel.classList.add('is-rotating');
      if (progressBar) {
        progressBar.style.animation = '';
        void progressBar.offsetWidth;
      }
      timer = window.setTimeout(function () {
        showSlide(activeIndex + 1);
        startRotation();
      }, 6000);
    }

    carousel.addEventListener('mouseenter', stopRotation);
    carousel.addEventListener('mouseleave', startRotation);
    carousel.addEventListener('focusin', stopRotation);
    carousel.addEventListener('focusout', function (event) {
      if (!carousel.contains(event.relatedTarget)) {
        startRotation();
      }
    });
    document.addEventListener('visibilitychange', startRotation);

    startRotation();
  });

  const mobileBuyBar = document.querySelector('.marketplace-mobile-buy-bar');
  if (mobileBuyBar) {
    const mobileBuyButton = mobileBuyBar.querySelector('.marketplace-mobile-buy-button');
    const mobilePrice = mobileBuyBar.querySelector('.marketplace-mobile-buy-price');
    const cartForm = document.querySelector('.single-product form.cart');
    const nativeBuyButton = cartForm && cartForm.querySelector('.single_add_to_cart_button');
    const variationForm = document.querySelector('.single-product form.variations_form');
    const originalPrice = mobilePrice ? mobilePrice.innerHTML : '';

    if (mobileBuyButton) {
      mobileBuyButton.addEventListener('click', function () {
        if (!cartForm) {
          return;
        }

        cartForm.scrollIntoView({ behavior: 'smooth', block: 'center' });

        if (variationForm) {
          const firstOption = variationForm.querySelector('.variable-item[tabindex], [role="radio"], select');
          if (firstOption) {
            firstOption.focus({ preventScroll: true });
          }
          return;
        }

        if (nativeBuyButton) {
          nativeBuyButton.click();
        }
      });
    }

    if (variationForm && mobilePrice && window.jQuery) {
      window.jQuery(variationForm).on('found_variation', function (event, variation) {
        if (variation && variation.price_html) {
          mobilePrice.innerHTML = variation.price_html;
        }
      });
      window.jQuery(variationForm).on('reset_data hide_variation', function () {
        mobilePrice.innerHTML = originalPrice;
      });
    }
  }

  document.querySelectorAll('[data-product-id]').forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();
      const productId = button.getAttribute('data-product-id');
      if (!productId) {
        return;
      }

      const request = new XMLHttpRequest();
      request.open('POST', bobbyafricaMarketData.ajaxurl, true);
      request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      request.onreadystatechange = function () {
        if (request.readyState !== 4) {
          return;
        }

        if (request.status >= 200 && request.status < 300) {
          window.location.href = '/bobbyafrica/cart/';
        }
      };

      request.send('action=woocommerce_add_to_cart&product_id=' + encodeURIComponent(productId) + '&quantity=1&_wpnonce=' + encodeURIComponent(bobbyafricaMarketData.nonce));
    });
  });
});
