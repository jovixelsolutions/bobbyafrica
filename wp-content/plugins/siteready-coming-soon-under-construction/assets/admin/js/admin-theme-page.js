document.addEventListener('DOMContentLoaded', function () {

  const searchInput = document.getElementById('theme-search');
  const filterSelect = document.getElementById('theme-filter');
  const themeGrid = document.getElementById('theme-grid');
  const readMoreBtn = document.getElementById('theme-readmore');

  let paginationStack = [''];
  const pageSize = 12;
  let isLoading = false;
  let hasNextPage = true;
  let collectionHandle = '';
  let searchTerm = '';
  let searchTimeout = null;

  const showLoader = () => {
    const loader = document.getElementById('theme-loader');
    if (loader) loader.style.display = 'block';
    // themeGrid.style.display = 'none';
  };

  const hideLoader = () => {
    const loader = document.getElementById('theme-loader');
    if (loader) loader.style.display = 'none';
    if ( themeGrid != null ) {
      themeGrid.style.display = 'flex'; // or 'block'
    }
  };

  const getProducts = (append = false) => {
    if (isLoading || (!hasNextPage && append)) return;
    isLoading = true;

    const afterCursor = paginationStack[paginationStack.length - 1];

    showLoader();

    fetch(ajaxurl + '?action=get_elemento_products', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        collectionHandle,
        productHandle: searchTerm,
        paginationParams: {
          first: pageSize,
          afterCursor: afterCursor,
          beforeCursor: '',
          reverse: true
        }
      })
    })
    .then(res => res.json())
    .then(json => {
      const parsed = typeof json === 'string' ? JSON.parse(json) : json;
      
      // Handle WordPress API response format
      const themes = parsed.themes || [];
      const pagination = parsed.pagination || {};

      hasNextPage = pagination.has_next_page || false;
      paginationStack.push(pagination.current_page ? pagination.current_page.toString() : '1');

      renderProducts(themes, append);
      if ( readMoreBtn != null ) {
        if (!hasNextPage) {
          readMoreBtn.style.display = 'none';
        } else {
          readMoreBtn.style.display = 'inline-block';
        }
      }
    })
    .finally(() => {
      hideLoader();
      isLoading = false;
    });
  };

  const renderProducts = async (themes, append = false) => {

    const formatPrice = (num) => {
        if (!num) return "0.00";
        return parseFloat(num).toFixed(2);
    };

    let html = '';
    themes.forEach(theme => {
      // Skip specific themes if needed
      if (theme.slug === 'wp-theme-bundle-copy') {
        return;
      }

      // Get demo link from the theme object
      const demoLink = theme.demo_link || theme.permalink || '#';
      const imageUrl = theme.featured_image?.url || '';

      const showRegularPrice = theme.regular_price && theme.price && (theme.regular_price != theme.price);

      const regularPriceFormatted = formatPrice(theme.regular_price);
      const salePriceFormatted = formatPrice(theme.price);

       html += `
            <div class="col-lg-4 col-md-4">
              <div class="home-themes-box">
                <div class="themes-box-image">
                  <div class="productimage-container" id="productimageContainer">
                    <img id="scrollproductImage" src="${imageUrl}" alt="${theme.title}" style="position: relative;">
                  </div>
                  <div class="themes-box-btn">
                    <a target="_blank" href="${theme.permalink}">
                        <button type="button" class="btn btn-default">Details</button>
                    </a>
                    <a target="_blank" href="${demoLink}">
                        <button type="button" class="btn btn-default">Demo</button>
                    </a>
                  </div>
                </div>

                <h4><a href="${theme.permalink}" target="_blank">${theme.title}</a></h4>
                ${ showRegularPrice ? `
                <del aria-hidden="true">
                    <span class="woocommerce-Price-amount amount">
                        <bdi><span class="woocommerce-Price-currencySymbol">$</span>${regularPriceFormatted}</bdi>
                    </span>
                </del>` : `` }
                <span class="woocommerce-Price-amount amount">
                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>${salePriceFormatted || '0'}</bdi>
                </span>
              </div>
            </div>`;
    });

    if ( themeGrid != null ) {

      if (append) {
        themeGrid.innerHTML += html;
      } else {
  
        if (html == '') {
          html = '<h4 class="card-title">No Result Found</h4>';
        }
  
        themeGrid.innerHTML = html;
      }
    }
    
  };

  const getCollections = () => {
    fetch(ajaxurl + '?action=get_elemento_collections')
      .then(res => res.json())
      .then(json => {
        const items = typeof json === 'string' ? JSON.parse(json) : json;

        items.sort((a, b) => a.name.localeCompare(b.name));

        let html = '<li><input type="radio" name="theme_cat" value="premium" hidden>All</li>';
        items.forEach(item => {
          // Skip free themes category if it exists
          if (item.slug === 'free' || item.slug === 'uncategorized') return;
          html += `<li><input type="radio" name="theme_cat" value="${item.slug}" hidden>${item.name}</li>`;
        });

        if ( filterSelect != null ) {
          filterSelect.innerHTML = html;
        }
      });
  };

  if ( readMoreBtn != null ) {
    // Event: Read More button
    readMoreBtn.addEventListener('click', () => {
      getProducts(true); // append = true
    });
  }

  if ( searchInput != null ) {
    
    // Event: search with debounce
    searchInput.addEventListener('input', () => {
      // Clear existing timeout
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }
      
      // Set new timeout for 2 seconds
      searchTimeout = setTimeout(() => {
        searchTerm = searchInput.value;
        paginationStack = [''];
        hasNextPage = true;
        getProducts(false); // append = false (fresh render)
      }, 300);
    });
  }

  if ( filterSelect != null ) {
    // Click on <li> triggers input selection
    filterSelect.addEventListener('click', (e) => {
      const li = e.target.closest('li');
      if (!li) return;
      const input = li.querySelector('input[type="radio"]');
      if (input) {
        input.checked = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  
    // Event: filter radio change
    filterSelect.addEventListener('change', (e) => {
      if (e.target.name === 'theme_cat') {
        collectionHandle = e.target.value;
        paginationStack = [''];
        hasNextPage = true;
        getProducts(false); // append = false
      }
    });
  }


  // Init
  searchTerm = '';
  getProducts(false);
  getCollections();

});


// new new for popup/floating button
jQuery(document).ready(function ($) {

    $('#sruc-popup-content .sruc-popup-dismiss, #sruc-popup-content .sruc-popup-template-btn').on('click', function () {
        $.ajax({
            url: sruc_obj.ajax_url,
            type: 'POST',
            data: { action: 'sruc_get_notice_dismiss' },
            success: function (response) {
                $('#sruc-popup-overlay').hide();
                // $('.sruc-premium-floating-btn').fadeIn(); 
                $('.sruc-premium-floating-btn')
                    .fadeIn()
                    .attr('style', 'display:inline-block !important;');
            }
        });
    });

});