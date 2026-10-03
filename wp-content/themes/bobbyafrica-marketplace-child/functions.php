<?php
/**
 * Jovixels Marketplace Child Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/enqueue.php';
require_once __DIR__ . '/inc/template-functions.php';
require_once __DIR__ . '/inc/woocommerce.php';

add_action( 'login_enqueue_scripts', 'bobbyafrica_login_branding_assets' );

function bobbyafrica_login_branding_assets() {
	wp_enqueue_style(
		'bobbyafrica-login-font',
		'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'bobbyafrica-login',
		get_stylesheet_directory_uri() . '/assets/css/login.css',
		array( 'bobbyafrica-login-font' ),
		filemtime( get_stylesheet_directory() . '/assets/css/login.css' )
	);

	$logo_url = bobbyafrica_get_login_logo_url();
	$custom_css = sprintf(
		':root { --bobbyafrica-login-logo: url("%s"); }',
		esc_url( $logo_url )
	);
	wp_add_inline_style( 'bobbyafrica-login', $custom_css );
}

function bobbyafrica_get_login_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );
		if ( ! empty( $logo[0] ) ) {
			return $logo[0];
		}
	}

	$logo_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="540" height="180" viewBox="0 0 540 180" role="img" aria-label="BOBBY AFRICA"><rect width="540" height="180" rx="30" fill="#d85f18"/><rect x="24" y="24" width="132" height="132" rx="30" fill="rgba(255,255,255,0.18)"/><path d="M58 92c0-18 14-32 32-32s32 14 32 32-14 32-32 32-32-14-32-32zm39 18h-18v-36h18v36zm49-18h17v18h-17v-18zm-69 52h-6v-12h32v12h-26v-3h26v-12h-32v-18h32v-12h-32v-18h32v-12h-6v-18h-8v18h-32v12h32v18h-32v12h32v18h-32v12h32v18h-8v-18h6v18h18v-18h-18v-12zm111-43h-18v54h18v-54zm-32 0h-18v54h18v-54zm26-18h18v72h-18v-72zm96-22c7 0 12 5 12 12v64c0 7-5 12-12 12h-54c-7 0-12-5-12-12V80c0-7 5-12 12-12h54zm-22 27h-16v47h16V89zm53 76h-36v-54h18v36h18v18zm-18-72c-30 0-54 23-54 52 0 29 24 52 54 52s54-23 54-52c0-29-24-52-54-52zm0 18c19 0 36 14 36 34s-17 34-36 34-36-14-36-34 17-34 36-34zm88-18h16v72h-16v-72zm20 0h16v72h-16v-72zm-76 72h-16v-72h16v72zm67-54h16v54h-16v-54zm-15 54h-42v-18h42v18z" fill="#fff" opacity="0.95"/><text x="185" y="105" fill="#ffffff" font-size="42" font-weight="700" font-family="Quicksand, Arial, sans-serif" letter-spacing="3">BOBBY AFRICA</text></svg>';

	return 'data:image/svg+xml;base64,' . base64_encode( $logo_svg );
}

add_filter( 'login_headerurl', 'bobbyafrica_login_logo_url_filter' );
function bobbyafrica_login_logo_url_filter() {
	return home_url( '/' );
}

add_filter( 'login_headertext', 'bobbyafrica_login_logo_text_filter' );
function bobbyafrica_login_logo_text_filter() {
	return get_bloginfo( 'name' );
}
