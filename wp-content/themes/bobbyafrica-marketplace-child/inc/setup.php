<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'bobbyafrica_child_setup' );

function bobbyafrica_child_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		array(
			'primary'   => __( 'Primary Menu', 'bobbyafrica-marketplace-child' ),
			'footer'    => __( 'Footer Menu', 'bobbyafrica-marketplace-child' ),
			'mobile'    => __( 'Mobile Menu', 'bobbyafrica-marketplace-child' ),
			'categories' => __( 'Category Menu', 'bobbyafrica-marketplace-child' ),
		)
	);

	add_image_size( 'marketplace-card', 600, 600, true );
	add_image_size( 'marketplace-hero', 1200, 620, true );
}

add_action( 'widgets_init', 'bobbyafrica_child_widgets_init' );

function bobbyafrica_child_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Marketplace Sidebar', 'bobbyafrica-marketplace-child' ),
			'id'            => 'marketplace-sidebar',
			'before_widget' => '<div class="widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
