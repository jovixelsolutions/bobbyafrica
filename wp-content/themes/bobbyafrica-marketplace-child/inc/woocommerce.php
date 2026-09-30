<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

add_filter( 'loop_shop_per_page', function() {
	return 12;
}, 20 );

add_action( 'woocommerce_before_main_content', 'bobbyafrica_marketplace_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'bobbyafrica_marketplace_wrapper_end', 10 );

function bobbyafrica_marketplace_wrapper_start() {
	if ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page() ) {
		echo '<main class="marketplace-page"><div class="container marketplace-shell">';
	}
}

function bobbyafrica_marketplace_wrapper_end() {
	if ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page() ) {
		echo '</div></main>';
	}
}

add_action( 'woocommerce_product_query', 'bobbyafrica_filter_sale_catalog' );

function bobbyafrica_filter_sale_catalog( $query ) {
	if ( is_admin() ) {
		return;
	}

	$product_ids = array();
	if ( isset( $_GET['market_sale'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['market_sale'] ) ) ) {
		$product_ids = wc_get_product_ids_on_sale();
	}
	if ( isset( $_GET['market_featured'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['market_featured'] ) ) ) {
		$featured_ids = wc_get_featured_product_ids();
		$product_ids  = $product_ids ? array_values( array_intersect( $product_ids, $featured_ids ) ) : $featured_ids;
	}
	if ( isset( $_GET['market_sale'] ) || isset( $_GET['market_featured'] ) ) {
		$existing_ids = $query->get( 'post__in' );
		if ( $existing_ids ) {
			$product_ids = array_values( array_intersect( $product_ids, $existing_ids ) );
		}
		$query->set( 'post__in', $product_ids ? $product_ids : array( 0 ) );
	}
	if ( isset( $_GET['market_new'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['market_new'] ) ) ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	$meta_query = (array) $query->get( 'meta_query' );
	foreach ( array( 'min_price' => '>=', 'max_price' => '<=' ) as $parameter => $operator ) {
		if ( isset( $_GET[ $parameter ] ) && '' !== $_GET[ $parameter ] ) {
			$price = wc_format_decimal( wp_unslash( $_GET[ $parameter ] ) );
			if ( '' !== $price ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => $price,
					'compare' => $operator,
					'type'    => 'DECIMAL',
				);
			}
		}
	}
	if ( isset( $_GET['rating_filter'] ) && '' !== $_GET['rating_filter'] ) {
		$rating = min( 5, max( 1, absint( wp_unslash( $_GET['rating_filter'] ) ) ) );
		$meta_query[] = array(
			'key'     => '_wc_average_rating',
			'value'   => $rating,
			'compare' => '>=',
			'type'    => 'DECIMAL',
		);
	}
	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}

	$tax_query = (array) $query->get( 'tax_query' );
	foreach ( bobbyafrica_marketplace_filter_taxonomies() as $filter_key => $taxonomy ) {
		$parameter = 'filter_' . $filter_key;
		if ( empty( $_GET[ $parameter ] ) ) {
			continue;
		}
		$term_slug = sanitize_title( wp_unslash( $_GET[ $parameter ] ) );
		if ( $term_slug ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array( $term_slug ),
			);
		}
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
}

function bobbyafrica_marketplace_filter_taxonomies() {
	$taxonomies = array();
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		if ( taxonomy_exists( $taxonomy ) ) {
			$taxonomies[ $attribute->attribute_name ] = $taxonomy;
		}
	}
	foreach ( array( 'product_brand' => 'brand', 'pa_brand' => 'brand', 'pa_warranty' => 'warranty', 'pa_gender' => 'gender', 'pa_size' => 'size' ) as $taxonomy => $filter_key ) {
		if ( taxonomy_exists( $taxonomy ) && ! isset( $taxonomies[ $filter_key ] ) ) {
			$taxonomies[ $filter_key ] = $taxonomy;
		}
	}
	return $taxonomies;
}

