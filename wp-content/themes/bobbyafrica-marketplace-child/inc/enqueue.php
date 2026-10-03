<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'bobbyafrica_child_enqueue_assets', 20 );

function bobbyafrica_child_enqueue_assets() {
	wp_enqueue_style(
	'bobbyafrica-quicksand',
	'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
	array(),
	null
	);

	wp_enqueue_style(
		'bobbyafrica-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css', 'bobbyafrica-quicksand' ),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);

	wp_enqueue_style(
		'bobbyafrica-child-main',
		get_stylesheet_directory_uri() . '/assets/css/main.css',
		array( 'bobbyafrica-child-style' ),
	filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
	);

	wp_enqueue_style(
		'bobbyafrica-child-responsive',
		get_stylesheet_directory_uri() . '/assets/css/responsive.css',
		array( 'bobbyafrica-child-main' ),
		filemtime( get_stylesheet_directory() . '/assets/css/responsive.css' )
	);

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style(
			'bobbyafrica-single-product',
			get_stylesheet_directory_uri() . '/assets/css/single-product.css',
			array( 'bobbyafrica-child-responsive' ),
			filemtime( get_stylesheet_directory() . '/assets/css/single-product.css' )
		);

		wp_enqueue_script(
			'bobbyafrica-single-product',
			get_stylesheet_directory_uri() . '/assets/js/single-product.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			filemtime( get_stylesheet_directory() . '/assets/js/single-product.js' ),
			true
		);
	}

	wp_enqueue_script(
		'bobbyafrica-child-main',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array( 'jquery' ),
		filemtime( get_stylesheet_directory() . '/assets/js/main.js' ),
		true
	);

		if ( function_exists( 'WC' ) && is_front_page() ) {
			wp_enqueue_script( 'wc-add-to-cart' );
			wp_enqueue_script( 'wc-cart-fragments' );
		}

	wp_localize_script(
		'bobbyafrica-child-main',
		'bobbyafricaMarketData',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'bobbyafrica_marketplace' ),
		)
	);
}
