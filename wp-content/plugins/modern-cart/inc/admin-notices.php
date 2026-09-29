<?php
/**
 * Modern Cart Admin Notices.
 *
 * @package modern-cart
 * @since 1.0.9
 */

namespace ModernCart\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Notices.
 *
 * @since 1.0.9
 */
class Admin_Notices {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object|null Class object.
	 * @since 1.0.9
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.9
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.9
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'notice_styles' ) );
		add_action( 'admin_head', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Enqueue notice styles on allowed screens.
	 *
	 * @since 1.0.9
	 * @return void
	 */
	public function notice_styles() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style( 'moderncart-admin-notices', MODERNCART_URL . 'assets/css/admin-notices.css', array(), MODERNCART_VER );
	}

	/**
	 * Show admin notices.
	 *
	 * @since 1.0.9
	 * @return void
	 */
	public function show_admin_notices() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$image_path = esc_url( MODERNCART_URL . 'admin-core/assets/images/logo.svg' );

		\Astra_Notices::add_notice(
			array(
				'id'                   => 'moderncart-5-star-notice',
				'type'                 => 'info',
				'class'                => 'moderncart-5-star',
				'show_if'              => true,
				/* translators: %1$s logo image, %2$s heading text, %3$s description text, %4$s review link, %5$s review button text, %6$s repeat notice after, %7$s maybe later text, %8$s already did text */
				'message'              => sprintf(
					'<div class="notice-image" style="display: flex;">
                        <img src="%1$s" class="custom-logo" alt="Modern Cart Icon" itemprop="logo" style="max-width: 90px; border-radius: 50px;"></div>
                        <div class="notice-content">
                            <div class="notice-heading">
                                %2$s
                            </div>
                            <div class="notice-description">
								%3$s
							</div>
                            <div class="astra-review-notice-container">
                                <a href="%4$s" class="astra-notice-close astra-review-notice button-primary" target="_blank">
									<span class="dashicons dashicons-yes"></span>
                                	%5$s
                                </a>

								<a href="#" data-repeat-notice-after="%6$s" class="astra-notice-close astra-review-notice">
									<span class="dashicons dashicons-calendar"></span>
                                	%7$s
                                </a>

                                <a href="#" class="astra-notice-close astra-review-notice">
								    <span class="dashicons dashicons-smiley"></span>
                                	<u>%8$s</u>
                                </a>
                            </div>
                        </div>',
					$image_path,
					__( 'Modern Cart is helping your customers enjoy a smoother checkout experience!', 'modern-cart' ),
					__( 'Love the difference it makes? A 5-star review would mean the world to us and help fellow WooCommerce store owners find us!', 'modern-cart' ),
					'https://wordpress.org/support/plugin/modern-cart/reviews/?filter=5#new-post',
					__( 'Ok, you deserve it', 'modern-cart' ),
					MONTH_IN_SECONDS,
					__( 'Nope, maybe later', 'modern-cart' ),
					__( 'I already did', 'modern-cart' )
				),
				'repeat-notice-after'  => MONTH_IN_SECONDS,
				'display-notice-after' => ( 2 * WEEK_IN_SECONDS ),
			)
		);
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.0.9
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'dashboard',
			'plugins',
		);

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}
}
