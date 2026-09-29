<?php
add_action( 'wp_ajax_get_elemento_collections', 'sruc_handle_get_elemento_collections' );
add_action( 'wp_ajax_nopriv_get_elemento_collections', 'sruc_handle_get_elemento_collections' );

function sruc_handle_get_elemento_collections() {
    $response = wp_remote_get( SRUC_ELEMENTO_API_MAIN . '/wp-json/premium-themes/v1/categories', [
        'headers' => [ 'Content-Type' => 'application/json' ], // send empty body if not needed
    ] );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => 'API request failed' ] );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    wp_send_json( $data );
}

add_action( 'wp_ajax_get_elemento_products', 'sruc_handle_get_elemento_products' );
add_action( 'wp_ajax_nopriv_get_elemento_products', 'sruc_handle_get_elemento_products' );

function sruc_handle_get_elemento_products() {
    $body = json_decode( file_get_contents( 'php://input' ), true );
    
    // Extract parameters from the request
    $collection_handle = isset($body['collectionHandle']) ? $body['collectionHandle'] : '';
    $search_term = isset($body['productHandle']) ? $body['productHandle'] : '';
    $pagination = isset($body['paginationParams']) ? $body['paginationParams'] : array();
    
    // Map pagination parameters
    $per_page = isset($pagination['first']) ? $pagination['first'] : 12;
    $page = 1;
    
    // Calculate page from afterCursor (simple implementation)
    if (!empty($pagination['afterCursor'])) {
        $page = intval($pagination['afterCursor']) + 1;
    }
    
    // Prepare API request body
    $api_body = array(
        'per_page' => $per_page,
        'page' => $page
    );
    
    if (!empty($search_term)) {
        $api_body['search'] = $search_term;
    }
    
    if (!empty($collection_handle)) {
        $api_body['category'] = $collection_handle;
    }
    
    // Call the Premium Themes API
    $response = wp_remote_post( SRUC_ELEMENTO_API_MAIN . '/wp-json/premium-themes/v1/themes', [
        'headers' => [ 'Content-Type' => 'application/json' ],
        'body'    => json_encode( $api_body ),
    ] );
    
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => 'API request failed' ] );
    }
    
    $api_data = json_decode( wp_remote_retrieve_body( $response ), true );
    
    // Return WordPress API response format directly
    wp_send_json( $api_data );
}

function sruc_render_themes_page() {
    
    delete_option( 'sruc_show_activation_popup' );
    ?>
    <div class='container-fluid theme-my-4'>
        <div class='row g-4 sruc-import-box-inner-group'>
        
            <!-- Content Area -->
            <div class='col-lg-9'>
                <!-- Search bar with icon -->
                <div class="sruc-upsell-banner">
                    <div class="sruc-notice-notice-main-img sruc-upsell-banner-image">
                        <img src="<?php echo esc_url(SRUC_THEME_BUNDLE_IMAGE_URL); ?>" alt="">
                    </div>
                    <div class="sruc-notice-banner-wrap sruc-upsell-banner-container">
                        <div class="sruc-notice-left-img sruc-upsell-banner-content">
                            <h1><?php echo esc_html('WordPress Theme Bundle – 25+ Elementor Themes'); ?></h1>
                            <p><?php echo esc_html('Transform your WordPress website with 25+ premium themes designed for Elementor. Easy to install and customize, these themes offer flexibility, responsiveness, and sleek designs that will make your website shine. Get started today with this one-time bundle offer!'); ?></p>
                        </div>

                        <div class="sruc-notice-btn sruc-upsell-banner-btn">
                            <a class="sruc-buy-btn" target="_blank"
                                href="<?php echo esc_url(SRUC_ELEMENTO_API_MAIN . 'products/wordpress-theme-bundle/'); ?>"><span class="dashicons dashicons-art"></span><?php echo esc_html('Get All Themes'); ?></a>
                        </div>
                    </div>
                </div>
                <div id='theme-loader' class='text-center py-5' style='display: none;'>
                    <div class='spinner-border text-primary' role='status'>
                        <span class='visually-hidden'>Loading...</span>
                    </div>
                </div>
                <!-- Themes Grid -->
                <div id='theme-grid' class='row g-4 all-theme-box-grid'></div>
                <div id="theme-readmore-container" class="text-center mt-4">
                    <button id="theme-readmore" class="btn btn-outline-secondary rounded-pill">Load More</button>
                </div>
                <!-- Pagination -->
                <nav class='mt-5'>
                    <div id='theme-pagination' class='d-flex justify-content-center mt-4 gap-2'></div>
                </nav>
            </div>

            <!-- Sidebar -->
            <div class='col-lg-3 sidebar-box'>
                <div class='mb-4 position-relative widget'>
                    <input type='text' id ='theme-search' class='form-control rounded-pill px-4 py-2 sruc-theme-searchbar ps-5' placeholder='Search Templates Here...'>
                    <span class="dashicons dashicons-search iconnnn position-absolute top-50 translate-middle-y" style="left: 45px !important;"></span>
                </div>
                <aside class='bg-white p-4 rounded shadow-sm theme-sidebar widget'>
                    <button class='btn btn-light w-100 mb-3 text-start fw-semibold search-by-cat-box'>Search By Categories</button>
                    <ul id='theme-filter' class='theme-radio-list list-unstyled small text-muted mb-4'></ul>
                    <!-- New div -->
                </aside>
            </div>
        </div>
    </div>
    <?php
}

