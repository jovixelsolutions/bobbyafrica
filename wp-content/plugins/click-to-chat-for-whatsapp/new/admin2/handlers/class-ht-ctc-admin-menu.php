<?php
/**
 * Admin Menu Class
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Menu' ) ) {

	/**
	 * Admin Menu Class
	 */
	class HT_CTC_Admin_Menu {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menus' ) );
		}

		/**
		 * Add admin menus.
		 */
		public function add_menus() {

			$icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iI2ZmZiIgY2xhc3M9ImJpIGJpLXdoYXRzYXBwIiB2aWV3Qm94PSIwIDAgMTYgMTYiPjxwYXRoIGQ9Ik0xMy42MDEgMi4zMjZBNy44NTQgNy44NTQgMCAwIDAgNy45OTQgMEMzLjYyNyAwIC4wNjggMy41NTguMDY0IDcuOTI2YzAgMS4zOTkuMzY2IDIuNzYgMS4wNTcgMy45NjVMMCAxNmw0LjIwNC0xLjEwMmE3LjkzMyA3LjkzMyAwIDAgMCAzLjc5Ljk2NWguMDA0YzQuMzY4IDAgNy45MjYtMy41NTggNy45My03LjkzQTcuODk4IDcuODk4IDAgMCAwIDEzLjYgMi4zMjZ6TTcuOTk0IDE0LjUyMWE2LjU3MyA2LjU3MyAwIDAgMS0zLjM1Ni0uOTJsLS4yNC0uMTQ0LTIuNDk0LjY1NC42NjYtMi40MzMtLjE1Ni0uMjUxYTYuNTYgNi41NiAwIDAgMS0xLjAwNy0zLjUwNWMwLTMuNjI2IDIuOTU3LTYuNTg0IDYuNTkxLTYuNTg0YTYuNTYgNi41NiAwIDAgMSA0LjY2IDEuOTMxIDYuNTU3IDYuNTU3IDAgMCAxIDEuOTI4IDQuNjZjLS4wMDQgMy42MzktMi45NjEgNi41OTItNi41OTIgNi41OTJ6bTMuNjE1LTQuOTM0Yy0uMTk3LS4wOTktMS4xNy0uNTc4LTEuMzUzLS42NDYtLjE4Mi0uMDY1LS4zMTUtLjA5OS0uNDQ1LjA5OS0uMTMzLjE5Ny0uNTEzLjY0Ni0uNjI3Ljc3NS0uMTE0LjEzMy0uMjMyLjE0OC0uNDMuMDUtLjE5Ny0uMS0uODM2LS4zMDgtMS41OTItLjk4NS0uNTktLjUyNS0uOTg1LTEuMTc1LTEuMTAzLTEuMzcyLS4xMTQtLjE5OC0uMDExLS4zMDQuMDg4LS40MDMuMDg3LS4wODguMTk3LS4yMzIuMjk2LS4zNDYuMS0uMTE0LjEzMy0uMTk4LjE5OC0uMzMuMDY1LS4xMzQuMDM0LS4yNDgtLjAxNS0uMzQ3LS4wNS0uMDk5LS40NDUtMS4wNzYtLjYxMi0xLjQ3LS4xNi0uMzg5LS4zMjMtLjMzNS0uNDQ1LS4zNC0uMTE0LS4wMDctLjI0Ny0uMDA3LS4zOC0uMDA3YS43MjkuNzI5IDAgMCAwLS41MjkuMjQ3Yy0uMTgyLjE5OC0uNjkxLjY3Ny0uNjkxIDEuNjU0IDAgLjk3Ny43MSAxLjkxNi44MSAyLjA0OS4wOTguMTMzIDEuMzk0IDIuMTMyIDMuMzgzIDIuOTkyLjQ3LjIwNS44NC4zMjYgMS4xMjkuNDE4LjQ3NS4xNTIuOTA0LjEyOSAxLjI0Ni4wOC4zOC0uMDU4IDEuMTcxLS40OCAxLjMzOC0uOTQzLjE2NC0uNDY0LjE2NC0uODYuMTE0LS45NDMtLjA0OS0uMDg0LS4xODItLjEzMy0uMzgtLjIzMnoiLz48L3N2Zz4=';

			add_menu_page(
				'Click to Chat ',
				'Click to Chat',
				'manage_options',
				'click-to-chat',
				array( $this, 'main_settings_page' ),
				$icon
			);

			// // todo: at first itself displaying this might not be a good idea?.
			// // Upgrade link.
			// if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
			// add_submenu_page(
			// 'click-to-chat',
			// 'Upgrade to PRO',
			// '<span class="dashicons dashicons-star-filled" aria-hidden="true" style="color: #fbbf24; font-size: 16px; width: 16px; height: 16px; margin-right: 4px; vertical-align: text-bottom;"></span><span style="font-weight: 600;">Upgrade to PRO</span>',
			// 'manage_options',
			// esc_url( HT_CTC_Utils::pro_url( 'menu' ) )
			// );
			// }
		}

		/**
		 * Main settings page
		 */
		public function main_settings_page() {
			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-settings-page.php' );
			HT_CTC_Admin_Settings_Page::display();
		}
	}

}
