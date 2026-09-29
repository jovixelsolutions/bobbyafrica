<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'bobbyafrica_child_enqueue_assets', 20 );

function bobbyafrica_child_enqueue_assets() {
	wp_enqueue_style(
		'bobbyafrica-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'bobbyafrica-child-main',
		get_stylesheet_directory_uri() . '/assets/css/main.css',
		array( 'bobbyafrica-child-style' ),
		'1.0.0'
	);

	wp_enqueue_style(
		'bobbyafrica-child-responsive',
		get_stylesheet_directory_uri() . '/assets/css/responsive.css',
		array( 'bobbyafrica-child-main' ),
		'1.0.0'
	);

	wp_enqueue_script(
		'bobbyafrica-child-main',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	wp_localize_script(
		'bobbyafrica-child-main',
		'bobbyafricaMarketData',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'bobbyafrica_marketplace' ),
		)
	);
}
