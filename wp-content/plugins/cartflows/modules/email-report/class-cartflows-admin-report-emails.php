<?php
/**
 * Email.
 *
 * @package CartFlows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CartflowsAdmin\AdminCore\Inc\AdminHelper;
/**
 * Class Cartflows_Admin_Report_Emails.
 */
class Cartflows_Admin_Report_Emails {


	/**
	 * Class instance.
	 *
	 * @access private
	 * @var $instance Class instance.
	 */
	private static $instance;

		/**
		 * Initiator
		 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Constructor.
	 */
	public function __construct() {

		// It will run once.
		add_action( 'admin_init', array( $this, 'schedule_weekly_report_email' ) );

		add_action( 'cartflows_send_report_summary_email', array( $this, 'send_weekly_report_email' ) );

		add_filter( 'admin_init', array( $this, 'unsubscribe_cartflows_weekly_emails' ), 10 );
	}

	/**
	 * Schedule the weekly email.
	 */
	public function schedule_weekly_report_email() {

		$is_report_emails = get_option( 'cartflows_stats_report_emails', 'enable' );

		if ( 'enable' === $is_report_emails && function_exists( 'as_next_scheduled_action' ) && false === as_next_scheduled_action( 'cartflows_send_report_summary_email' ) ) {

			wcf()->logger->log( 'Is weekly report enabled: ' . $is_report_emails );

			$date = new DateTime( 'next monday 2pm' );

			// It will automatically reschedule the action once initiated.
			as_schedule_recurring_action( $date, WEEK_IN_SECONDS, 'cartflows_send_report_summary_email' );

			wcf()->logger->log( 'Weekly report action scheduled. Action: cartflows_send_report_summary_email ' );
		} elseif ( 'enable' !== $is_report_emails && as_next_scheduled_action( 'cartflows_send_report_summary_email' ) ) {
			as_unschedule_all_actions( 'cartflows_send_report_summary_email' );

			wcf()->logger->log( 'Weekly report action unscheduled. Action: cartflows_send_report_summary_email ' );
		}
	}

	/**
	 * Send weekly report email.
	 */
	public function send_weekly_report_email() {

		$is_report_emails = get_option( 'cartflows_stats_report_emails', 'enable' );

		$emails = get_option( 'cartflows_stats_report_email_ids', get_option( 'admin_email' ) );

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 'enable' === $is_report_emails && ! empty( $emails ) && apply_filters( 'cartflows_send_weekly_report_email', true ) ) {

			$stats = $this->get_last_week_stats();

			wcf()->logger->log( 'Send weekly emails to ' . $emails );
			wcf()->logger->log( 'Total Revenue: ' . $stats['total_revenue'] );

			if ( isset( $stats['total_revenue_raw'] ) && $stats['total_revenue_raw'] > 0 ) {

				$subject  = $this->get_email_subject( $stats );
				$headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";
				$headers .= "Content-Type: text/html;\r\n";

				$emails = preg_split( "/[\f\r\n]+/", $emails );

				foreach ( $emails as $email_id ) {
					$user_info  = get_user_by( 'email', $email_id );
					$name       = $user_info ? $user_info->display_name : __( 'There', 'cartflows' );
					$email_body = $this->get_email_content( $stats, $name, $email_id );
					$status     = wp_mail( $email_id, $subject, stripslashes( $email_body ), $headers ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
					wcf()->logger->log( 'Email send status for email ' . $email_id . ' is ' . $status );
				}
			}
		}
	}

