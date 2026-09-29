<?php
/**
 * Abstract Ability Class
 *
 * Base class for all Modern Cart abilities.
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class Abstract_Ability
 */
abstract class Abstract_Ability {

	/**
	 * Ability ID (e.g. 'moderncart/get-settings').
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Ability Category.
	 *
	 * @var string
	 */
	protected $category = 'moderncart';

	/**
	 * Ability Label.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Ability Description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Required capability for this ability.
	 *
	 * Defaults to 'manage_options' (admin-only). Abilities that require a
	 * lower capability must explicitly override this in configure().
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Whether the ability is destructive or state-changing.
	 * If true, it supports dry_run.
	 *
	 * @var bool
	 */
	protected $is_destructive = false;

	/**
	 * Plain-text instructions for AI agents calling this ability.
	 *
	 * Write this as a direct instruction to the AI — e.g. "Always call
	 * get-settings first before updating." Leave null to omit from registration.
	 *
	 * @var string|null
	 */
	protected $instructions = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->configure();
	}

	/**
	 * Configure the ability (set ID, label, description, etc.).
	 *
	 * @return void
	 */
	abstract public function configure();

	/**
	 * Get the input schema for the ability.
	 *
	 * @return array<string, mixed>
	 */
	abstract public function get_input_schema();

	/**
	 * Execute the ability.
	 *
	 * Must return a plain data array on success (matching output_schema),
	 * or a WP_Error on failure — consistent with the Abilities API contract.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	abstract public function execute( $args );

	/**
	 * Get the final input schema, including any automatically added parameters.
	 *
	 * Destructive abilities automatically receive a `dry_run` boolean parameter.
	 *
	 * @return array<string, mixed>
	 */
	public function get_final_input_schema() {
		$schema = $this->get_input_schema();

		// Ensure $schema['properties'] is an array before accessing nested keys.
		$properties = isset( $schema['properties'] ) && is_array( $schema['properties'] ) ? $schema['properties'] : array();

		if ( $this->is_destructive ) {
			// Inject dry_run support.
			if ( ! isset( $properties['dry_run'] ) ) {
				$properties['dry_run'] = array(
					'type'        => 'boolean',
					'description' => __( 'If true, simulates the changes without applying them.', 'modern-cart' ),
					'default'     => false,
				);
			}
		}

		$schema['properties'] = $properties;

		return $schema;
	}

	/**
	 * Get the meta annotations for this ability.
	 *
	 * The three hint booleans are derived from $is_destructive:
	 *  - Read abilities:  readOnlyHint=true,  destructiveHint=false, idempotentHint=true
	 *  - Write abilities: readOnlyHint=false, destructiveHint=true,  idempotentHint=false
	 *
	 * @return array<string, bool|float>
	 */
	public function get_annotations() {
		$is_write = $this->is_destructive;

		return array(
			'priority'        => 3.0,
			'readOnlyHint'    => ! $is_write,
			'idempotentHint'  => ! $is_write,
			'destructiveHint' => $is_write,
			'openWorldHint'   => false,
		);
	}

	/**
	 * Check that the current user is allowed to run this ability.
	 *
	 * Returns a plain bool consistent with the WordPress Abilities API contract.
	 * current_user_can() returns false for unauthenticated users, so a separate
	 * is_user_logged_in() check is not required.
	 *
	 * @param mixed $request WP_REST_Request or input array.
	 * @return bool
	 */
	public function check_permission( $request ) {
		return current_user_can( $this->capability );
	}

	/**
	 * Handle execution of the ability.
	 *
	 * Applies a capability re-check before delegating to execute(). This guards
	 * against handle_execute being called directly, bypassing check_permission.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function handle_execute( $args ) {
		// Re-verify the user still holds the required capability.
		if ( ! current_user_can( $this->capability ) ) {
			return new \WP_Error(
				'moderncart_ability_forbidden',
				__( 'You do not have permission to perform this action.', 'modern-cart' )
			);
		}

		try {
			if ( $this->is_destructive && ! empty( $args['dry_run'] ) ) {
				return $this->dry_run( $args );
			}

			return $this->execute( $args );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'moderncart_ability_exception', __( 'An unexpected error occurred:', 'modern-cart' ) . ' ' . $e->getMessage() );
		} catch ( \Error $e ) {
			return new \WP_Error( 'moderncart_ability_error', __( 'A system error occurred:', 'modern-cart' ) . ' ' . $e->getMessage() );
		}
	}

	/**
	 * Default dry run implementation.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>
	 */
	protected function dry_run( $args ) {
		return array( 'dry_run' => true );
	}

	/**
	 * Get the ability ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the ability label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get the ability description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the category.
	 *
	 * @return string
	 */
	public function get_category() {
		return $this->category;
	}

	/**
	 * Get the plain-text instructions for AI agents.
	 *
	 * Returns null when no instructions are set, so callers can skip the field.
	 *
	 * @return string|null
	 */
	public function get_instructions() {
		return $this->instructions;
	}
}
