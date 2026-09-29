<?php
/**
 * Animation styles for Click to Chat widgets.
 *
 * Provides CSS keyframes for button animations and entry effects.
 *
 * @since 2.8
 * @since 3.3.5 Added entry effects.
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Animations' ) ) {

	/**
	 * Outputs CSS rules for chat animations.
	 */
	class HT_CTC_Animations {

		/**
		 * Render base animation styles.
		 *
		 * @param string     $animation  Animation identifier.
		 * @param string     $duration   Animation duration (e.g. '1s').
		 * @param string     $delay      Delay before the animation runs.
		 * @param int|string $iterations Number of iterations to run.
		 *
		 * @return void
		 */
		public function animations( $animation, $duration, $delay, $iterations ) {
			printf(
				'<style id="ht-ctc-animations">.ht_ctc_animation{animation-duration:%1$s;animation-fill-mode:both;animation-delay:%2$s;animation-iteration-count:%3$s;}',
				esc_attr( $duration ),
				esc_attr( $delay ),
				esc_attr( $iterations )
			);

			$callback = $this->resolve_callback( $animation );
			if ( $callback ) {
				$this->$callback( "ht_ctc_an_$animation" );
			}

			echo '</style>';
		}

		/**
		 * Render entry animation styles.
		 *
		 * @param string $animation  Animation identifier.
		 * @param string $duration   Animation duration (e.g. '1s').
		 * @param string $delay      Animation delay.
		 * @param string $iterations Iteration count.
		 *
		 * @return void
		 */
		public function entry( $animation, $duration, $delay, $iterations ) {
			printf(
				'<style id="ht-ctc-entry-animations">.ht_ctc_entry_animation{animation-duration:%1$s;animation-fill-mode:both;animation-delay:%2$s;animation-iteration-count:%3$s;}',
				esc_attr( $duration ),
				esc_attr( $delay ),
				esc_attr( $iterations )
			);

			$callback = $this->resolve_callback( $animation );
			if ( $callback ) {
				$this->$callback( "ht_ctc_an_entry_$animation" );
			}

			echo '</style>';
		}

		/**
		 * Resolve animation callback names to snake_case method names.
		 *
		 * @param string $animation Raw animation identifier.
		 * @return string|null Resolved method name when available.
		 */
		protected function resolve_callback( $animation ) {
			$animation = (string) $animation;
			$animation = preg_replace( '/([a-z0-9])([A-Z])/', '\1_\2', $animation );
			$animation = preg_replace( '/[^a-zA-Z0-9_]/', '_', $animation );
			$animation = strtolower( $animation );
			$animation = preg_replace( '/_{2,}/', '_', $animation );
			$animation = trim( $animation, '_' );

			if ( '' === $animation ) {
				return null;
			}

			if ( method_exists( $this, $animation ) ) {
				return $animation;
			}

			return null;
		}

		/**
		 * Minify CSS by removing comments, whitespace, and unnecessary characters.
		 *
		 * @param string $css CSS code to minify.
		 * @return string Minified CSS code.
		 */
		private function minify_css( $css ) {

			// Remove comments
			$css = preg_replace( '!/\*.*?\*/!s', '', $css );

			// Remove space before/after colons, semicolons, commas, braces
			$css = preg_replace( '/\s*([{};:,])\s*/', '$1', $css );

			// Remove trailing semicolon before }
			$css = preg_replace( '/;}/', '}', $css );

			// Compress multiple spaces into one
			$css = preg_replace( '/\s+/', ' ', $css );

			// Trim space
			$css = trim( $css );

			return $css;
		}

		/**
		 * Output CSS for the bounce animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce( $selector ) {
			?>
		@keyframes bounce{from,20%,53%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1);transform:translate3d(0,0,0)}40%,43%{animation-timing-function:cubic-bezier(0.755,0.05,0.855,0.06);transform:translate3d(0,-30px,0) scaleY(1.1)}70%{animation-timing-function:cubic-bezier(0.755,0.05,0.855,0.06);transform:translate3d(0,-15px,0) scaleY(1.05)}80%{transition-timing-function:cubic-bezier(0.215,0.61,0.355,1);transform:translate3d(0,0,0) scaleY(0.95)}90%{transform:translate3d(0,-4px,0) scaleY(1.02)}}.<?php echo esc_attr( $selector ); ?>{animation-name:bounce;transform-origin:center bottom}
			<?php
		}

		/**
		 * Output CSS for the flash animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function flash( $selector ) {
			?>
		@keyframes flash{from,50%,to{opacity:1}25%,75%{opacity:0}}.<?php echo esc_attr( $selector ); ?>{animation-name:flash}
			<?php
		}

		/**
		 * Output CSS for the pulse animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function pulse( $selector ) {
			?>
		@keyframes pulse{from{transform:scale3d(1,1,1)}50%{transform:scale3d(1.05,1.05,1.05)}to{transform:scale3d(1,1,1)}}.<?php echo esc_attr( $selector ); ?>{animation-name:pulse;animation-timing-function:ease-in-out}
			<?php
		}

		/**
		 * Output CSS for the heart-beat animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function heart_beat( $selector ) {
			// Duplicate animation-duration values retained for compatibility.
			?>
		@keyframes heartBeat{0%{transform:scale(1)}14%{transform:scale(1.3)}28%{transform:scale(1)}42%{transform:scale(1.3)}70%{transform:scale(1)}}.<?php echo esc_attr( $selector ); ?>{animation-name:heartBeat;animation-duration:calc(1s * 1.3);animation-duration:calc(var(1) * 1.3);animation-timing-function:ease-in-out}
			<?php
		}

		/**
		 * Output CSS for the flip animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function flip( $selector ) {
			?>
		@keyframes flip{from{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,0) rotate3d(0,1,0,-360deg);animation-timing-function:ease-out}40%{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,150px) rotate3d(0,1,0,-190deg);animation-timing-function:ease-out}50%{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,150px) rotate3d(0,1,0,-170deg);animation-timing-function:ease-in}80%{transform:perspective(400px) scale3d(.95,.95,.95) translate3d(0,0,0) rotate3d(0,1,0,0deg);animation-timing-function:ease-in}to{transform:perspective(400px) scale3d(1,1,1) translate3d(0,0,0) rotate3d(0,1,0,0deg);animation-timing-function:ease-in}}.<?php echo esc_attr( $selector ); ?>{backface-visibility:visible;animation-name:flip}
			<?php
		}

		/**
		 * Output CSS for the bounce-in-left animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce_in_left( $selector ) {
			?>
		@keyframes bounceInLeft{from,60%,75%,90%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1)}0%{opacity:0;transform:translate3d(-3000px,0,0) scaleX(3)}60%{opacity:1;transform:translate3d(25px,0,0) scaleX(1)}75%{transform:translate3d(-10px,0,0) scaleX(0.98)}90%{transform:translate3d(5px,0,0) scaleX(0.995)}to{transform:translate3d(0,0,0)}}.<?php echo esc_attr( $selector ); ?>{animation-name:bounceInLeft}
			<?php
		}

		/**
		 * Output CSS for the bounce-in-right animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce_in_right( $selector ) {
			?>
		@keyframes bounceInRight{from,60%,75%,90%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1)}from{opacity:0;transform:translate3d(3000px,0,0) scaleX(3)}60%{opacity:1;transform:translate3d(-25px,0,0) scaleX(1)}75%{transform:translate3d(10px,0,0) scaleX(0.98)}90%{transform:translate3d(-5px,0,0) scaleX(0.995)}to{transform:translate3d(0,0,0)}}.<?php echo esc_attr( $selector ); ?>{animation-name:bounceInRight}
			<?php
		}

		/**
		 * Output CSS for the bounce-in animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce_in( $selector ) {
			?>
		@keyframes bounceIn{from,20%,40%,60%,80%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1)}0%{opacity:0;transform:scale3d(0.3,0.3,0.3)}20%{transform:scale3d(1.1,1.1,1.1)}40%{transform:scale3d(0.9,0.9,0.9)}60%{opacity:1;transform:scale3d(1.03,1.03,1.03)}80%{transform:scale3d(0.97,0.97,0.97)}to{opacity:1;transform:scale3d(1,1,1)}}.<?php echo esc_attr( $selector ); ?>{animation-duration:calc(1s * 0.75);animation-duration:calc(var(1) * 0.75);animation-name:bounceIn}
			<?php
		}

		/**
		 * Output CSS for the bounce-in-down animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce_in_down( $selector ) {
			?>
		@keyframes bounceInDown{from,60%,75%,90%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1)}0%{opacity:0;transform:translate3d(0,-3000px,0) scaleY(3)}60%{opacity:1;transform:translate3d(0,25px,0) scaleY(0.9)}75%{transform:translate3d(0,-10px,0) scaleY(0.95)}90%{transform:translate3d(0,5px,0) scaleY(0.985)}to{transform:translate3d(0,0,0)}}.<?php echo esc_attr( $selector ); ?>{animation-name:bounceInDown}
			<?php
		}

		/**
		 * Output CSS for the bounce-in-up animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bounce_in_up( $selector ) {
			?>
		@keyframes bounceInUp{from,60%,75%,90%,to{animation-timing-function:cubic-bezier(0.215,0.61,0.355,1)}from{opacity:0;transform:translate3d(0,3000px,0) scaleY(5)}60%{opacity:1;transform:translate3d(0,-20px,0) scaleY(0.9)}75%{transform:translate3d(0,10px,0) scaleY(0.95)}90%{transform:translate3d(0,-5px,0) scaleY(0.985)}to{transform:translate3d(0,0,0)}}.<?php echo esc_attr( $selector ); ?>{animation-name:bounceInUp}
			<?php
		}

		/**
		 * Output CSS for the center entry animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function center( $selector ) {
			?>
		@keyframes center{from{transform:scale(0);}to{transform:scale(1);}}.<?php echo esc_attr( $selector ); ?>{animation: center .25s;}
			<?php
		}

		/**
		 * Output CSS for the corner entry animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function corner( $selector ) {
			?>
			@keyframes ht_ctc_anim_corner {0% {opacity: 0;transform: scale(0);}100% {opacity: 1;transform: scale(1);}}.<?php echo esc_attr( $selector ); ?> {animation-name: ht_ctc_anim_corner;animation-timing-function: cubic-bezier(0.25, 1, 0.5, 1);transform-origin: bottom var(--side, right);}
			<?php
			// animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);  ~ if timing set to 0.12s
			// animation-timing-function: cubic-bezier(0.25, 1, 0.5, 1); if timing set to 0.4s
		}


		/**
		 * Output CSS for the zoom-in animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function zoom_in( $selector ) {
			?>
		@keyframes zoomIn {
		from {
			opacity: 0;
			transform: scale3d(0.3, 0.3, 0.3);
		}

		50% {
			opacity: 1;
		}
		}
		.<?php echo esc_attr( $selector ); ?> {
		animation: zoomIn .25s;
		/* animation-name: zoomIn; */
		}
			<?php
		}

		/**
		 * Output CSS for the bottom-right animation.
		 *
		 * @param string $selector Target selector class.
		 * @return void
		 */
		public function bottom_right( $selector ) {
			?>
		@keyframes bounceInBR {
		0% {
			transform: translateY(1000px) translateX(1000px);
			opacity: 0;
		}
		100% {
			transform: translateY(0) translateX(0);
			opacity: 1;
		}
		}
		.<?php echo esc_attr( $selector ); ?> {
			animation: bounceInBR 0.5s linear both;
		}
			<?php
		}
	}

	// new HT_CTC_Animations();

} // END class_exists check