function bobbyafrica_marketplace_catalog_filters() {
	$shop_url         = wc_get_page_permalink( 'shop' );
	$categories       = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'name' ) );
	$current_category = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';
	if ( ! $current_category && is_product_category() ) {
		$current_term    = get_queried_object();
		$current_category = isset( $current_term->slug ) ? $current_term->slug : '';
	}
	$current_rating   = isset( $_GET['rating_filter'] ) ? absint( wp_unslash( $_GET['rating_filter'] ) ) : 0;
	?>
	<form class="marketplace-filter-form" method="get" action="<?php echo esc_url( $shop_url ); ?>">
		<h2><?php esc_html_e( 'Filter products', 'bobbyafrica-marketplace-child' ); ?></h2>
		<?php foreach ( array( 'market_sale', 'market_featured', 'market_new' ) as $parameter ) : ?>
			<?php if ( isset( $_GET[ $parameter ] ) ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $parameter ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET[ $parameter ] ) ) ); ?>" />
			<?php endif; ?>
		<?php endforeach; ?>
		<label for="marketplace-filter-category"><?php esc_html_e( 'Category', 'bobbyafrica-marketplace-child' ); ?></label>
		<select id="marketplace-filter-category" name="product_cat">
			<option value=""><?php esc_html_e( 'All categories', 'bobbyafrica-marketplace-child' ); ?></option>
			<?php if ( ! is_wp_error( $categories ) ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php $depth = count( get_ancestors( $category->term_id, 'product_cat' ) ); ?>
					<option value="<?php echo esc_attr( $category->slug ); ?>"<?php selected( $current_category, $category->slug ); ?>><?php echo esc_html( str_repeat( '- ', $depth ) . $category->name ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<fieldset>
			<legend><?php esc_html_e( 'Price range', 'bobbyafrica-marketplace-child' ); ?></legend>
			<div class="marketplace-price-fields">
				<label><span><?php esc_html_e( 'Min', 'bobbyafrica-marketplace-child' ); ?></span><input type="number" name="min_price" min="0" step="any" value="<?php echo isset( $_GET['min_price'] ) ? esc_attr( wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) ) : ''; ?>" /></label>
				<label><span><?php esc_html_e( 'Max', 'bobbyafrica-marketplace-child' ); ?></span><input type="number" name="max_price" min="0" step="any" value="<?php echo isset( $_GET['max_price'] ) ? esc_attr( wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) ) : ''; ?>" /></label>
			</div>
		</fieldset>
		<label for="marketplace-filter-rating"><?php esc_html_e( 'Minimum rating', 'bobbyafrica-marketplace-child' ); ?></label>
		<select id="marketplace-filter-rating" name="rating_filter">
			<option value=""><?php esc_html_e( 'Any rating', 'bobbyafrica-marketplace-child' ); ?></option>
			<?php for ( $rating = 5; $rating >= 1; $rating-- ) : ?>
				<option value="<?php echo esc_attr( $rating ); ?>"<?php selected( $current_rating, $rating ); ?>><?php echo esc_html( sprintf( _n( '%s star and up', '%s stars and up', $rating, 'bobbyafrica-marketplace-child' ), number_format_i18n( $rating ) ) ); ?></option>
			<?php endfor; ?>
		</select>
		<?php foreach ( bobbyafrica_marketplace_filter_taxonomies() as $filter_key => $taxonomy ) : ?>
			<?php $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'orderby' => 'name' ) ); ?>
			<?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
				<label for="marketplace-filter-<?php echo esc_attr( $filter_key ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $filter_key ) ) ); ?></label>
				<select id="marketplace-filter-<?php echo esc_attr( $filter_key ); ?>" name="filter_<?php echo esc_attr( $filter_key ); ?>">
					<option value=""><?php echo esc_html( sprintf( __( 'Any %s', 'bobbyafrica-marketplace-child' ), strtolower( ucwords( str_replace( '_', ' ', $filter_key ) ) ) ) ); ?></option>
					<?php foreach ( $terms as $term ) : ?>
						<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( isset( $_GET[ 'filter_' . $filter_key ] ) ? sanitize_title( wp_unslash( $_GET[ 'filter_' . $filter_key ] ) ) : '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		<?php endforeach; ?>
		<button class="marketplace-button primary" type="submit"><?php esc_html_e( 'Apply filters', 'bobbyafrica-marketplace-child' ); ?></button>
		<a class="marketplace-filter-reset" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Clear filters', 'bobbyafrica-marketplace-child' ); ?></a>
	</form>
	<?php
}

