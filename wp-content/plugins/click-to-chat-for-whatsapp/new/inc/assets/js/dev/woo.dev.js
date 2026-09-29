
/**
 * Click to Chat - WooCommerce Integration
 *
 * It applies styles of the cart buttons to chat widget(s1,s8) on single product and shop pages.
 *
 * @package Click to Chat
 * @since 3.8
 * currenlty only loads if display like cart layout option is checked
 * at woo single product pages or shop page.
 */
( function htCtcWooModule ( $ ) {
	$( function handleWooReady () {
		console.log( 'WooCommerce Dev JS Loaded' );

		try {
			/**
			 * Check if cart button exists, if yes then apply styles to chat widget.
			 *  single_add_to_cart_button: Single Product Page cart button
			 *  add_to_cart_button: Shop/Archive Page cart button
			 */
			if (
				document.querySelector( '.single_add_to_cart_button' ) ||
				document.querySelector( '.add_to_cart_button' )
			) {
				initializeCartLayout();
			} else if ( document.querySelector( '.ctc_woo_place' ) ) {
				//  && !document.querySelector('.ctc_woo_schedule')
				// in shop page - cart button might not exist
				// display (might be added display none)
				console.log( 'Displaying .ctc_woo_place' );
				displayCtcWooPlace();
			}
		} catch ( error ) {
			console.error( 'Error initializing cart layout:', error );
		}

		/**
		 * Displays the .ctc_woo_place element if .ctc_woo_schedule is not present.
		 */
		function displayCtcWooPlace () {
			if ( ! document.querySelector( '.ctc_woo_schedule' ) ) {
				$( '.ctc_woo_place' )
					.css( {
						display: $( '.ctc_woo_place' )
							.attr( 'data-dt' ),
					} );
				$( '.ctc_woo_place' )
					.show();
			}
		}

		/**
			 * Initializes the cart layout for WooCommerce single and archive pages.
			 *
			 * .ctc_woo_single_cart_layout:
			 * Click to Chat Widget adds at WooCommerce Single Product Page
			 * based on position added at settings.
			 * .ctc_woo_shop_cart_layout:
			 * Click to Chat Widget adds at WooCommerce Shop/Archive Page
			 * or related products list at single product page.
			 */
		function initializeCartLayout () {
			console.log( 'Initializing cart layout' );

			const singleCartButton = document.querySelector( '.single_add_to_cart_button' );

			/**
				 * Single Product Page cart button adding by if-else condition
				 * as it might be different for different themes.
				 * '.button.add_to_cart_button' is sourced from Astra theme.
				 */
			const shopCartButton =
				document.querySelector( '.button.add_to_cart_button' ) ||
				document.querySelector( '.add_to_cart_button' );

			console.log( 'Single Cart Button:', singleCartButton );
			console.log( 'Shop/archive list Cart Button:', shopCartButton );

			// Single Product Page - s1 Button Styling
			applyS1Styling( '.ctc_woo_single_cart_layout .s1_btn', singleCartButton );

			// Shop/Archive Page - s1 Button Styling.
			// issue: shopCartButton i.e. add_to_cart_button captures another 'bag' icon
			// that shares the same class
			applyS1Styling( '.ctc_woo_shop_cart_layout .s1_btn', shopCartButton, true );

			// Apply s8 Styling for Shop/Archive Pages
			// applyS8Styling('.ctc_woo_shop_cart_layout .s_8', singleCartButton);
			applyS8Styling( '.ctc_woo_shop_cart_layout .s_8', shopCartButton );

			// applyS8Styling('.ctc_woo_shop_cart_layout .s_8', shopCartButton, true);

			// Apply s8 Styling for Single Product Pages
			applyS8Styling( '.ctc_woo_single_cart_layout .s_8', singleCartButton );
		}

		/**
		 * Applies styles to the cart buttons based on existing WooCommerce button styles.
		 *
		 * @param {string} selector - The selector for the target buttons.
		 * @param {HTMLElement} sourceButton - The button whose styles should be copied.
		 * @param {boolean} multiple - Whether to apply styles to multiple elements.
		 */
		function applyS1Styling ( selector, sourceButton, multiple = false ) {
			const targetButtons = multiple ?
				document.querySelectorAll( selector ) :
				[ document.querySelector( selector ) ];

			console.log( `Applying cart styling to: ${selector}` );
			console.log( `${selector}` );

			console.log( 'Source Button:', sourceButton );
			console.log( 'Target Buttons:', targetButtons );

			if ( ! sourceButton || ! targetButtons.length || ! targetButtons[ 0 ] ) { return; }

			console.log( 'Applying styles to:', targetButtons );

			targetButtons.forEach( ( targetButton ) => {
				copyNodeStyle( sourceButton, targetButton );
				const textColor = $( targetButton )
					.css( 'color' );
				const bgColor = $( targetButton )
					.css( 'background-color' );

				$( targetButton )
					.css( {
						display: 'inline-flex',
						width: 'fit-content',
						'align-items': 'center',
						color: textColor,
						'background-color': bgColor,
					} );
			} );

			displayCtcWooPlace();
		}

		/**
		 * Applies specific styling for .s8 elements based on the main cart button.
		 *
		 * @param {string} selector - The selector for the s8 elements.
		 * @param {HTMLElement} referenceButton - The button to use as a style reference.
		 */
		function applyS8Styling ( selector, referenceButton ) {
			console.log( `Applying s8 styling to: ${selector}` );
			console.log( 'Reference Button:', referenceButton );

			// Select all elements, not just one
			const targetElements = document.querySelectorAll( selector );

			if ( ! targetElements.length || ! referenceButton ) { return; }

			console.log( 'Applying styles to:', targetElements );

			targetElements.forEach( ( targetElement ) => {
				$( targetElement )
					.css( {
						'min-height': $( referenceButton )
							.css( 'min-height' ),
						'font-size': $( referenceButton )
							.css( 'font-size' ),
						'font-weight': $( referenceButton )
							.css( 'font-weight' ),
						'letter-spacing': $( referenceButton )
							.css( 'letter-spacing' ),
						'border-radius': $( referenceButton )
							.css( 'border-radius' ),
						width: 'fit-content',
					} );
			} );

			displayCtcWooPlace();
		}

		/**
		 * Copies computed styles from one element to another.
		 *
		 * @param {HTMLElement} sourceNode - The source element.
		 * @param {HTMLElement} targetNode - The target element.
		 */
		function copyNodeStyle ( sourceNode, targetNode ) {

			const computedStyle = window.getComputedStyle( sourceNode );

			// Array.from( computedStyle )
			// 	.forEach( ( property ) => {
			// 		targetNode.style.setProperty(
			// 			property,
			// 			computedStyle.getPropertyValue( property ),
			// 			computedStyle.getPropertyPriority( property ),
			// 		);
			// 	} );

			// Avoid cloning every computed style property as it's expensive and
			// unnecessary. Only copy the few styles that visually match the
			// WooCommerce buttons.
			const propertiesToCopy = [
				'color',
				'background-color',
				'border-radius',
				'border',
				'padding',
				'font-size',
				'font-weight',

				// // more related
				// 'letter-spacing',
				// 'min-height',
				// 'text-transform',
				// 'box-shadow',
				// 'line-height',
				// 'font-family',
				// 'opacity',
				// 'visibility',
				// 'transition',
				// 'filter',
				// 'cursor',
				// 'outline',
				// 'text-shadow',
				// 'text-decoration',
				// 'text-align',
				// 'flex',
				// 'align-items',
				// 'justify-content',
				// 'flex-direction',
				// 'flex-wrap',
			];

			propertiesToCopy.forEach( property => {
				targetNode.style.setProperty(
					property,
					computedStyle.getPropertyValue( property ),
					computedStyle.getPropertyPriority( property ),
				);
			} );

		}
	} );
} )( jQuery );
