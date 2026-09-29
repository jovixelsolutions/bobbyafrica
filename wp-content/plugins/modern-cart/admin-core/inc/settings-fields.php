<?php
/**
 * Settings fields configuration.
 *
 * @package modern-cart
 */

namespace ModernCart\Admin_Core\Inc;

use function __;
use ModernCart\Inc\Helper;

/**
 * Settings Fields helper class.
 */
class Settings_Fields {
	/**
	 * Get SVG icon markup for a given settings key.
	 *
	 * @param string $key The icon key (e.g. 'moderncart_setting', 'moderncart_text', etc).
	 * @return string SVG markup or empty string if not found.
	 */
	public static function get_icon_svg( $key = '' ) {
		$icons = apply_filters(
			'moderncart_settings_icons',
			[
				'moderncart_setting'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
				'moderncart_text'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>',
				'cart_settings'         => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
				'moderncart_floating'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>',
				'moderncart_styling'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42"/></svg>',
				'moderncart_extensions' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z"/></svg>',
				'moderncart_license'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>',
				'settings'              => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather inline-block feather-sliders moderncart-icon mr-4"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>',
				'how'                   => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
				'spinner'               => '<svg class="animate-spin ml-2 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>',
				'style'                 => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="var(--ci-primary-color, currentColor)" d="M19.946 3.846C17.849 1.85 15.032.75 12.013.75c-2.814 0-5.619.941-7.695 2.581C2.017 5.149.75 7.695.75 10.5c0 2.817.728 4.635 2.291 5.72 1.313.911 3.207 1.3 6.334 1.3h1.395A1.232 1.232 0 0 1 12 18.75v2.249a1.5 1.5 0 0 0 1.5 1.5h.002l4.254-.004a1.504 1.504 0 0 0 .923-.319c.456-.357 1.628-1.378 2.662-3.136 1.267-2.153 1.909-4.648 1.909-7.415 0-2.987-1.173-5.75-3.304-7.779Zm.102 14.434c-.905 1.537-1.906 2.412-2.294 2.715l-4.254.004V18.75a2.734 2.734 0 0 0-2.73-2.73H9.375c-2.798 0-4.436-.309-5.479-1.032-1.138-.791-1.646-2.175-1.646-4.488 0-5.164 4.964-8.25 9.763-8.25 5.46 0 9.737 4.118 9.737 9.375 0 2.496-.573 4.735-1.702 6.655Z" class="ci-primary"></path><path fill="var(--ci-primary-color, currentColor)" d="M6 6.75a2.625 2.625 0 1 0 2.625 2.625A2.628 2.628 0 0 0 6 6.75Zm0 3.75a1.125 1.125 0 1 1 1.125-1.125A1.126 1.126 0 0 1 6 10.5Zm5.25-7.125A2.625 2.625 0 1 0 13.875 6a2.628 2.628 0 0 0-2.625-2.625Zm0 3.75A1.125 1.125 0 1 1 12.375 6a1.126 1.126 0 0 1-1.125 1.125ZM16.875 6A2.625 2.625 0 1 0 19.5 8.625 2.628 2.628 0 0 0 16.875 6Zm0 3.75A1.125 1.125 0 1 1 18 8.625a1.126 1.126 0 0 1-1.125 1.125Zm.75 2.625A2.625 2.625 0 1 0 20.25 15a2.628 2.628 0 0 0-2.625-2.625Zm0 3.75A1.125 1.125 0 1 1 18.75 15a1.126 1.126 0 0 1-1.125 1.125Z" class="ci-primary"></path></svg>',
				'checked-circle'        => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
			]
		);

		if ( empty( $key ) ) {
			return $icons;
		}

		return $icons[ $key ] ?? '';
	}

	/**
	 * Get the settings tabs for Modern Cart.
	 *
	 * Returns an associative array of settings tabs, where each tab contains:
	 * - name: string The display name of the tab.
	 * - slug: string The unique slug identifier for the tab.
	 * - priority: int The order priority for displaying the tab.
	 *
	 * @return array<string, array{ name: string, slug: string, priority: int }>
	 */
	public static function get_tabs(): array {
		return apply_filters(
			'moderncart_settings_tabs',
			[
				'moderncart_setting'  => [
					'name'     => __( 'General', 'modern-cart' ), // Tab name.
					'title'    => __( 'General', 'modern-cart' ), // Page title.
					'slug'     => 'moderncart_setting',
					'priority' => 10,
				],
				'moderncart_text'     => [
					'name'     => __( 'Text Label', 'modern-cart' ), // Tab name.
					'title'    => __( 'Text Label', 'modern-cart' ),  // Page title.
					'slug'     => 'moderncart_text',
					'priority' => 20,
				],
				'moderncart_floating' => [
					'name'     => __( 'Floating Cart Icon', 'modern-cart' ), // Tab name.
					'title'    => __( 'Floating Cart Icon', 'modern-cart' ),  // Page title.
					'slug'     => 'moderncart_floating',
					'priority' => 30,
				],
				'moderncart_styling'  => [
					'name'     => __( 'Styling', 'modern-cart' ), // Tab name.
					'title'    => __( 'Styling', 'modern-cart' ),  // Page title.
					'slug'     => 'moderncart_styling',
					'priority' => 40,
				],
			]
		);
	}