add_action( 'wp_ajax_bobbyafrica_category_products', 'bobbyafrica_category_products_ajax' );
add_action( 'wp_ajax_nopriv_bobbyafrica_category_products', 'bobbyafrica_category_products_ajax' );

function bobbyafrica_category_products_ajax() {
	check_ajax_referer( 'bobbyafrica_marketplace', 'nonce' );

	$category_id = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
	$category    = get_term( $category_id, 'product_cat' );

	if ( ! $category_id || is_wp_error( $category ) || ! $category ) {
		wp_send_json_error( array( 'message' => __( 'Category not found.', 'bobbyafrica-marketplace-child' ) ), 404 );
	}

	$transient_key = 'bobbyafrica_mega_products_' . $category_id;
	$products_html = get_transient( $transient_key );

	if ( false === $products_html ) {
		$products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 5,
				'category' => array( $category->slug ),
				'orderby'  => 'popularity',
				'order'    => 'DESC',
			)
		);

		ob_start();
		if ( $products ) :
			?>
			<ul class="marketplace-mega-product-links">
				<?php foreach ( $products as $product ) : ?>
					<li>
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
							<span><?php echo esc_html( $product->get_name() ); ?></span>
							<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
		else :
			?>
			<p><?php esc_html_e( 'No products are currently listed in this category.', 'bobbyafrica-marketplace-child' ); ?></p>
			<?php
		endif;
		$products_html = ob_get_clean();
		set_transient( $transient_key, $products_html, 5 * MINUTE_IN_SECONDS );
	}

	wp_send_json_success( array( 'html' => $products_html ) );
}

add_action( 'woocommerce_single_product_summary', 'bobbyafrica_product_purchase_notes', 29 );

function bobbyafrica_product_purchase_notes() {
	global $product;
	if ( ! $product ) {
		return;
	}
	?>
	<ul class="marketplace-product-notes" aria-label="<?php esc_attr_e( 'Purchase information', 'bobbyafrica-marketplace-child' ); ?>">
		<?php if ( $product->needs_shipping() ) : ?>
			<li><?php esc_html_e( 'Shipping options and rates are shown at checkout', 'bobbyafrica-marketplace-child' ); ?></li>
		<?php endif; ?>
		<li><?php esc_html_e( 'Payment methods are available at checkout', 'bobbyafrica-marketplace-child' ); ?></li>
		<li><?php esc_html_e( 'Product details and customer reviews are below', 'bobbyafrica-marketplace-child' ); ?></li>
	</ul>
	<?php
}

add_action( 'wp_footer', 'bobbyafrica_product_mobile_buy_bar', 30 );

function bobbyafrica_product_mobile_buy_bar() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}
	?>
	<div class="marketplace-mobile-buy-bar<?php echo $product->is_type( 'variable' ) ? ' is-variable' : ''; ?>" data-marketplace-product="<?php echo esc_attr( $product->get_id() ); ?>">
		<div class="marketplace-mobile-buy-price" aria-live="polite"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<button class="marketplace-mobile-buy-button" type="button" aria-controls="product-<?php echo esc_attr( $product->get_id() ); ?>">
			<?php echo $product->is_type( 'variable' ) ? esc_html__( 'Choose options', 'bobbyafrica-marketplace-child' ) : esc_html__( 'Add to cart', 'bobbyafrica-marketplace-child' ); ?>
		</button>
	</div>
	<?php
}

add_action( 'wp_footer', 'bobbyafrica_marketplace_cart_drawer' );

function bobbyafrica_marketplace_cart_drawer() {
	if ( is_cart() || is_checkout() ) {
		return;
	}
	?>
	<div class="marketplace-mini-cart" aria-live="polite">
		<?php echo wp_kses_post( woocommerce_mini_cart() ); ?>
	</div>
	<?php
}

add_action( 'woocommerce_before_shop_loop_item_title', 'bobbyafrica_product_loop_badges', 10 );

function bobbyafrica_product_loop_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}
	if ( $product->is_on_sale() ) {
		echo '<span class="product-badge secondary">' . esc_html__( 'Featured', 'bobbyafrica-marketplace-child' ) . '</span>';
	}
}
