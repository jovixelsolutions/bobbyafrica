<?php
/**
 * Icons Helper Class (Sprite System)
 *
 * @package Click_To_Chat
 * @subpackage Administration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Icons' ) ) {

	/**
	 * Icons Class
	 */
	class HT_CTC_Icons {

		/**
		 * SVG Definitions (Paths/Polylines only)
		 *
		 * @var array
		 */
		private static $icons = array(
			// Lucide Save Icon
			'save'             => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>',
			// Settings (Simpler Gear)
			// Settings (Simpler Gear)
			// Settings (Standard Gear)
			'settings'         => '<path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
			// Menu (Hamburger)
			'menu'             => '<line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="18" x2="20" y2="18"></line>',
			// Arrow Left Right (Switch)
			'arrow-left-right' => '<path d="M8 3 4 7l4 4"></path><path d="M4 7h16"></path><path d="m16 21 4-4-4-4"></path><path d="M20 17H4"></path>',
			// Sun (Light Mode)
			'sun'              => '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path>',
			// Moon (Dark Mode)
			'moon'             => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>',
			// Monitor (System Mode)
			'monitor'          => '<rect width="20" height="14" x="2" y="3" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line>',
			// Loader 2 (Spinner)
			'loader-2'         => '<path d="M21 12a9 9 0 1 1-6.219-8.56"></path>',
			// Crown (Pro Badge)
			'crown'            => '<path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"/><path d="M5 21h14"/>',
			// Pointer / Click (greetings action trigger)
			'click'            => '<path d="M3 12h3"/><path d="M12 3v3"/><path d="M7.8 7.8 5.6 5.6"/><path d="M16.2 7.8l2.2-2.2"/><path d="M7.8 16.2l-2.2 2.2"/><path d="M12 12l9 3l-4 2l-2 4l-3-9"/>',
			// Eye / Viewport (greetings action trigger)
			'eye'              => '<path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0-4 0"/><path d="M21 12c-2.4 4-5.4 6-9 6c-3.6 0-6.6-2-9-6c2.4-4 5.4-6 9-6c3.6 0 6.6 2 9 6"/>',
			// Clock / Time Delay (greetings action trigger)
			'clock'            => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
			// Arrow Down / Scroll Depth (greetings action trigger)
			'arrow-down'       => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
			// Code / Shortcode
			'code'             => '<polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline>',
			// Square Mouse Pointer / Custom Element (a UI element being pointed at)
			'pointer-square'   => '<path d="M12.034 12.681a.498.498 0 0 1 .647-.647l9 3.5a.5.5 0 0 1-.033.943l-3.444 1.068a1 1 0 0 0-.66.66l-1.067 3.443a.5.5 0 0 1-.943.033z"/><path d="M5 3a2 2 0 0 0-2 2"/><path d="M19 3a2 2 0 0 1 2 2"/><path d="M5 21a2 2 0 0 1-2-2"/><path d="M9 3h1"/><path d="M9 21h2"/><path d="M14 3h1"/><path d="M3 9v1"/><path d="M21 9v2"/><path d="M3 14v1"/>',
			// Copy (click-to-copy cue)
			'copy'             => '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
			// Check (copied / confirmation state)
			'check'            => '<path d="M20 6 9 17l-5-5"/>',
			// Link (URL parameter feature)
			'link'             => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
			// Cookie (cookie value feature)
			'cookie'           => '<path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 15.5v.01"/><path d="M12 12v.01"/><path d="M11 17v.01"/><path d="M7 14v.01"/>',
		);

		/**
		 * Get SVG Icon (Sprite Reference)
		 *
		 * @param string $name  Icon name.
		 * @param string $classname Optional class name.
		 * @return string SVG HTML using <use> tag.
		 */
		public static function get( $name, $classname = '' ) {
			if ( ! isset( self::$icons[ $name ] ) ) {
				return '';
			}

			$class_attr = ( $classname ) ? ' class="' . esc_attr( $classname ) . '"' : '';

			// Return SVG referencing the symbol ID
			return sprintf(
				'<svg%s><use href="#ctc-icon-%s"></use></svg>',
				$class_attr,
				esc_attr( $name )
			);
		}

		/**
		 * Render SVG Icon (Sprite Reference)
		 *
		 * @param string $name  Icon name.
		 * @param string $classname Optional class name.
		 */
		public static function render( $name, $classname = '' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is sanitized at generation/Static SVG content.
			echo self::get( $name, $classname );
		}

		/**
		 * Render the hidden SVG Sprite Sheet.
		 * Should be called once in the footer/bottom of the page.
		 */
		public static function render_sprites() {
			echo '<svg style="display: none;" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">';
			foreach ( self::$icons as $name => $content ) {
				printf(
					'<symbol id="ctc-icon-%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</symbol>',
					esc_attr( $name ),
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG content.
					$content
				);
			}
			echo '</svg>';
		}
	}
}
