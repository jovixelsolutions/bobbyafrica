<?php
/**
 * CartFlows Dashboard Widget.
 *
 * Renders a "Funnel Performance" dashboard widget summarising
 * the last 30 days of orders and revenue, with locked PRO upsell
 * cards for free users.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cartflows_Dashboard_Widget.
 */
class Cartflows_Dashboard_Widget {

	/**
	 * Instance.
	 *
	 * @access private
	 * @var self|null Class instance.
	 * @since 2.2.3
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @since 2.2.3
	 * @return self Initialised object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 2.2.3
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @since 2.2.3
	 * @return void
	 */
	public function register_widget() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// No meaningful stats without WooCommerce; avoid fatal from wc_price() in renderers.
		if ( ! wcf()->is_woo_active ) {
			return;
		}

		wp_add_dashboard_widget(
			'cartflows_funnel_performance',
			__( 'CartFlows — Funnel Performance', 'cartflows' ),
			array( $this, 'render_widget' )
		);
	}

	/**
	 * Render the dashboard widget body.
	 *
	 * @since 2.2.3
	 * @return void
	 */
	public function render_widget() {

		$stats  = $this->get_stats();
		$locked = $this->show_locked_cards();

		// Decide message copy based on activity and licence state.
		if ( $locked ) {
			if ( 0 === (int) $stats['total_orders'] && (float) $stats['total_revenue_raw'] <= 0 ) {
				$message = __( 'Your funnels are ready to generate revenue. Start boosting sales instantly with order bumps and upsells in CartFlows Pro.', 'cartflows' );
			} elseif ( (int) $stats['total_orders'] > 0 ) {
				/* translators: %s: Total revenue formatted as currency. */
				$message = sprintf( __( "You've made %s — now scale it with upsells & order bumps.", 'cartflows' ), wp_strip_all_tags( str_replace( '&nbsp;', ' ', wc_price( (float) $stats['total_revenue_raw'] ) ) ) );
			} else {
				$message = __( 'Want to boost funnel revenue by 30%+? Unlock order bumps, upsells, and advanced funnel features with CartFlows Pro.', 'cartflows' );
			}
		} else {
			/* translators: %s: Total revenue formatted as currency. */
			$message = sprintf( __( "You've earned %s with CartFlows in the last 30 days.", 'cartflows' ), wp_strip_all_tags( str_replace( '&nbsp;', ' ', wc_price( (float) $stats['total_revenue_raw'] ) ) ) );
		}

		$report_url  = admin_url( 'admin.php?page=cartflows&path=analytics' );
		$upgrade_url = \Cartflows_Helper::get_upgrade_to_pro_link(
			'pricing',
			'',
			array(
				'utm_source'   => 'plugin-page',
				'utm_campaign' => 'dashboard-widget-card',
			)
		);
		?>
		<div class="wcf-dash-widget">
			<div class="wcf-dash-header">
				<div class="wcf-dash-titles">
					<p class="wcf-dash-heading"><?php esc_html_e( 'Your Funnel Performance 🚀', 'cartflows' ); ?></p>
					<p class="wcf-dash-subheading">
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %s: Bolded "last 30 days" timeframe. */
								__( 'See how your funnels performed in the %s', 'cartflows' ),
								'<strong>' . esc_html__( 'last 30 days', 'cartflows' ) . '</strong>'
							),
							array( 'strong' => array() )
						);
						?>
					</p>
				</div>
				<a class="wcf-dash-report-link" href="<?php echo esc_url( $report_url ); ?>"><?php esc_html_e( 'View Report', 'cartflows' ); ?> &rarr;</a>
			</div>

			<div class="wcf-dash-cards">
				<?php $this->render_card( __( 'Orders Generated', 'cartflows' ), $stats['total_orders'], false, false, false, $upgrade_url ); ?>
				<?php $this->render_card( __( 'Total Revenue', 'cartflows' ), $stats['total_revenue_raw'], true, false, false, $upgrade_url ); ?>
				<?php $this->render_card( __( 'Revenue from Order Bumps', 'cartflows' ), $stats['total_bump_revenue_raw'], true, $locked, true, $upgrade_url ); ?>
				<?php $this->render_card( __( 'Revenue from Upsells', 'cartflows' ), $stats['total_offers_revenue_raw'], true, $locked, true, $upgrade_url ); ?>
			</div>

			<div class="wcf-dash-message">
				<?php echo esc_html( $message ); ?>
			</div>

			<div class="wcf-dash-footer">
				<?php if ( $locked ) : ?>
					<a href="
					<?php
					echo esc_url(
						\Cartflows_Helper::get_upgrade_to_pro_link(
							'pricing',
							'',
							array(
								'utm_source'   => 'plugin-page',
								'utm_campaign' => 'dashboard-widget-upgrade',
							) 
						) 
					);
					?>
								" target="_blank" rel="noopener noreferrer" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'cartflows' ); ?></a>
				<?php endif; ?>
				<div class="wcf-dash-footer-links">
					<a href="
					<?php
					echo esc_url(
						\Cartflows_Helper::get_kb_doc_link(
							'https://cartflows.com/docs/',
							array(
								'utm_source'   => 'plugin-page',
								'utm_campaign' => 'dashboard-widget-help',
							) 
						) 
					);
					?>
								" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help & Tutorials', 'cartflows' ); ?></a>
				</div>
			</div>

			<?php $this->render_inline_styles(); ?>
		</div>
		<?php
	}

	/**
	 * Get cached funnel stats for the last 30 days.
	 *
	 * @since 2.2.3
	 * @return array Normalised stats array.
	 *
	 * @phpstan-return array{total_orders: int, total_revenue_raw: float, total_bump_revenue_raw: float, total_offers_revenue_raw: float}
	 */
	private function get_stats() {

		$end      = gmdate( 'Y-m-d' );
		$start    = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'NA';

		// Currency code is part of the key so symbol changes invalidate the cache.
		$cache_key = 'cartflows_dashboard_widget_stats_' . $currency . '_' . $end;
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && isset( $cached['total_orders'], $cached['total_revenue_raw'], $cached['total_bump_revenue_raw'], $cached['total_offers_revenue_raw'] ) ) {
			return array(
				'total_orders'             => (int) $cached['total_orders'],
				'total_revenue_raw'        => (float) $cached['total_revenue_raw'],
				'total_bump_revenue_raw'   => (float) $cached['total_bump_revenue_raw'],
				'total_offers_revenue_raw' => (float) $cached['total_offers_revenue_raw'],
			);
		}

		$earnings = array();

		if ( class_exists( '\CartflowsAdmin\AdminCore\Inc\AdminHelper' ) ) {
			$earnings = \CartflowsAdmin\AdminCore\Inc\AdminHelper::get_earnings( $start, $end, '', 'home', '' );
		}

		if ( ! is_array( $earnings ) ) {
			$earnings = array();
		}

		// Pro filter shape may omit the *_raw keys; fall back to numeric parse of formatted fields.
		$bump_raw   = isset( $earnings['total_bump_revenue_raw'] ) ? (float) $earnings['total_bump_revenue_raw'] : ( isset( $earnings['total_bump_revenue'] ) && is_numeric( $earnings['total_bump_revenue'] ) ? (float) $earnings['total_bump_revenue'] : 0.0 );
		$offers_raw = isset( $earnings['total_offers_revenue_raw'] ) ? (float) $earnings['total_offers_revenue_raw'] : ( isset( $earnings['total_offers_revenue'] ) && is_numeric( $earnings['total_offers_revenue'] ) ? (float) $earnings['total_offers_revenue'] : 0.0 );

		$stats = array(
			'total_orders'             => isset( $earnings['total_orders'] ) ? (int) $earnings['total_orders'] : 0,
			'total_revenue_raw'        => isset( $earnings['total_revenue_raw'] ) ? (float) $earnings['total_revenue_raw'] : ( isset( $earnings['total_revenue'] ) && is_numeric( $earnings['total_revenue'] ) ? (float) $earnings['total_revenue'] : 0.0 ),
			'total_bump_revenue_raw'   => $bump_raw,
			'total_offers_revenue_raw' => $offers_raw,
		);

		set_transient( $cache_key, $stats, 6 * HOUR_IN_SECONDS );

		return $stats;
	}

	/**
	 * Decide whether the PRO upsell cards should appear locked.
	 *
	 * @since 2.2.3
	 * @return bool True when running on free or unlicensed Pro.
	 */
	private function show_locked_cards() {
		return ! ( _is_cartflows_pro() && is_wcf_pro_plan() );
	}

	/**
	 * Render a single stat card.
	 *
	 * @since 2.2.3
	 * @param string           $label       Card label.
	 * @param int|float|string $raw_value   Raw numeric value.
	 * @param bool             $is_currency Whether the value should be formatted as currency.
	 * @param bool             $is_locked   Whether the card should display the locked state.
	 * @param bool             $is_pro      Whether this metric is PRO-only.
	 * @param string           $upgrade_url Upgrade URL used when the card is locked.
	 * @return void
	 */
	private function render_card( $label, $raw_value, $is_currency, $is_locked, $is_pro, $upgrade_url ) {

		$numeric = (float) ( is_numeric( $raw_value ) ? $raw_value : 0 );
		$display = $is_currency ? wc_price( $numeric ) : number_format_i18n( $numeric );

		$class = 'wcf-dash-card';
		if ( $is_locked ) {
			$class .= ' is-locked';
		}
		if ( $is_pro ) {
			$class .= ' is-pro';
		}

		// Locked card is rendered as a clickable upgrade surface; unlocked is a plain div.
		if ( $is_locked ) {
			?>
			<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php
		} else {
			?>
			<div class="<?php echo esc_attr( $class ); ?>">
			<?php
		}
		?>
			<span class="wcf-dash-card-label">
				<span class="wcf-dash-card-label-text"><?php echo esc_html( $label ); ?></span>
			</span>
			<span class="wcf-dash-card-value-wrap">
				<?php if ( $is_locked ) : ?>
					<span class="wcf-dash-card-value wcf-dash-card-value-teaser" aria-hidden="true">-</span>
					<span class="wcf-dash-card-unlock">
						<svg width="11" height="11" viewBox="0 0 16 16" aria-hidden="true" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							<path d="M8 1a3 3 0 0 0-3 3v3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-1V4a3 3 0 0 0-3-3zm-2 6V4a2 2 0 1 1 4 0v3H6z"/>
						</svg>
						<span><?php esc_html_e( 'Unlock with Pro', 'cartflows' ); ?></span>
					</span>
				<?php else : ?>
					<span class="wcf-dash-card-value">
						<?php echo $is_currency ? wp_kses_post( $display ) : esc_html( $display ); ?>
					</span>
				<?php endif; ?>
			</span>
		<?php
		echo $is_locked ? '</a>' : '</div>';
	}

	/**
	 * Render scoped inline styles for the widget.
	 *
	 * @since 2.2.3
	 * @return void
	 */
	private function render_inline_styles() {
		?>
		<style>
			#cartflows_funnel_performance .wcf-dash-widget { position: relative; }
			#cartflows_funnel_performance .wcf-dash-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
			#cartflows_funnel_performance .wcf-dash-titles { flex: 1; min-width: 0; }
			#cartflows_funnel_performance .wcf-dash-heading { margin: 0 0 2px; font-size: 15px; font-weight: 600; color: #111827; line-height: 1.3; }
			#cartflows_funnel_performance .wcf-dash-subheading { margin: 0; font-size: 13px; color: #6b7280; line-height: 1.4; }
			#cartflows_funnel_performance .wcf-dash-report-link { font-size: 12px; font-weight: 500; text-decoration: none; color: var(--wp-admin-theme-color, #2271b1); }
			#cartflows_funnel_performance .wcf-dash-report-link:hover { color: var(--wp-admin-theme-color-darker-10, #135e96); }
			#cartflows_funnel_performance .wcf-dash-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
			#cartflows_funnel_performance .wcf-dash-card { position: relative; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
			#cartflows_funnel_performance .wcf-dash-card-label { display: inline-flex; align-items: center; font-size: 12px; font-weight: 600; color: #6b7280; }
			#cartflows_funnel_performance .wcf-dash-card-label-text { line-height: 1.3; }
			#cartflows_funnel_performance .wcf-dash-card-value-wrap { display: flex; flex-direction: column; gap: 6px; min-height: 30px; }
			#cartflows_funnel_performance .wcf-dash-card-value { font-size: 22px; font-weight: 700; color: #111827; line-height: 1.15; letter-spacing: -0.01em; }
			#cartflows_funnel_performance .wcf-dash-card-value-teaser { color: #9ca3af; user-select: none; pointer-events: none; }
			#cartflows_funnel_performance .wcf-dash-card-unlock { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: var(--wp-admin-theme-color, #2271b1); }
			#cartflows_funnel_performance .wcf-dash-card-unlock svg { flex: 0 0 auto; }
			#cartflows_funnel_performance a.wcf-dash-card { text-decoration: none; cursor: pointer; }
			#cartflows_funnel_performance a.wcf-dash-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, .08); border-color: var(--wp-admin-theme-color, #2271b1); }
			#cartflows_funnel_performance a.wcf-dash-card:hover .wcf-dash-card-unlock { color: var(--wp-admin-theme-color-darker-10, #135e96); }
			#cartflows_funnel_performance a.wcf-dash-card:focus-visible { outline: 2px solid var(--wp-admin-theme-color, #2271b1); outline-offset: 2px; }
			#cartflows_funnel_performance .wcf-dash-message { margin-bottom: 14px; font-size: 13px; line-height: 1.5; color: #374151; }
			#cartflows_funnel_performance .wcf-dash-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding-top: 14px; border-top: 1px solid #e5e7eb; }
			#cartflows_funnel_performance .wcf-dash-footer-links { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 14px; }
			#cartflows_funnel_performance .wcf-dash-footer-links a { position: relative; font-size: 13px; font-weight: 500; text-decoration: none; color: var(--wp-admin-theme-color, #2271b1); transition: color .15s ease; }
			#cartflows_funnel_performance .wcf-dash-footer-links a:hover { color: var(--wp-admin-theme-color-darker-10, #135e96); }
			#cartflows_funnel_performance .wcf-dash-footer-links a + a::before { content: '·'; position: absolute; left: -10px; color: #d1d5db; font-weight: 700; }
			@media ( max-width: 480px ) {
				#cartflows_funnel_performance .wcf-dash-cards { grid-template-columns: minmax(0, 1fr); }
			}
		</style>
		<?php
	}

}

Cartflows_Dashboard_Widget::get_instance();
