document.addEventListener('DOMContentLoaded', function () {
  const searchForms = document.querySelectorAll('.marketplace-search');

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

  const primaryNav = document.querySelector('.marketplace-primary-nav');
  const categoryToggle = primaryNav && primaryNav.querySelector('.marketplace-nav-categories');
  const categoryMega = primaryNav && primaryNav.querySelector('.marketplace-category-mega');

  if (primaryNav && categoryToggle && categoryMega) {
    const categoryLinks = Array.from(categoryMega.querySelectorAll('[data-mega-category]'));
    const categoryPanels = Array.from(categoryMega.querySelectorAll('[data-category-panel]'));

    function loadSubcategoryProducts(categoryId, productList) {
      if (!productList || productList.dataset.loaded === 'true' || productList.dataset.loading === 'true') {
        return;
      }

      productList.dataset.loading = 'true';
      const loading = productList.querySelector('.marketplace-mega-loading');
      const results = productList.querySelector('.marketplace-mega-product-results');
      if (loading) {
        loading.hidden = false;
      }

      const request = new FormData();
      request.append('action', 'bobbyafrica_category_products');
      request.append('nonce', bobbyafricaMarketData.nonce);
      request.append('category_id', categoryId);

      fetch(bobbyafricaMarketData.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: request
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Product request failed');
          }
          return response.json();
        })
        .then(function (response) {
          if (!response.success || !results) {
            throw new Error('Product list unavailable');
          }
          results.innerHTML = response.data.html;
          productList.dataset.loaded = 'true';
        })
        .catch(function () {
          if (results) {
            results.textContent = 'Products could not be loaded. Open the category to browse.';
          }
        })
        .finally(function () {
          delete productList.dataset.loading;
          if (loading) {
            loading.hidden = true;
          }
        });
    }

    function activateSubcategory(link, panel) {
      panel.querySelectorAll('[data-mega-subcategory]').forEach(function (subcategoryLink) {
        subcategoryLink.setAttribute('aria-current', subcategoryLink === link ? 'true' : 'false');
      });
      panel.querySelectorAll('[data-expand-subcategory]').forEach(function (button) {
        button.setAttribute('aria-expanded', button.getAttribute('data-expand-subcategory') === link.getAttribute('data-mega-subcategory') ? 'true' : 'false');
      });
      panel.querySelectorAll('[data-mega-products]').forEach(function (productList) {
        productList.hidden = productList.id !== link.getAttribute('aria-controls');
      });

      const products = document.getElementById(link.getAttribute('aria-controls'));
      loadSubcategoryProducts(link.getAttribute('data-mega-subcategory'), products);
    }

    function activateCategory(categoryId) {
      const panel = categoryPanels.find(function (candidate) {
        return candidate.getAttribute('data-category-panel') === String(categoryId);
      });
      if (!panel) {
        return;
      }

      categoryLinks.forEach(function (link) {
        link.setAttribute('aria-current', link.getAttribute('data-mega-category') === String(categoryId) ? 'true' : 'false');
      });
      categoryMega.querySelectorAll('[data-expand-category]').forEach(function (button) {
        button.setAttribute('aria-expanded', button.getAttribute('data-expand-category') === String(categoryId) ? 'true' : 'false');
      });
      categoryPanels.forEach(function (candidate) {
        candidate.hidden = candidate !== panel;
      });

      const firstSubcategory = panel.querySelector('[data-mega-subcategory]');
      if (firstSubcategory) {
        activateSubcategory(firstSubcategory, panel);
      }
    }

    function openCategoryMenu() {
      categoryMega.hidden = false;
      categoryToggle.setAttribute('aria-expanded', 'true');
      const selectedCategory = categoryLinks.find(function (link) {
        return link.getAttribute('aria-current') === 'true';
      }) || categoryLinks[0];
      if (selectedCategory) {
        activateCategory(selectedCategory.getAttribute('data-mega-category'));
      }
    }

    function closeCategoryMenu() {
      categoryMega.hidden = true;
      categoryToggle.setAttribute('aria-expanded', 'false');
    }

    categoryToggle.addEventListener('click', function () {
      if (categoryMega.hidden) {
        openCategoryMenu();
      } else {
        closeCategoryMenu();
      }
    });
    categoryToggle.addEventListener('mouseenter', openCategoryMenu);
    categoryToggle.addEventListener('focus', openCategoryMenu);

    categoryLinks.forEach(function (link) {
      const activate = function () {
        activateCategory(link.getAttribute('data-mega-category'));
      };
      link.addEventListener('mouseenter', activate);
      link.addEventListener('focus', activate);
    });

    categoryMega.querySelectorAll('[data-expand-category]').forEach(function (button) {
      button.addEventListener('click', function () {
        openCategoryMenu();
        activateCategory(button.getAttribute('data-expand-category'));
      });
    });

    categoryMega.querySelectorAll('[data-mega-subcategory]').forEach(function (link) {
      const activate = function () {
        const panel = link.closest('[data-category-panel]');
        if (panel) {
          activateSubcategory(link, panel);
        }
      };
      link.addEventListener('mouseenter', activate);
      link.addEventListener('focus', activate);
    });

    categoryMega.querySelectorAll('[data-expand-subcategory]').forEach(function (button) {
      button.addEventListener('click', function () {
        const link = categoryMega.querySelector('[data-mega-subcategory="' + button.getAttribute('data-expand-subcategory') + '"]');
        const panel = link && link.closest('[data-category-panel]');
        if (link && panel) {
          activateCategory(panel.getAttribute('data-category-panel'));
          activateSubcategory(link, panel);
        }
      });
    });

    primaryNav.addEventListener('mouseleave', function () {
      if (!primaryNav.contains(document.activeElement)) {
        closeCategoryMenu();
      }
    });
    primaryNav.addEventListener('focusout', function (event) {
      if (!primaryNav.contains(event.relatedTarget) && !primaryNav.matches(':hover')) {
        closeCategoryMenu();
      }
    });
    document.addEventListener('click', function (event) {
      if (!primaryNav.contains(event.target)) {
        closeCategoryMenu();
      }
    });
    primaryNav.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !categoryMega.hidden) {
        closeCategoryMenu();
        categoryToggle.focus();
      }
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

  document.querySelectorAll('[data-marketplace-product-carousel]').forEach(function (carousel) {
    const section = carousel.closest('.marketplace-product-section');
    const buttons = section ? section.querySelectorAll('[data-carousel-scroll]') : [];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const firstCard = carousel.querySelector('.product-card');
    let timer = null;

    if (!firstCard) {
      return;
    }

    function scrollByCard(direction) {
      const styles = window.getComputedStyle(carousel);
      const gap = parseFloat(styles.columnGap || styles.gap) || 0;
      const distance = firstCard.getBoundingClientRect().width + gap;
      const atEnd = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 4;
      if (direction > 0 && atEnd) {
        carousel.scrollTo({ left: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
        return;
      }
      carousel.scrollBy({ left: direction * distance, behavior: reducedMotion ? 'auto' : 'smooth' });
    }

    function stopRotation() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function startRotation() {
      stopRotation();
      if (!reducedMotion && !document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
        timer = window.setInterval(function () {
          scrollByCard(1);
        }, 3500);
      }
    }

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        scrollByCard(Number(button.getAttribute('data-carousel-scroll')) || 1);
        startRotation();
      });
    });
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

  document.querySelectorAll('[data-marketplace-banner-carousel]').forEach(function (carousel) {
    const slides = Array.from(carousel.querySelectorAll('[data-marketplace-banner-slide]'));
    const buttons = carousel.querySelectorAll('[data-banner-step]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (slides.length < 2) {
      return;
    }

    let activeIndex = 0;
    let timer = null;

    function showSlide(index) {
      activeIndex = (index + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        const active = slideIndex === activeIndex;
        slide.hidden = !active;
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
    }

    function stopRotation() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function startRotation() {
      stopRotation();
      if (!reducedMotion && !document.hidden && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
        timer = window.setInterval(function () {
          showSlide(activeIndex + 1);
        }, 5000);
      }
    }

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        showSlide(activeIndex + Number(button.getAttribute('data-banner-step')));
        startRotation();
      });
    });
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

});