	/**
	 * Return settings fields definition.
	 *
	 * @return array<string,array<array<string,mixed>>>
	 */
	public static function get_fields() {
		return apply_filters(
			'moderncart_settings_fields',
			[
				'moderncart_setting'  => [
					'moderncart_setting_enable_moderncart' => [
						'type'        => 'dropdown',
						'label'       => __( 'Enable Modern Cart', 'modern-cart' ),
						'description' => __( 'Choose where you want to enable the cart on your website.', 'modern-cart' ),
						'name'        => 'moderncart_setting[enable_moderncart]',
						'options'     => [
							[
								'id'   => 'disabled',
								'name' => __( 'Disable', 'modern-cart' ),
							],
							[
								'id'   => 'wc_pages',
								'name' => __( 'WooCommerce Pages', 'modern-cart' ),
							],
							[
								'id'   => 'all',
								'name' => __( 'Entire Website', 'modern-cart' ),
							],
						],
						'priority'    => 10,
					],
					'moderncart_cart_cart_theme_style'     => [
						'type'        => 'dropdown',
						'label'       => __( 'Cart Style', 'modern-cart' ),
						'description' => Helper::setting_doc_link( __( 'Pick a cart design that best suits your store\'s look and feel.', 'modern-cart' ), 'https://cartflows.com/docs/modern-cart-general-settings/#_3--cart-style' ),
						'name'        => 'moderncart_cart[cart_theme_style]',
						'options'     => [
							[
								'id'   => 'style1',
								'name' => __( 'Style 1', 'modern-cart' ),
							],
							[
								'id'   => 'style2',
								'name' => __( 'Style 2', 'modern-cart' ),
							],
						],
						'priority'    => 20,
					],
					'moderncart_cart_enable_coupon_field'  => [
						'type'        => 'dropdown',
						'label'       => __( 'Display Coupon Field', 'modern-cart' ),
						'description' => Helper::setting_doc_link( __( 'Allow customers to enter a coupon code in the cart to apply discounts.', 'modern-cart' ), 'https://cartflows.com/docs/modern-cart-general-settings/#_8--display-coupon-field' ),
						'name'        => 'moderncart_cart[enable_coupon_field]',
						'options'     => [
							[
								'id'   => 'disabled',
								'name' => __( 'Disable', 'modern-cart' ),
							],
							[
								'id'   => 'minimize',
								'name' => __( 'Minimize', 'modern-cart' ),
							],
							[
								'id'   => 'traditional',
								'name' => __( 'Expand', 'modern-cart' ),
							],
						],
						'priority'    => 30,
					],
					'moderncart_setting_enable_ajax_add_to_cart' => [
						'type'        => 'toggle',
						'label'       => __( 'Enable AJAX Add To Cart', 'modern-cart' ),
						'description' => __( 'Enable or disable AJAX add to cart functionality in single product page. When disabled, the cart page will reload after adding a product.', 'modern-cart' ),
						'name'        => 'moderncart_setting[enable_ajax_add_to_cart]',
						'priority'    => 35,
					],
					'moderncart_setting_enable_free_shipping_bar' => [
						'type'        => 'toggle',
						'label'       => __( 'Enable Free Shipping Bar', 'modern-cart' ),
						'description' => sprintf(
							// translators: %1$s: link html start, %2$s: link html end.
							__( 'Shows a progress bar that lets customers see how much more they need to spend to get free shipping. To enable this, turn on Free Shipping in your WooCommerce shipping settings. Need help? Check out %1$s this guide%2$s.', 'modern-cart' ),
							'<a href="https://cartflows.com/docs/how-to-enable-free-shipping-in-woocommerce/" class="text-wpcolor hover:text-wphovercolor no-underline" target="_blank">',
							'</a>'
						),
						'name'        => 'moderncart_setting[enable_free_shipping_bar]',
						'priority'    => 40,
					],
					'moderncart_cart_enable_usage_tracking' => [
						'type'        => 'toggle',
						'label'       => __( 'Help Us Improve Your Experience', 'modern-cart' ),
						'description' => sprintf(
							/* translators: %1$s: link html start, %2$s: link html end. */
							__( 'Allow Modern Cart and our other products to track non-sensitive usage tracking data. %1$sLearn More%2$s', 'modern-cart' ),
							'<a href="https://my.cartflows.com/usage-tracking/?utm_source=dashboard&utm_medium=modern-cart&utm_campaign=docs" class="text-wpcolor hover:text-wphovercolor no-underline" target="_blank">',
							'</a>'
						),
						'name'        => 'moderncart_cart[enable_usage_tracking]',
						'priority'    => 45,
					],
					'moderncart_setting_delete_data_on_uninstall' => [
						'type'        => 'toggle',
						'label'       => __( 'Delete Plugin Data on Uninstall', 'modern-cart' ),
						'description' => __( 'Enable this to delete all Modern Cart settings and data when the plugin is deleted. Once deleted, your saved data cannot be restored.', 'modern-cart' ),
						'name'        => 'moderncart_setting[delete_data_on_uninstall]',
						'priority'    => 110,
					],
				],
				'moderncart_text'     => [
					'moderncart_cart_main_title'         => [
						'type'        => 'text',
						'label'       => __( 'Main Heading', 'modern-cart' ),
						'description' => __( 'Set the main heading for your cart. This is the most important title and helps customers understand what this section is about.', 'modern-cart' ),
						'name'        => 'moderncart_cart[main_title]',
						'priority'    => 10,
					],
					'moderncart_cart_coupon_title'       => [
						'type'        => 'text',
						'label'       => __( 'Coupon Field Title', 'modern-cart' ),
						'description' => __( "Enter a title for the coupon field to let users know what it's for.", 'modern-cart' ),
						'name'        => 'moderncart_cart[coupon_title]',
						'badge'       => __( 'Default value: Got a Discount Code?', 'modern-cart' ),
						'priority'    => 20,
					],
					'moderncart_cart_coupon_placeholder' => [
						'type'        => 'text',
						'label'       => __( 'Coupon Field Placeholder', 'modern-cart' ),
						'description' => __( 'Enter a placeholder text for the coupon field (e.g., "Enter your coupon code").', 'modern-cart' ),
						'name'        => 'moderncart_cart[coupon_placeholder]',
						'badge'       => __( 'Default value: Enter discount code', 'modern-cart' ),
						'priority'    => 30,
					],
					'moderncart_cart_checkout_button_label' => [
						'type'        => 'text',
						'label'       => __( 'Checkout Button Text', 'modern-cart' ),
						'description' => __( 'Customize the text on your checkout button to match your store\'s style and guide customers clearly.', 'modern-cart' ),
						'name'        => 'moderncart_cart[checkout_button_label]',
						'badge'       => __( 'Default value: Checkout now', 'modern-cart' ),
						'priority'    => 40,
					],
					'moderncart_cart_free_shipping_bar_text' => [
						'type'        => 'text',
						'label'       => __( 'Free Shipping Bar Text', 'modern-cart' ),
						'description' => __( 'Use {amount} to show the remaining amount to spend.', 'modern-cart' ),
						'name'        => 'moderncart_cart[free_shipping_bar_text]',
						'badge'       => __( "Default value: You're {amount} away from free shipping!", 'modern-cart' ),
						'priority'    => 50,
					],
					'moderncart_cart_free_shipping_success_text' => [
						'type'        => 'text',
						'label'       => __( 'Free Shipping Success Message', 'modern-cart' ),
						'description' => __( 'Message to display when customer qualifies for free shipping.', 'modern-cart' ),
						'name'        => 'moderncart_cart[free_shipping_success_text]',
						'badge'       => __( "Default value: Awesome pick! You've unlocked free shipping.", 'modern-cart' ),
						'priority'    => 50,
					],
					'moderncart_cart_on_sale_percentage_text' => [
						'type'        => 'text',
						'label'       => __( 'Discount Label', 'modern-cart' ),
						'description' => __( 'Enter the discount label to display the sale price. Use {percent} to show the discount percent value.', 'modern-cart' ),
						'name'        => 'moderncart_cart[on_sale_percentage_text]',
						'badge'       => __( 'Default value: You saved {percent}%', 'modern-cart' ),
						'priority'    => 60,
					],
				],
				'moderncart_floating' => [
					'moderncart_floating_floating_cart_position' => [
						'type'        => 'dropdown',
						'label'       => __( 'Floating Cart Button Position', 'modern-cart' ),
						'description' => __( 'Select where you want to position floating cart button on your website.', 'modern-cart' ),
						'name'        => 'moderncart_floating[floating_cart_position]',
						'options'     => [
							[
								'id'   => 'bottom-left',
								'name' => __( 'Bottom Left', 'modern-cart' ),
							],
							[
								'id'   => 'bottom-right',
								'name' => __( 'Bottom Right', 'modern-cart' ),
							],
						],
						'conditions'  => [
							'fields' => [
								[
									'name'     => 'moderncart_floating[display_floating_cart_icon]',
									'operator' => '===',
									'value'    => true,
								],
							],
						],
						'priority'    => 10,
					],
				],
				'moderncart_styling'  => [
					'sections' => [
						'colors'        => [
							'label'      => __( 'Colors', 'modern-cart' ),
							'priority'   => 10,
							'field_args' => [
								'moderncart_appearance_primary_color' => [
									'type'        => 'color',
									'label'       => __( 'Primary Color', 'modern-cart' ),
									'description' => __( 'Select the main color for your design.', 'modern-cart' ),
									'name'        => 'moderncart_appearance[primary_color]',
									'default_var' => 'primary_color',
									'priority'    => 10,
								],
								'moderncart_appearance_heading_color' => [
									'type'        => 'color',
									'label'       => __( 'Heading Color', 'modern-cart' ),
									'description' => __( 'Choose a color for the headings in the cart.', 'modern-cart' ),
									'name'        => 'moderncart_appearance[heading_color]',
									'default_var' => 'heading_color',
									'priority'    => 20,
								],
								'moderncart_appearance_body_color' => [
									'type'        => 'color',
									'label'       => __( 'Body Color', 'modern-cart' ),
									'description' => __( 'Choose a color for the text in your cart.', 'modern-cart' ),
									'name'        => 'moderncart_appearance[body_color]',
									'default_var' => 'body_color',
									'priority'    => 30,
								],
							],
						],
						'cart_header'   => [
							'label'      => __( 'Cart Header', 'modern-cart' ),
							'priority'   => 12,
							'field_args' => [
								'moderncart_appearance_cart_header_text_alignment' => [
									'type'        => 'dropdown',
									'label'       => __( 'Cart Header Text Alignment', 'modern-cart' ),
									'description' => __( 'Choose how the text in your cart header is aligned.', 'modern-cart' ),
									'name'        => 'moderncart_appearance[cart_header_text_alignment]',
									'options'     => [
										[
											'id'   => 'left',
											'name' => __( 'Left', 'modern-cart' ),
										],
										[
											'id'   => 'center',
											'name' => __( 'Center', 'modern-cart' ),
										],
										[
											'id'   => 'right',
											'name' => __( 'Right', 'modern-cart' ),
										],
									],
									'priority'    => 10,
								],
								'moderncart_appearance_cart_header_font_size' => [
									'type'        => 'number',
									'label'       => __( 'Cart Header Font Size', 'modern-cart' ),
									'description' => __( 'Set the font size for the cart header (in pixels).', 'modern-cart' ),
									'name'        => 'moderncart_appearance[cart_header_font_size]',
									'badge'       => __( 'Default size: 22px', 'modern-cart' ),
									'type_attr'   => 'px',
									'priority'    => 20,
								],
							],
						],
						'miscellaneous' => [
							'label'      => __( 'Miscellaneous', 'modern-cart' ),
							'priority'   => 80,
							'field_args' => [
								'moderncart_cart_product_image_size' => [
									'type'        => 'dropdown',
									'label'       => __( 'Product Image Size', 'modern-cart' ),
									'description' => __( 'Choose the size of product images displayed in the cart.', 'modern-cart' ),
									'name'        => 'moderncart_cart[product_image_size]',
									'options'     => [
										[
											'id'   => 'small',
											'name' => __( 'Small', 'modern-cart' ),
										],
										[
											'id'   => 'medium',
											'name' => __( 'Medium', 'modern-cart' ),
										],
										[
											'id'   => 'large',
											'name' => __( 'Large', 'modern-cart' ),
										],
									],
									'priority'    => 10,
								],
								'moderncart_cart_cart_item_padding' => [
									'type'        => 'number',
									'label'       => __( 'Cart Item Padding', 'modern-cart' ),
									'description' => __( 'Set the padding around each item in the cart (in pixels).', 'modern-cart' ),
									'name'        => 'moderncart_cart[cart_item_padding]',
									'badge'       => __( 'Default padding: 20px', 'modern-cart' ),
									'type_attr'   => 'px',
									'conditions'  => [
										'fields' => [
											[
												'name'     => 'moderncart_cart[cart_type]',
												'operator' => '===',
												'value'    => 'slideout',
											],
										],
									],
									'priority'    => 20,
								],
								'moderncart_cart_animation_speed' => [
									'type'        => 'number',
									'label'       => __( 'Animation Speed', 'modern-cart' ),
									'description' => __( 'Choose how fast or slow the animation plays.', 'modern-cart' ),
									'name'        => 'moderncart_cart[animation_speed]',
									'type_attr'   => 'ms',
									'badge'       => __( 'Default speed: 300', 'modern-cart' ),
									'priority'    => 30,
								],
							],
						],
					],
				],
			]
		);
	}
}
