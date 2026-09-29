<?php
/**
 * Email Template Importer and Exporter
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_CA_Email_Template_Importer_Exporter.
 */
class Cartflows_CA_Email_Template_Importer_Exporter {

	/**
	 * Instance
	 *
	 * @var Cartflows_CA_Email_Template_Importer_Exporter
	 */
	private static $instance;

	/**
	 * Templates table name.
	 *
	 * @var string
	 */
	private $template_table;

	/**
	 * Templates meta table name.
	 *
	 * @var string
	 */
	private $meta_table;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		global $wpdb;
		$this->template_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;
		$this->meta_table     = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;

		add_action( 'wp_ajax_wcf_ca_import_email_templates', array( $this, 'ajax_import_templates' ) );
		add_action( 'wp_ajax_wcf_ca_export_email_templates', array( $this, 'ajax_export_templates' ) );
	}

	/**
	 * AJAX handler for exporting templates.
	 *
	 * @return void
	 */
	public function ajax_export_templates() {
		check_ajax_referer( WCF_EMAIL_TEMPLATES_NONCE, '_wpnonce' );

		$ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = is_array( $ids ) ? array_map( 'absint', $ids ) : array_map( 'absint', array( $ids ) );

		if ( empty( $ids ) ) {
			$templates = $this->get_all_templates();
		} else {
			$templates = $this->get_templates_by_ids( $ids );
		}

		$data = $this->generate_export_array( $templates );

		wp_send_json( $data );
	}

	/**
	 * AJAX handler for importing templates.
	 *
	 * @return void
	 */
	public function ajax_import_templates() {
		// Verifies the nonce for security purposes.
		check_ajax_referer( WCF_EMAIL_TEMPLATES_NONCE, '_wpnonce' );

		// Retrieves and sanitizes the template data from the POST request. No need to further sanitization as we are doing later before importing the code.
		$json = isset( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// Checks if the template data is empty.
		if ( empty( $json ) ) {
			// Sends a JSON error response if no data is found.
			wp_send_json_error();
		}

		// Decodes the JSON string into a PHP array.
		$templates = json_decode( $json, true );
		if ( empty( $templates ) ) {
			wp_send_json_error();
		}

		if ( isset( $templates['template_name'] ) ) {
			$templates = array( $templates );
		} elseif ( ! is_array( $templates ) ) {
			wp_send_json_error();
		}

		$this->insert_templates( $templates );

		wp_send_json_success();
	}

	/**
	 * Fetches all email templates from the database.
	 *
	 * @return array An array of all email templates.
	 */
	public function get_all_templates() {
			global $wpdb;

		// Note: Using {$this->template_table} is safe here because it's a class property and not user input.
		$query = $wpdb->prepare( "SELECT * FROM {$this->template_table} WHERE %d = %d", 1, 1 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// Note: The get_results() call is safe because the query is prepared above.
			return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Fetch templates by IDs.
	 *
	 * @param array $ids Template IDs.
	 * @return array
	 */
	public function get_templates_by_ids( $ids ) {
		global $wpdb;

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs are sanitized before.
		$query = $wpdb->prepare( "SELECT * FROM {$this->template_table} WHERE id IN ($placeholders)", $ids ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Generate export array.
	 *
	 * @param array $templates templates.
	 * @return array
	 */
	public function generate_export_array( $templates ) {
		global $wpdb;
		$export = array();
		foreach ( $templates as $template ) {
			// Note: Using {$this->meta_table} is safe here because it's a class property and not user input.
			$query = $wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$this->meta_table} WHERE email_template_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$template['id']
			);
			// Note: The get_results() call is safe because the query is prepared above.
			$meta_rows = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$meta      = array();
			if ( $meta_rows ) {
				foreach ( $meta_rows as $row ) {
					$meta[ $row['meta_key'] ] = $row['meta_value'];
				}
			}
			$export[] = array_merge( $template, array( 'meta' => $meta ) );
		}
		return $export;
	}

	/**
	 * Insert templates from the array.
	 *
	 * @param array $templates Array containing templates.
	 * @return void
	 */
	public function insert_templates( $templates ) {
		// Sanitize all templates and meta keys before import.
		$templates = $this->sanitize_import_data( $templates );

		// Iterates through each template in the array.
		foreach ( $templates as $template ) {
			// Extracts meta data from the template, defaulting to an empty array.
			$meta = isset( $template['meta'] ) ? $template['meta'] : array();

			// Removes the meta data from the main template array.
			unset( $template['meta'] );

			// Inserts the template into the database and retrieves the new ID.
			$insert_id = $this->insert_template( $template );

			// Checks if the template was inserted and if there is meta data.
			if ( $insert_id && ! empty( $meta ) ) {
				// Inserts the associated meta data for the new template.
				$this->insert_template_meta( $insert_id, $meta );
			}
		}
	}

	/**
	 * Insert template row.
	 *
	 * @param array $template template array.
	 * @return int|false
	 */
	private function insert_template( $template ) {
		global $wpdb;
		$wpdb->insert(
			$this->template_table,
			array(
				'template_name'  => isset( $template['template_name'] ) ? $template['template_name'] : '',
				'email_subject'  => isset( $template['email_subject'] ) ? $template['email_subject'] : '',
				'email_body'     => isset( $template['email_body'] ) ? $template['email_body'] : '',
				'is_activated'   => isset( $template['is_activated'] ) ? (int) $template['is_activated'] : 0,
				'frequency'      => isset( $template['frequency'] ) ? (int) $template['frequency'] : 0,
				'frequency_unit' => isset( $template['frequency_unit'] ) ? $template['frequency_unit'] : 'MINUTE',
			),
			array( '%s', '%s', '%s', '%d', '%d', '%s' )
		); // db call ok; no-cache ok.
		return $wpdb->insert_id;
	}

	/**
	 * Insert template meta.
	 *
	 * @param int   $template_id template id.
	 * @param array $meta        meta array.
	 * @return void
	 */
	private function insert_template_meta( $template_id, $meta ) {
		global $wpdb;
		foreach ( $meta as $key => $value ) {
			$value = ( 'wcf_email_body' === $key ) ? wp_kses_post( $value ) : sanitize_text_field( $value );
			$wpdb->insert(
				$this->meta_table,
				array(
					'email_template_id' => $template_id,
					'meta_key'          => sanitize_text_field( $key ),
					// Note: This is an insert operation, the slow query check for meta_value is not applicable here.
					'meta_value'        => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			); // db call ok; no-cache ok.
		}
	}

	/**
	 * Sanitize all template and meta keys before import.
	 *
	 * @param array $templates Templates array from JSON.
	 * @return array Sanitized templates array.
	 */
	private function sanitize_import_data( $templates ) {
		$sanitized_templates = array();
		foreach ( $templates as $template ) {
			// Top-level keys with defaults and sanitization.
			$sanitized                   = array();
			$sanitized['template_name']  = isset( $template['template_name'] ) ? sanitize_text_field( $template['template_name'] ) : '';
			$sanitized['email_subject']  = isset( $template['email_subject'] ) ? sanitize_text_field( $template['email_subject'] ) : '';
			$sanitized['email_body']     = isset( $template['email_body'] ) ? wp_kses_post( html_entity_decode( $template['email_body'], ENT_COMPAT, 'UTF-8' ) ) : '';
			$sanitized['is_activated']   = isset( $template['is_activated'] ) ? (int) $template['is_activated'] : 0;
			$sanitized['frequency']      = isset( $template['frequency'] ) ? (int) $template['frequency'] : 0;
			$sanitized['frequency_unit'] = isset( $template['frequency_unit'] ) ? sanitize_text_field( $template['frequency_unit'] ) : 'MINUTE';

			// Meta keys with defaults and sanitization.
			$meta                                     = isset( $template['meta'] ) && is_array( $template['meta'] ) ? $template['meta'] : array();
			$sanitized_meta                           = array();
			$sanitized_meta['override_global_coupon'] = ! empty( $meta['override_global_coupon'] );
			$sanitized_meta['discount_type']          = isset( $meta['discount_type'] ) ? sanitize_text_field( $meta['discount_type'] ) : 'percent';
			$sanitized_meta['coupon_amount']          = isset( $meta['coupon_amount'] ) ? intval( $meta['coupon_amount'] ) : 10;
			$sanitized_meta['coupon_expiry_date']     = isset( $meta['coupon_expiry_date'] ) ? sanitize_text_field( $meta['coupon_expiry_date'] ) : '';
			$sanitized_meta['coupon_expiry_unit']     = isset( $meta['coupon_expiry_unit'] ) ? sanitize_text_field( $meta['coupon_expiry_unit'] ) : 'hours';
			$sanitized_meta['use_woo_email_style']    = ! empty( $meta['use_woo_email_style'] );
			$sanitized_meta['auto_coupon']            = isset( $meta['auto_coupon'] ) ? ! empty( $meta['auto_coupon'] ) : false;
			$sanitized_meta['free_shipping_coupon']   = isset( $meta['free_shipping_coupon'] ) ? ! empty( $meta['free_shipping_coupon'] ) : false;
			$sanitized_meta['individual_use_only']    = isset( $meta['individual_use_only'] ) ? ! empty( $meta['individual_use_only'] ) : false;

			$sanitized['meta']     = $sanitized_meta;
			$sanitized_templates[] = $sanitized;
		}
		return $sanitized_templates;
	}
}

Cartflows_CA_Email_Template_Importer_Exporter::get_instance();