		/**
		 *  Unsubscribe the user from the mailing list.
		 */
	public function unsubscribe_cartflows_weekly_emails() {

		$unsubscribe = filter_input( INPUT_GET, 'unsubscribe_weekly_email', FILTER_VALIDATE_BOOLEAN );
		$page        = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$email       = filter_input( INPUT_GET, 'email', FILTER_SANITIZE_EMAIL );
		$token       = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $unsubscribe && 'cartflows' === $page && ! empty( $email ) && is_user_logged_in() && current_user_can( 'cartflows_manage_settings' ) ) {

			// Verify unsubscribe token to prevent CSRF.
			$expected_token = wp_hash( 'cartflows_unsubscribe_' . $email );
			if ( ! hash_equals( $expected_token, $token ) ) {
				wp_die( esc_html__( 'Invalid unsubscribe link.', 'cartflows' ), esc_html__( 'Error', 'cartflows' ), array( 'response' => 403 ) );
			}

			$email_list = get_option( 'cartflows_stats_report_email_ids', false );

			if ( ! empty( $email_list ) ) {
				$email_list = preg_split( "/[\f\r\n]+/", $email_list );

				$email_list = array_filter(
					$email_list,
					function ( $e ) use ( $email ) {
						return ( $e !== $email );
					}
				);

				$email_list = implode( "\n", $email_list );

				update_option( 'cartflows_stats_report_email_ids', $email_list );
				wcf()->logger->log( 'Email unsubscribed: ' . $email );
			}

			wp_die( esc_html__( 'You have successfully unsubscribed from our weekly emails list.', 'cartflows' ), esc_html__( 'Unsubscribed', 'cartflows' ) );
		}
	}

	/**
	 *  Get the stats mention in to email.
	 */
	public function get_last_week_stats() {

		$start_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$end_date   = gmdate( 'Y-m-d' );

		return AdminHelper::get_earnings( $start_date, $end_date );
	}

	/**
	 *  Get the stats mention in to email.
	 */
	public function get_last_month_stats() {

		$start_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = gmdate( 'Y-m-d' );

		return AdminHelper::get_earnings( $start_date, $end_date );
	}

	/**
	 * Get admin report email subject.
	 *
	 * @param array<string, mixed> $stats Weekly stats for revenue interpolation.
	 * @return string
	 */
	public function get_email_subject( $stats = array() ) {

		// Strip wc_price() HTML wrappers and decode numeric entities (e.g. &#8360; for ₨) so currency symbols render in plain-text subject lines.
		$revenue = isset( $stats['total_revenue'] ) && is_string( $stats['total_revenue'] )
			? html_entity_decode( wp_strip_all_tags( $stats['total_revenue'] ), ENT_QUOTES, 'UTF-8' )
			: '';

		if ( '' === $revenue ) {
			return esc_html__( 'Your weekly CartFlows report', 'cartflows' );
		}

		/* translators: %s: Total revenue earned in the last 7 days, formatted with currency. */
		return sprintf( esc_html__( 'Your weekly CartFlows report — %s earned', 'cartflows' ), $revenue );
	}

	/**
	 * Get a weekly tip for the "One thing to try this week" section.
	 * Rotates by ISO week so the same tip shows for all sends in the same week.
	 *
	 * @since 3.1.2
	 * @param array<string, mixed> $stats Weekly stats (allows the filter to choose data-driven tips).
	 * @return array{headline: string, body: string}
	 */
	public function get_weekly_tip( $stats = array() ) {

		$tips = array(
			array(
				'headline' => __( 'Add a one-click upsell to your top flow.', 'cartflows' ),
				'body'     => __( 'Stores that add one see an average +12% lift in average order value.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Enable an order bump on your highest-traffic checkout.', 'cartflows' ),
				'body'     => __( 'Order bumps consistently lift revenue per order with minimal effort.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Run an A/B test on your top-performing flow.', 'cartflows' ),
				'body'     => __( 'Test a single change — headline, button, or image — and let the winner run.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Add a thank-you page upsell.', 'cartflows' ),
				'body'     => __( 'Post-purchase offers convert better than pre-purchase upsells for many stores.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Streamline your checkout to a single column.', 'cartflows' ),
				'body'     => __( 'Single-column checkouts reduce cognitive load and tend to convert better on mobile.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Send abandoned cart recovery emails.', 'cartflows' ),
				'body'     => __( 'Most stores recover 8–15% of abandoned carts with a simple two-email sequence.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Use a countdown timer on limited-time offers.', 'cartflows' ),
				'body'     => __( 'Urgency, when honest, lifts conversion on time-sensitive promotions.', 'cartflows' ),
			),
			array(
				'headline' => __( 'Pre-fill checkout fields whenever possible.', 'cartflows' ),
				'body'     => __( 'Each saved keystroke reduces friction at the highest-stakes step of the journey.', 'cartflows' ),
			),
		);

		$index = (int) gmdate( 'W' ) % count( $tips );
		$tip   = $tips[ $index ];

		/**
		 * Filter the weekly tip shown in the report email.
		 *
		 * @param array<string, mixed> $tip   Default tip with 'headline' and 'body'.
		 * @param array<string, mixed> $stats Weekly stats.
		 */
		$filtered = apply_filters( 'cartflows_weekly_email_tip', $tip, $stats );

		if (
			is_array( $filtered )
			&& isset( $filtered['headline'], $filtered['body'] )
			&& is_string( $filtered['headline'] )
			&& is_string( $filtered['body'] )
		) {
			return array(
				'headline' => $filtered['headline'],
				'body'     => $filtered['body'],
			);
		}

		return $tip;
	}

	/**
	 * Get top wins data (top flow, best step, top bump) for the highlights section.
	 * Free returns an empty array; Pro hooks the filter to return real data.
	 *
	 * @since 3.1.2
	 * @param string $start_date Range start (Y-m-d).
	 * @param string $end_date   Range end (Y-m-d).
	 * @return array<int, array{label?: string, detail?: string}>
	 */
	public function get_top_wins( $start_date, $end_date ) {

		/**
		 * Filter top wins bullets shown in the weekly email.
		 *
		 * @param array<int, mixed> $bullets    Default empty array.
		 * @param string            $start_date Range start.
		 * @param string            $end_date   Range end.
		 */
		$bullets = apply_filters( 'cartflows_weekly_email_top_wins', array(), $start_date, $end_date );

		if ( ! is_array( $bullets ) ) {
			return array();
		}

		$normalised = array();
		foreach ( $bullets as $bullet ) {
			if (
				is_array( $bullet )
				&& isset( $bullet['label'], $bullet['detail'] )
				&& is_string( $bullet['label'] )
				&& is_string( $bullet['detail'] )
			) {
				$normalised[] = array(
					'label'  => $bullet['label'],
					'detail' => $bullet['detail'],
				);
			}
		}

		return $normalised;
	}

	/**
	 * Get quick link destinations for the report email.
	 *
	 * @since 3.1.2
	 * @return array<string, string>
	 */
	public function get_quick_links() {

		$links = array(
			__( 'View full report', 'cartflows' ) => admin_url( 'admin.php?page=cartflows' ),
			__( 'Edit your flows', 'cartflows' )  => admin_url( 'admin.php?page=cartflows&path=flows' ),
			__( 'Browse templates', 'cartflows' ) => admin_url( 'admin.php?page=cartflows&path=library' ),
			__( 'Get help', 'cartflows' )         => 'https://cartflows.com/docs/',
		);

		if ( ! _is_cartflows_pro() ) {
			$links[ __( 'Try CartFlows Pro', 'cartflows' ) ] = 'https://cartflows.com/pricing/?utm_source=newsletter&utm_medium=weekly-report-email&utm_campaign=cartflows-pro';
		} else {
			$links[ __( 'Discover More Products', 'cartflows' ) ] = 'https://cartflows.com/products-to-scale-growth/?utm_source=newsletter&utm_medium=weekly-report-email&utm_campaign=cartflows-bsf';
		}

		/**
		 * Filter the quick links shown in the weekly email.
		 *
		 * @param array<string, mixed> $links Map of label => URL.
		 */
		$filtered = apply_filters( 'cartflows_weekly_email_quick_links', $links );

		if ( ! is_array( $filtered ) ) {
			return $links;
		}

		$normalised = array();
		foreach ( $filtered as $label => $url ) {
			if ( is_string( $label ) && is_string( $url ) ) {
				$normalised[ $label ] = $url;
			}
		}

		return $normalised;
	}

	/**
	 *  Get admin report email content.
	 *
	 * @param array  $stats reports details.
	 * @param string $user_name user name.
	 * @param string $email_id email id.
	 */
	public function get_email_content( $stats, $user_name, $email_id ) {

		$cf_logo            = CARTFLOWS_URL . 'assets/images/cartflows-email-logo.png';
		$unsubscribe_token  = wp_hash( 'cartflows_unsubscribe_' . $email_id );
		$unsubscribe_link   = add_query_arg(
			array(
				'page'                     => 'cartflows',
				'unsubscribe_weekly_email' => true,
				'email'                    => $email_id,
				'token'                    => $unsubscribe_token,
			),
			admin_url( 'admin.php' )
		);
		$facebook_icon      = CARTFLOWS_URL . 'assets/images/facebook2x.png';
		$twitter_icon       = CARTFLOWS_URL . 'assets/images/twitter2x.png';
		$youtube_icon       = CARTFLOWS_URL . 'assets/images/youtube2x.png';
		$from_date          = gmdate( 'M j', strtotime( '-7 days' ) );
		$to_date            = gmdate( 'M j' );
		$total_orders       = $stats['total_orders'];
		$total_visits       = $stats['total_visits'];
		$order_bump_revenue = $stats['total_bump_revenue'];
		$offers_revenue     = $stats['total_offers_revenue'];
		$lock_icon          = CARTFLOWS_URL . 'assets/images/lock.png';

		$total_revenue      = $stats['total_revenue'];
		$last_month_stats   = $this->get_last_month_stats();
		$last_month_revenue = $last_month_stats['total_revenue'];
		$store_name         = get_bloginfo( 'name' );

		$is_pro      = _is_cartflows_pro();
		$start_date  = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$end_date    = gmdate( 'Y-m-d' );
		$top_wins    = $this->get_top_wins( $start_date, $end_date );
		$weekly_tip  = $this->get_weekly_tip( $stats );
		$quick_links = $this->get_quick_links();
		$sign_off    = __( 'The CartFlows team', 'cartflows' );

		/* translators: 1: total orders, 2: total visits. */
		$preheader = sprintf( esc_html__( '%1$s orders from %2$s visits — here\'s what to optimize next.', 'cartflows' ), (int) $total_orders, (int) $total_visits );

		return include CARTFLOWS_DIR . 'modules/email-report/templates/email-body.php';
	}
}

Cartflows_Admin_Report_Emails::get_instance();
