<?php
/**
 * Cart Abandonment DB Updater.
 *
 * Handles schema upgrades for existing installs without altering the
 * CREATE TABLE logic. New columns are added through an explicit,
 * consented migration (admin notice + background AJAX) rather than a
 * silent dbDelta ALTER on page load.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 * @since   2.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cartflows_Ca_Db_Updater' ) ) {

	/**
	 * Cart Abandonment DB Updater class.
	 *
	 * @since 2.1.3
	 */
	class Cartflows_Ca_Db_Updater {

		/**
		 * Class instance.
		 *
		 * @var Cartflows_Ca_Db_Updater
		 */
		private static $instance;

		/**
		 * Columns added by this migration, keyed by name with their definition.
		 *
		 * @var array<string,string>
		 */
		private $revenue_columns = [
			'original_cart_total' => 'DECIMAL(10,2) DEFAULT NULL',
			'order_id'            => 'BIGINT(20) DEFAULT NULL',
		];

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', [ $this, 'show_update_notice' ] );
			add_action( 'wp_ajax_wcar_update_db', [ $this, 'update_db_ajax' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		/**
		 * Initiator.
		 *
		 * @return Cartflows_Ca_Db_Updater
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Whether the stored schema version is current.
		 *
		 * Cheap option read used as the source of truth once a migration has
		 * completed, so the heavier INFORMATION_SCHEMA lookups are skipped.
		 *
		 * @return bool
		 */
		public static function columns_exist() {
			return version_compare( get_option( 'wcar_db_version', '1.0.0' ), CARTFLOWS_CA_DB_VER, '>=' );
		}

		/**
		 * Whether an existing install still needs the schema migration.
		 *
		 * Self-heals: if the columns are already present (e.g. just created on
		 * a fresh install) the stored version is advanced so the check stops
		 * running on every admin load.
		 *
		 * @return bool
		 */
		public function needs_db_update() {
			if ( self::columns_exist() ) {
				return false;
			}

			if ( $this->revenue_columns_present() ) {
				update_option( 'wcar_db_version', CARTFLOWS_CA_DB_VER );
				return false;
			}

			return true;
		}

		/**
		 * Whether the notice (and its script) should display on the current screen.
		 *
		 * @return bool
		 */
		private function should_display(): bool {
			if ( ! current_user_can( 'manage_options' ) || ! $this->needs_db_update() ) {
				return false;
			}

			if ( class_exists( 'Cartflows_Ca_Admin_Notices' ) && ! Cartflows_Ca_Admin_Notices::get_instance()->allowed_screen_for_notices() ) {
				return false;
			}

			return true;
		}

		/**
		 * Show the database-update admin notice.
		 *
		 * @return void
		 */
		public function show_update_notice(): void {
			if ( ! $this->should_display() ) {
				return;
			}
			?>
			<div id="wcar-update-notice" class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Cart Abandonment Recovery database update required.', 'woo-cart-abandonment-recovery' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'We need to update your database to maintain compatibility with the latest features. Please back up your database before running this update.', 'woo-cart-abandonment-recovery' ); ?>
				</p>
				<p>
					<button type="button" class="button-primary" id="wcar-update-db-btn">
						<?php esc_html_e( 'Update Database', 'woo-cart-abandonment-recovery' ); ?>
					</button>
					<span class="spinner" style="float: none; margin-left: 10px;"></span>
				</p>
			</div>
			<?php
		}

		/**
		 * Enqueue the script that drives the update button.
		 *
		 * @return void
		 */
		public function enqueue_scripts(): void {
			if ( ! $this->should_display() ) {
				return;
			}

			wp_enqueue_script(
				'wcar-db-update',
				CARTFLOWS_CA_URL . 'admin/assets/js/db-update.js',
				[ 'jquery' ],
				CARTFLOWS_CA_VER,
				true
			);

			wp_localize_script(
				'wcar-db-update',
				'wcarDbUpdate',
				[
					'nonce'    => wp_create_nonce( 'wcar_update_db' ),
					'messages' => [
						'success' => __( 'Database updated successfully!', 'woo-cart-abandonment-recovery' ),
						'failed'  => __( 'Update failed. Please try again.', 'woo-cart-abandonment-recovery' ),
					],
				]
			);
		}

		/**
		 * Handle the AJAX database update request.
		 *
		 * @return void
		 */
		public function update_db_ajax(): void {
			check_ajax_referer( 'wcar_update_db', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( "You don't have permission to perform this action.", 'woo-cart-abandonment-recovery' ), 403 );
			}

			if ( ! $this->run_update() ) {
				wp_send_json_error( __( 'Database update could not complete. Please try again, and contact support if the problem persists.', 'woo-cart-abandonment-recovery' ) );
			}

			wp_send_json_success();
		}

		/**
		 * Run the migration and advance the stored version on success.
		 *
		 * @return bool
		 */
		public function run_update(): bool {
			$success = $this->add_revenue_columns();

			if ( $success ) {
				update_option( 'wcar_db_version', CARTFLOWS_CA_DB_VER );
			}

			/**
			 * Fires after the cart abandonment schema migration runs.
			 *
			 * @since 2.1.3
			 * @param bool $success Whether the migration completed.
			 */
			do_action( 'cartflows_ca_db_updated', $success );

			return $success;
		}

		/**
		 * Seed the new columns on a fresh install only.
		 *
		 * Runs at activation against an empty table where the ALTER is instant
		 * and needs no consent. Installs that already hold data are left for
		 * the admin-notice flow.
		 *
		 * @return void
		 */
		public function maybe_seed_on_fresh_install(): void {
			global $wpdb;

			$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // db call ok; no-cache ok.
			if ( empty( $table_exists ) ) {
				return;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
			if ( $row_count > 0 ) {
				return;
			}

			$this->run_update();
		}

		/**
		 * Whether every migration column is already present on the table.
		 *
		 * @return bool
		 */
		private function revenue_columns_present(): bool {
			global $wpdb;

			$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

			foreach ( array_keys( $this->revenue_columns ) as $column ) {
				if ( ! $this->column_exists( $table, $column ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Add any missing migration columns to the cart abandonment table.
		 *
		 * Idempotent: each column is checked against INFORMATION_SCHEMA before
		 * an explicit ALTER, so re-running is safe.
		 *
		 * @return bool True when all columns are present afterwards.
		 */
		private function add_revenue_columns(): bool {
			global $wpdb;

			$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // db call ok; no-cache ok.
			if ( empty( $table_exists ) ) {
				return false;
			}

			$all_present = true;

			foreach ( $this->revenue_columns as $column => $definition ) {
				if ( $this->column_exists( $table, $column ) ) {
					continue;
				}

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$altered = $wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}" );

				if ( false === $altered ) {
					$all_present = false;
				}
			}

			return $all_present;
		}

		/**
		 * Whether a given column exists on a table.
		 *
		 * @param string $table  Full table name.
		 * @param string $column Column name.
		 * @return bool
		 */
		private function column_exists( $table, $column ): bool {
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$found = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
					DB_NAME,
					$table,
					$column
				)
			);

			return ! empty( $found );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method.
	 */
	Cartflows_Ca_Db_Updater::get_instance();
}
