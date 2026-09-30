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

	wp_enqueue_script(
		'bobbyafrica-child-main',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array( 'jquery' ),
		filemtime( get_stylesheet_directory() . '/assets/js/main.js' ),
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
