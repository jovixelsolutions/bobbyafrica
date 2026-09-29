( function ( $ ) {
	/**
	 * Validate AJAX response before using its content for DOM insertion.
	 *
	 * @param {Object} response The AJAX response object.
	 * @return {boolean} True if response has valid content string.
	 */
	function moderncartIsValidResponse( response ) {
		return (
			response &&
			typeof response === 'object' &&
			typeof response.content === 'string'
		);
	}

	const ModernCartFrontendScripts = {
		init() {
			// Handle browser back/forward navigation to refresh cart state.
			window.addEventListener( 'pageshow', function ( e ) {
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					false,
					true
				);
			} );

			$( 'body' ).on( 'added_to_cart', function ( e ) {
				e.preventDefault();
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					true,
					true
				);
			} );

			$( 'body' ).on(
				'click keydown',
				'.moderncart-slide-out-header-close',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.hideSlideOut();
					}
				}
			);

			const customTriggers =
				ModernCartFrontendScripts.getCustomTriggerSelectors();

			if ( customTriggers ) {
				$( 'body' ).on(
					'click keydown',
					ModernCartFrontendScripts.getCustomTriggerSelectors(),
					function ( e ) {
						if (
							[ 'click', 'Enter', ' ' ].includes(
								e.type || e.key
							)
						) {
							e.preventDefault();
							ModernCartFrontendScripts.showSlideOut( e );
						}
					}
				);
			}

			$( 'body' ).on(
				'click',
				'#moderncart-slide-out-modal',
				function ( e ) {
					e.preventDefault();
					const container = $( '.moderncart-default-slide-out' );
					if (
						! container.is( e.target ) &&
						container.has( e.target ).length === 0
					) {
						ModernCartFrontendScripts.hideSlideOut();
					}
				}
			);

			$( 'body' ).on(
				'click keydown',
				'#moderncart-slide-out-modal a',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();

						if (
							'moderncart-powered-by' ===
							$( this ).parent().prop( 'className' )
						) {
							window.open( $( this ).attr( 'href' ), '_blank' );

							return;
						}
						window.location.href = $( this ).attr( 'href' );
					}
				}
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-floating-cart-button',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.showSlideOut( e );
					}
				}
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-checkout-form-edit-link',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.showSlideOut( e );
					}
				}
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-cart-item-actions-remove',
				moderncartDebounce( 250, function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.removeProduct( e, $( this ) );
					}
				} )
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-cart-item-quantity button',
				moderncartDebounce( 250, function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.updateProduct(
							e,
							$( this ),
							false
						);
					}
				} )
			);

			$( 'body' ).on(
				'change',
				'.moderncart-quantity__input',
				moderncartDebounce( 250, function ( e ) {
					e.preventDefault();

					ModernCartFrontendScripts.updateProduct(
						e,
						$( this ),
						true
					);
				} )
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-slide-out-coupon-form-button',
				moderncartDebounce( 250, function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.applyCoupon( e, $( this ) );
					}
				} )
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-coupon-remove-item-delete',
				moderncartDebounce( 250, function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.removeCoupon( e, $( this ) );
					}
				} )
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart_add_to_cart_button',
				moderncartDebounce( 250, function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.addToCartManually(
							e,
							$( this )
						);
					}
				} )
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-recommendations-button',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.showRecommendationsSlide();
					}
				}
			);

			$( 'body' ).on(
				'click keydown',
				'.moderncart-slide-back',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						ModernCartFrontendScripts.hideRecommendationsSlide();
					}
				}
			);

			$( document.body ).on( 'wc_fragments_loaded', function () {
				ModernCartFrontendScripts.notifyAddedToCart();
			} );
			// Array of classes to check for
			const classArray = [
				'single_variation_wrap',
				'grouped_form',
				'product_type_simple',
				'product-type-simple',
			];
			// Check if any class exists in the DOM tree
			const supportedClassExists = classArray.some(
				( className ) =>
					document.querySelector( '.' + className ) !== null
			);
			// Check if supported product type exists.
			if ( supportedClassExists ) {
				$( 'body' ).on(
					'click keydown',
					'.single_add_to_cart_button',
					function ( e ) {
						// Return early if AJAX add to cart is disabled for single product pages.
						if ( moderncart_ajax_object.disable_ajax_add_to_cart ) {
							localStorage.setItem(
								'moderncart_single_add_to_cart_clicked',
								'yes'
							);
							if ( $( 'form.cart' ).length ) {
								$( 'form.cart' ).submit();
							}
							return;
						}

						if (
							e.type === 'click' ||
							e.key === 'Enter' ||
							e.key === ' '
						) {
							if ( $( '.bos4w-display-wrap' ).length ) {
								return;
							}
							e.preventDefault();
							if ( $( this ).hasClass( 'disabled' ) ) {
								return;
							}
							ModernCartFrontendScripts.addToCartProductPage(
								e,
								$( this )
							);
						}
					}
				);
			}

			$( 'body' ).on(
				'click keydown',
				'.moderncart-have-coupon-code-area',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();
						const couponForm = $( '.moderncart-slide-out-coupon' );
						couponForm.toggleClass( 'moderncart-hide' );
						$(
							'.moderncart-have-coupon-code-area .moderncart-arrow-down-icon'
						).toggle();
						$(
							'.moderncart-have-coupon-code-area .moderncart-arrow-up-icon'
						).toggle();
						ModernCartFrontendScripts.couponFieldAriaLabel();
					}
				}
			);

			$( document ).on( 'keydown', function ( e ) {
				if ( e.key === 'Escape' ) {
					// Check if recommendations slide is open first
					if (
						$( '#moderncart-recommendations-slide' ).hasClass(
							'moderncart-slide-visible'
						)
					) {
						ModernCartFrontendScripts.hideRecommendationsSlide();
					} else if (
						$( '#moderncart-slide-out-modal' ).is( ':visible' )
					) {
						ModernCartFrontendScripts.hideSlideOut();
					}
				}
			} );

			$( 'body' ).on(
				'click keydown',
				'.moderncart-collapse-btn-link',
				function ( e ) {
					if (
						e.type === 'click' ||
						e.key === 'Enter' ||
						e.key === ' '
					) {
						e.preventDefault();

						$(
							'.' + $( this ).data( 'moderncart-target' )
						).slideToggle( 200 );
					}
				}
			);

			$( '.moderncart-cart-item-quantity .quantity__button--up' ).html(
				'<svg width="12" height="12" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#000000" class="bi bi-plus-lg" aria-hidden="true" focusable="false"><path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/> </svg>'
			);
			$( '.moderncart-cart-item-quantity .quantity__button--down' ).html(
				'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"> <path d="M6 12L18 12" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
			);

			// Removed header mini cart
			if ( $( '.modern-cart-for-wc-available' ).length ) {
				$(
					'.modern-cart-for-wc-available .ast-site-header-cart-data'
				).html( '' );
			}

			// Adding edit cart link
			if (
				'1' === moderncart_ajax_object.is_needed_edit_cart &&
				$( '#order_review_heading' ).length
			) {
				const orderReviewHeading = $( '#order_review_heading' );
				$( '#order_review_heading' ).append(
					'<button type="button" class="moderncart-checkout-form-edit-link" aria-label="' +
						moderncart_ajax_object.edit_cart_text +
						'">' +
						moderncart_ajax_object.edit_cart_text +
						'</button>'
				);
				const editLink = $( '.moderncart-checkout-form-edit-link' );
				editLink.css(
					'line-height',
					orderReviewHeading.css( 'line-height' )
				);
				editLink.css(
					'right',
					orderReviewHeading.css( 'padding-left' )
				);
				orderReviewHeading.css( 'position', 'relative' );
			}

			$( 'body' ).on( 'updated_checkout', function ( e ) {
				e.preventDefault();
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					false,
					true
				);
			} );

			$( 'body' ).on( 'updated_cart_totals', function ( e ) {
				e.preventDefault();
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					false,
					true
				);
			} );

			// Trigger cart refresh on item remove.
			$( 'body' ).on( 'removed_from_cart', function ( e ) {
				e.preventDefault();
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					false,
					true
				);
			} );

			$( 'body' ).on( 'cartflows_update_checkout', function ( e ) {
				e.preventDefault();
				ModernCartFrontendScripts.refreshSlideOutFloating(
					e,
					false,
					true
				);
			} );

			let resizeTimer;
			$( window ).resize( function () {
				clearTimeout( resizeTimer );
				resizeTimer = setTimeout(
					ModernCartFrontendScripts.displayCartItemImages,
					100
				);
			} );

			if ( $( '.ast-sticky-add-to-cart' ).length ) {
				const target = $( '.ast-sticky-add-to-cart' )[ 0 ];
				const observer = new MutationObserver( () => {
					if ( $( target ).hasClass( 'bottom is-active' ) ) {
						$( '#moderncart-floating-cart' ).css(
							'bottom',
							'80px'
						);
					} else {
						$( '#moderncart-floating-cart' ).css(
							'bottom',
							'20px'
						);
					}
				} );

				observer.observe( target, {
					attributes: true,
					attributeFilter: [ 'class' ],
				} );
			}

			if ( $( '#ast-scroll-top' ).length ) {
				const targetHeight = $( '#ast-scroll-top' ).outerHeight() + 35;
				if ( $( '#moderncart-floating-cart' ).length ) {
					$( '#moderncart-floating-cart' )[ 0 ].style.setProperty(
						'bottom',
						`${ targetHeight }px`,
						'important'
					);
				}
			}

			ModernCartFrontendScripts.couponFieldAccordionState();
			// Load the function to add third-party compatibilities.
			ModernCartFrontendScripts.addThirdPartyPluginTriggerCompatibilities();
		},

		/**
		 * Get custom trigger selectors.
		 */
		getCustomTriggerSelectors() {
			const customSelectors =
				moderncart_ajax_object?.custom_trigger_selectors || '';

			// Create array of default Astra selectors
			const defaultSelectors = [
				'.ast-site-header-cart-li a',
				'#ast-mobile-header .ast-site-header-cart-li',
			];

			// Split custom selectors by comma, clean them up, and combine with defaults
			const allSelectors = [
				...customSelectors
					.split( ',' )
					.map( ( selector ) => selector.trim() )
					.filter( Boolean ),
				...defaultSelectors,
			];

			// Remove duplicates and join into single string
			const customTriggers = [ ...new Set( allSelectors ) ].join( ', ' );

			return customTriggers;
		},

		/**
		 * Set coupon field accordion state based on user choice
		 */
		couponFieldAccordionState: () => {
			if ( localStorage.getItem( 'couponForm' ) ) {
				const isExpanded =
					localStorage.getItem( 'couponForm' ) === 'expanded';
				const couponForm = $( '.moderncart-slide-out-coupon' );
				if ( isExpanded ) {
					couponForm.removeClass( 'moderncart-hide' );
					$(
						'.moderncart-have-coupon-code-area .moderncart-arrow-down-icon'
					).removeClass( 'moderncart-hide' );
					$(
						'.moderncart-have-coupon-code-area .moderncart-arrow-up-icon'
					).addClass( 'moderncart-hide' );
				} else {
					couponForm.addClass( 'moderncart-hide' );
					$(
						'.moderncart-have-coupon-code-area .moderncart-arrow-down-icon'
					).addClass( 'moderncart-hide' );
					$(
						'.moderncart-have-coupon-code-area .moderncart-arrow-up-icon'
					).removeClass( 'moderncart-hide' );
				}
				ModernCartFrontendScripts.couponFieldAriaLabel( couponForm );
			}
		},

		/**
		 * Add aria labels to coupon field
		 */
		couponFieldAriaLabel: () => {
			const couponForm = $( '.moderncart-slide-out-coupon' );
			if ( ! couponForm.length ) {
				return;
			}
			const isCollapsed = couponForm.hasClass( 'moderncart-hide' );
			localStorage.setItem(
				'couponForm',
				isCollapsed ? 'minimized' : 'expanded'
			);
			$( '.moderncart-have-coupon-code-area' ).attr(
				'aria-expanded',
				! isCollapsed
			);
			couponForm.attr( 'aria-hidden', isCollapsed );
			if ( couponForm.hasClass( 'moderncart-hide' ) ) {
				if ( $( '.moderncart-slide-out-cart' )[ 0 ].length ) {
					$( '.moderncart-slide-out-cart' ).scrollTop(
						$( '.moderncart-slide-out-cart' )[ 0 ].position().top
					);
				}
				$( '#moderncart-coupon-form-container' ).attr(
					'aria-hidden',
					true
				);
			} else {
				$( '#moderncart-coupon-input' ).focus();
				$( '#moderncart-coupon-form-container' ).attr(
					'aria-hidden',
					false
				);
			}
		},

		/**
		 * Show cart product image based on the screen width
		 */
		displayCartItemImages: () => {
			if ( moderncart_ajax_object.enable_product_image_in_mobile_view ) {
				// If the setting to show product images in mobile view is enabled, do not hide images based on screen width.
				return;
			}
			const cartItemImage = $( '.moderncart-cart-item-image' );
			if ( window.innerWidth < 768 ) {
				cartItemImage.css( 'display', 'none' );
			} else {
				cartItemImage.css( 'display', 'block' );
			}
		},

		/**
		 * Add a refresh slide-out cart compatibilities for other third-party plugins.
		 */
		addThirdPartyPluginTriggerCompatibilities: () => {
			/**
			 * Trigger/update the slide-out cart on block builder's event.
			 * Case: While using the Spectra One, there is no JS triggers available for Add to Cart. As the product is getting added to cart via API call by WooCommerce.
			 * So instead of add_to_cart event wc-blocks_added_to_cart is triggered.
			 */
			document.body.addEventListener(
				'wc-blocks_added_to_cart',
				function ( e ) {
					e.preventDefault();
					ModernCartFrontendScripts.refreshSlideOutFloating(
						e,
						true,
						true
					);
				}
			);
		},

		/**
		 * Grouped Product Data.
		 */
		getGroupedProductData: () => {
			const products = [];

			// Select all quantity inputs
			const quantityInputs = document.querySelectorAll(
				'.woocommerce-grouped-product-list .quantity input[type="number"][name^="quantity"]'
			);

			// Loop through the quantity inputs and retrieve the product ID and quantity
			quantityInputs.forEach( function ( quantityInput ) {
				const productId = quantityInput
					.getAttribute( 'name' )
					.match( /\d+/ )[ 0 ];
				const quantity = quantityInput.value;
				if ( quantity >= 1 ) {
					products.push( {
						productId,
						quantity,
					} );
				}
			} );

			// Return an array of selected products
			return products;
		},

		/**
		 * Add to cart product item
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e       Object current prevent default event.
		 * @param {Object} current Object current mouse event.
		 */
		addToCartProductPage: ( e, current ) => {
			e.preventDefault();
			let productData = [
				{
					productId: current.val(),
					quantity: $( '.quantity .qty' ).val(),
				},
			];

			if ( $( '.single_variation_wrap' ).length ) {
				const attributes = {};
				const variations = $( '.variations' );

				// If variations exist, collect attribute inputs
				if ( variations.length ) {
					variations
						.find(
							'input[name^="attribute"], select[name^="attribute"]'
						)
						.each( function () {
							const name = $( this ).attr( 'name' );
							const value = $( this ).val();
							if ( value ) {
								attributes[ name ] = value;
							}
						} );
				}

				productData = [
					{
						productId:
							$( '.single_variation_wrap' )
								.find( 'input[name="product_id"]' )
								.val() || productData[ 0 ].productId,
						variationId: $( '.single_variation_wrap' )
							.find( 'input[name="variation_id"]' )
							.val(),
						quantity:
							$( '.single_variation_wrap' )
								.find( '.quantity .qty' )
								.val() || productData[ 0 ].quantity,
						attributes,
					},
				];
			}

			if ( $( '.grouped_form' ).length ) {
				if (
					0 ===
					ModernCartFrontendScripts.getGroupedProductData()?.length
				) {
					return;
				}
				productData = ModernCartFrontendScripts.getGroupedProductData();
			}
			// Get the cart form element to collect all form fields including add-ons and custom fields
			const form = document.querySelector( 'form.cart' );
			// Create FormData object to handle all form inputs including file uploads
			const formData = new FormData( form );

			// Process the form data into a structured object that can be sent via AJAX
			// This handles complex form fields like arrays and nested objects
			const processedFormData = moderncartProcessFormData( formData );

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_add_to_cart',
					productData,
					formEntries: processedFormData,
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
					current.addClass( 'moderncart-loading' );
					current.attr( 'disabled', true );
				},
				success( response ) {
					if ( ! response?.success && response?.data?.message ) {
						$( '.woocommerce-notices-wrapper' ).html( `
							<div class="woocommerce-error">
								${ $( '<div>' ).text( response.data.message ).html() }
							</div>
						` );
					}
					ModernCartFrontendScripts.refreshSlideOutFloating(
						e,
						true,
						true
					);
					ModernCartFrontendScripts.afterAjaxAction();
					ModernCartFrontendScripts.triggerRefetchFragements();
					current.removeClass( 'moderncart-loading' );
					current.removeAttr( 'disabled' );

					// Redirect to the required page after add-to-cart.
					if (
						response.redirect_to &&
						'yes' ===
							moderncart_ajax_object.cart_redirect_after_add &&
						false !== response.redirect_to &&
						'' !== response.redirect_to
					) {
						window.location = response.redirect_to;
					}
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
				},
			} );
		},

		/**
		 * Add to cart product item
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e       Object current prevent default event.
		 * @param {Object} current Object current mouse event.
		 */
		addToCartManually: ( e, current ) => {
			e.preventDefault();

			const isFromRecommendationsSlide =
				current.data( 'moderncart-type' ) === 'recommendation';

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_add_to_cart',
					productData: [
						{
							productId: current.data( 'product_id' ),
							quantity: 1,
						},
					],
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
					current.addClass( 'moderncart-loading' );
				},
				success() {
					// Close recommendations slide if adding from there
					if ( isFromRecommendationsSlide ) {
						ModernCartFrontendScripts.hideRecommendationsSlide();
					}

					ModernCartFrontendScripts.refreshSlideOutFloating(
						e,
						false,
						true
					);
					ModernCartFrontendScripts.afterAjaxAction();
					ModernCartFrontendScripts.triggerRefetchFragements();
					current.removeClass( 'moderncart-loading' );
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
					current.removeClass( 'moderncart-loading' );
				},
			} );
		},

		/**
		 * Remove coupon
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e       Object current prevent default event.
		 * @param {Object} current Object current mouse event.
		 */
		removeCoupon: ( e, current ) => {
			e.preventDefault();

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_remove_coupon',
					coupon: current.data( 'coupon' ),
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
				},
				success( response ) {
					if ( moderncartIsValidResponse( response ) ) {
						$( '#moderncart-slide-out-modal' ).html(
							response.content
						);
					}

					ModernCartFrontendScripts.refreshSlideOutFloating(
						e,
						false,
						true
					);
					ModernCartFrontendScripts.showNotice();
					ModernCartFrontendScripts.afterAjaxAction();
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
				},
			} );
		},

		/**
		 * Apply coupon
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e       Object current prevent default event.
		 * @param {Object} current Object current mouse event.
		 */
		applyCoupon: ( e, current ) => {
			e.preventDefault();
			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_apply_coupon',
					coupon: $( '#moderncart-coupon-input' ).val(),
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
					current.addClass( 'moderncart-loading' );
				},
				success( response ) {
					if ( moderncartIsValidResponse( response ) ) {
						$( '#moderncart-slide-out-modal' ).html(
							response.content
						);
					}

					ModernCartFrontendScripts.refreshFloating( e );
					ModernCartFrontendScripts.showNotice();
					ModernCartFrontendScripts.afterAjaxAction();
					current.removeClass( 'moderncart-loading' );
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
				},
			} );
		},

		/**
		 * Update product item
		 *
		 * @since 1.0.0
		 *
		 * @param {Object}  e       Object current prevent default event.
		 * @param {Object}  current Object current mouse event.
		 * @param {boolean} direct  Check direct data update.
		 */
		updateProduct: ( e, current, direct ) => {
			e.preventDefault();
			$( '.moderncart-cart-item-' + current.data( 'key' ) ).append(
				'<div class="moderncart-cart-item-loading"><div class="moderncart-cart-item-spinner"></div></div>'
			);
			let quantity = 0;

			if ( direct ) {
				quantity = parseInt( $( '#q-' + current.data( 'key' ) ).val() );
			} else {
				quantity =
					parseInt( $( '#q-' + current.data( 'key' ) ).val() ) + 1;

				if ( 'down' === current.data( 'action' ) ) {
					quantity =
						parseInt( $( '#q-' + current.data( 'key' ) ).val() ) -
						1;
				}
			}

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_update_cart',
					quantity: isNaN( quantity ) ? 0 : quantity,
					cart_key: current.data( 'key' ),
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
				},
				success( response ) {
					if ( moderncartIsValidResponse( response ) ) {
						$( '#moderncart-slide-out-modal' ).html(
							response.content
						);
					}

					ModernCartFrontendScripts.refreshFloating( e );
					if (
						$(
							'.moderncart-slide-out-notices-wrapper .moderncart-notification'
						).data( 'type' ) === 'error'
					) {
						ModernCartFrontendScripts.showNotice();
					} else {
						const qtyMsg =
							'down' === current.data( 'action' )
								? (
										moderncart_ajax_object.i18n
											?.quantity_decreased ||
										'Quantity decreased, current quantity %d'
								  ).replace( '%d', quantity )
								: (
										moderncart_ajax_object.i18n
											?.quantity_increased ||
										'Quantity increased, current quantity %d'
								  ).replace( '%d', quantity );
						$( '#live-region' ).text( qtyMsg );
					}
					$(
						'.moderncart-cart-item-' +
							current.data( 'key' ) +
							' .moderncart-cart-item-loading'
					).remove();

					if ( quantity === 0 ) {
						$(
							'.moderncart-cart-item-' + current.data( 'key' )
						).remove();
					}

					ModernCartFrontendScripts.afterAjaxAction();
					ModernCartFrontendScripts.triggerRefetchFragements();
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
				},
			} );
		},

		/**
		 * Show slide out cart
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e Object current prevent default event.
		 */
		showSlideOut: ( e ) => {
			e.preventDefault();

			const moderncartSlideOutModal = $( '#moderncart-slide-out-modal' );

			if ( moderncartSlideOutModal.hasClass( 'moderncart-show' ) ) {
				return;
			}

			$( 'body' ).append(
				'<div class="moderncart-modal-backdrop"></div>'
			);
			$( 'html' ).addClass( 'moderncart-trigger-open' );
			moderncartSlideOutModal.slideToggle();
			moderncartSlideOutModal.addClass( 'moderncart-show' );
			moderncartSlideOutModal.attr( 'aria-hidden', 'false' );
			$( '.moderncart-floating-cart-button' ).attr(
				'aria-expanded',
				'true'
			);
			$( '#live-region' ).text(
				moderncart_ajax_object.i18n?.cart_opened || 'Cart is opened'
			);
			ModernCartFrontendScripts.displayCartItemImages();
			ModernCartFrontendScripts.trapCartNavigation();
		},

		shouldAutoOpenCartDrawer() {
			switch ( moderncart_ajax_object.cart_auto_open_behavior ) {
				case 'never-auto-open-cart':
					// Never auto-open.
					return false;

				case 'show-cart-on-first-add':
					// For ajax disabled case.
					if ( moderncart_ajax_object.disable_ajax_add_to_cart ) {
						// Use count=1 since cart count is realtime without ajax.
						if (
							$( '#moderncart-slide-out .moderncart-cart-item' )
								.length > 1
						) {
							// Prevent auto-opening if cart has more than 1 item.
							return false;
						}
						return true;
					}
					// For ajax enabled case .
					// Use count=0 since cart count lags due to ajax.
					if (
						$( '#moderncart-slide-out .moderncart-cart-item' )
							.length > 0
					) {
						// Prevent auto-opening if cart has any items.
						return false;
					}
					return true;

				default:
					// Always auto-open.
					return true;
			}
		},

		/**
		 * Hide slide out cart
		 *
		 * @since 1.0.0
		 */
		hideSlideOut: () => {
			$( '.moderncart-modal-backdrop' ).remove();
			const moderncartSlideOutModal = $( '#moderncart-slide-out-modal' );
			$( 'html' ).removeClass( 'moderncart-trigger-open' );
			moderncartSlideOutModal.removeClass( 'moderncart-show' );
			moderncartSlideOutModal.attr( 'aria-hidden', 'true' );
			$( '.moderncart-floating-cart-button' ).attr(
				'aria-expanded',
				'false'
			);
			$( '#live-region' ).text(
				moderncart_ajax_object.i18n?.cart_closed || 'Cart is closed'
			);
			setTimeout( () => {
				moderncartSlideOutModal.slideToggle();
			}, moderncart_ajax_object.animation_speed );
			$( 'body' ).trigger( 'update_checkout' );
			moderncartSlideOutModal.off( 'keydown.trapNavigation' );
		},

		/**
		 * Refresh slideout and floating boths
		 *
		 * @since 1.0.0
		 *
		 * @param {Object}  e            Object current prevent default event.
		 * @param {boolean} reloadWindow Check if need reload full cart window.
		 * @param {boolean} notice       Check if need to show notice.
		 */
		refreshSlideOutFloating: ( e, reloadWindow = true, notice = false ) => {
			if (
				$( '#moderncart-slide-out-modal' ).hasClass(
					'moderncart-show'
				) ||
				'never-auto-open-cart' ===
					moderncart_ajax_object.cart_auto_open_behavior
			) {
				reloadWindow = false;
			}

			// Prevent auto-opening cart if behavior is 'show-cart-on-first-add' and cart already has multiple items.
			// We are counting greater than 0 because the count is late due to ajax call. For the realtime count, it will be greater than 1.
			if (
				'show-cart-on-first-add' ===
					moderncart_ajax_object.cart_auto_open_behavior &&
				$( '#moderncart-slide-out .moderncart-cart-item' ).length > 0
			) {
				reloadWindow = false;
			}

			ModernCartFrontendScripts.refreshSlideOut(
				e,
				reloadWindow,
				notice
			);
			ModernCartFrontendScripts.refreshFloating( e );
		},

		/**
		 * Show general error
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e Object current prevent default event.
		 */
		showGeneralError: ( e ) => {
			e.preventDefault();
		},

		/**
		 * Before ajax actions
		 *
		 * @since 1.0.0
		 */
		beforeAjaxAction: () => {
			$( 'body' ).css( 'cursor', 'progress' );
		},

		/**
		 * After ajax actions
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Object current prevent default event.
		 */
		afterAjaxAction: ( event = null ) => {
			$( 'body' ).css( 'cursor', '' );
			$( '#moderncart-slide-out' ).focus();
			ModernCartFrontendScripts.couponFieldAccordionState();
			moderncartReloadSliderScripts();
			moderncartInitEmptyCartProductRecommendation();
			ModernCartFrontendScripts.displayCartItemImages();
			$( '.moderncart-cart-item-quantity .quantity__button--up' ).html(
				'<svg width="12" height="12" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#000000" class="bi bi-plus-lg" aria-hidden="true" focusable="false"><path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/> </svg>'
			);
			$( '.moderncart-cart-item-quantity .quantity__button--down' ).html(
				'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"> <path d="M6 12L18 12" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
			);

			// Show "Added to cart" notification if auto-open is not set to always and event is 'added_to_cart'
			if ( 'added_to_cart' === event?.type ) {
				ModernCartFrontendScripts.notifyAddedToCart();
			}
		},

		/**
		 * After ajax actions
		 *
		 * @since 1.0.0
		 */
		showNotice: () => {
			$( '.moderncart-slide-out-notices-wrapper' ).show();

			setTimeout( function () {
				$( '.moderncart-slide-out-notices-wrapper' ).hide();
			}, 3000 );
		},

		notifyAddedToCart() {
			// Show "Added to cart" notification if auto-open is not set to always and event is 'added_to_cart'
			if (
				'always-auto-open-cart' !==
				moderncart_ajax_object.cart_auto_open_behavior
			) {
				// Show "Added to cart" notification with fade in/out, and clear any previous timeout to avoid stacking
				if ( ModernCartFrontendScripts?.notificationTimeout ) {
					clearTimeout(
						ModernCartFrontendScripts.notificationTimeout
					);
				}
				ModernCartFrontendScripts.notificationTimeout = setTimeout(
					() => {
						$( '.moderncart-add-to-cart-notification' )
							.fadeIn( 1000 )
							.delay( 1000 )
							.fadeOut( 2000 );
					},
					1000
				);
			}
		},

		/**
		 * Refresh slide out cart data
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e       Object current prevent default event.
		 * @param {Object} current Object current mouse event.
		 */
		removeProduct: ( e, current ) => {
			e.preventDefault();
			$( '.moderncart-cart-item-' + current.data( 'key' ) ).append(
				'<div class="moderncart-cart-item-loading"><div class="moderncart-cart-item-spinner"></div></div>'
			);

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_remove_product',
					cart_key: current.data( 'key' ),
					notice_action: true,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
				},
				success() {
					$(
						'.moderncart-cart-item-' + current.data( 'key' )
					).remove();
					ModernCartFrontendScripts.refreshSlideOutFloating(
						e,
						false,
						true
					);
					if (
						$(
							'.moderncart-slide-out-notices-wrapper .moderncart-notification'
						).data( 'type' ) === 'error'
					) {
						ModernCartFrontendScripts.showNotice();
					}
					ModernCartFrontendScripts.afterAjaxAction();
					ModernCartFrontendScripts.triggerRefetchFragements();
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
					ModernCartFrontendScripts.afterAjaxAction();
				},
			} );
		},

		/**
		 * Refresh slide out cart data
		 *
		 * @since 1.0.0
		 *
		 * @param {Object}  e            Object current prevent default event.
		 * @param {boolean} reloadWindow Check if need reload full cart window.
		 * @param {boolean} noticeAction Check if need to show notice.
		 */
		refreshSlideOut: ( e, reloadWindow = false, noticeAction = false ) => {
			e.preventDefault();

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_refresh_slide_out_cart',
					notice_action: noticeAction,
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
					_: Date.now(),
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
				},
				success( response ) {
					if ( moderncartIsValidResponse( response ) ) {
						$( '#moderncart-slide-out-modal' ).html(
							response.content
						);
					}

					if ( reloadWindow ) {
						ModernCartFrontendScripts.showSlideOut( e );
					}

					if (
						noticeAction &&
						$(
							'.moderncart-slide-out-notices-wrapper .moderncart-notification'
						).data( 'type' ) === 'error'
					) {
						ModernCartFrontendScripts.showNotice();
					}
					ModernCartFrontendScripts.afterAjaxAction( e );
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
				},
			} );
		},

		/**
		 * Refresh floating cart
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e Object current prevent default event.
		 */
		refreshFloating: ( e ) => {
			e.preventDefault();

			$.ajax( {
				type: 'post',
				dataType: 'json',
				url: moderncart_ajax_object.ajax_url,
				data: {
					action: 'moderncart_refresh_floating_cart',
					moderncart_nonce: moderncart_ajax_object.ajax_nonce,
					_: Date.now(),
				},
				beforeSend: () => {
					ModernCartFrontendScripts.beforeAjaxAction();
				},
				success( response ) {
					if ( moderncartIsValidResponse( response ) ) {
						$( '#moderncart-floating-cart' ).html(
							response.content
						);
					}
					if ( response.hide_if_empty ) {
						if ( response.cart_count > 0 ) {
							$( '#moderncart-floating-cart' ).removeClass(
								'moderncart-floating-cart-empty'
							);
						} else {
							$( '#moderncart-floating-cart' ).addClass(
								'moderncart-floating-cart-empty'
							);
						}
					}
					ModernCartFrontendScripts.afterAjaxAction();
				},
				error() {
					ModernCartFrontendScripts.showGeneralError( e );
				},
			} );
		},

		/**
		 * Trigger the WooCommerce fragment refresh event.
		 *
		 * @since 1.0.1
		 */
		triggerRefetchFragements: () => {
			jQuery( document.body ).trigger( 'wc_fragment_refresh' );
		},

		/**
		 * Trap navigation inside cart when opened.
		 *
		 * @since 1.0.2
		 */
		trapCartNavigation: () => {
			const container = $( '#moderncart-slide-out-modal' );
			const focusable = container.find( `
				a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]),
				[tabindex]:not([tabindex="-1"])
			` );

			const first = focusable.first();
			const last = focusable.last();

			first.focus();

			container.on( 'keydown.trapNavigation', function ( e ) {
				const activeElement =
					container[ 0 ].ownerDocument.activeElement;
				if ( e.key === 'Tab' ) {
					if ( e.shiftKey ) {
						if ( activeElement === first[ 0 ] ) {
							e.preventDefault();
							last.focus();
						}
					} else if ( activeElement === last[ 0 ] ) {
						e.preventDefault();
						first.focus();
					}
				}
			} );
		},

		/**
		 * Show recommendations slide
		 *
		 * @since 1.0.8
		 */
		showRecommendationsSlide: () => {
			const slideOut = $( '#moderncart-slide-out' );
			const recommendationsSlide = $(
				'#moderncart-recommendations-slide'
			);

			slideOut.addClass( 'moderncart-slide-active' );
			recommendationsSlide.addClass( 'moderncart-slide-visible' );

			// Focus on the back button for accessibility
			recommendationsSlide.find( '.moderncart-slide-back' ).focus();

			$( '#live-region' ).text(
				moderncart_ajax_object.i18n?.recommendations_opened ||
					'Recommendations opened'
			);
		},

		/**
		 * Hide recommendations slide
		 *
		 * @since 1.0.8
		 */
		hideRecommendationsSlide: () => {
			const slideOut = $( '#moderncart-slide-out' );
			const recommendationsSlide = $(
				'#moderncart-recommendations-slide'
			);

			slideOut.removeClass( 'moderncart-slide-active' );
			recommendationsSlide.removeClass( 'moderncart-slide-visible' );

			// Focus back on the recommendations button
			$( '.moderncart-recommendations-button' ).focus();

			$( '#live-region' ).text(
				moderncart_ajax_object.i18n?.recommendations_closed ||
					'Recommendations closed'
			);
		},
	};

	$( function () {
		localStorage.removeItem( 'couponForm' ); // Reset localStorage value to settings value on first initialization.
		ModernCartFrontendScripts.init();
	} );

	// Handle single product page add to cart with cart drawer auto-open
	// This must run AFTER page load to properly check localStorage and cart state
	window.addEventListener( 'load', function ( e ) {
		// Check if AJAX add to cart is disabled.
		if ( moderncart_ajax_object.disable_ajax_add_to_cart ) {
			// Check if single add to cart was clicked and cart drawer should auto-open.
			if (
				'yes' ===
					localStorage.getItem(
						'moderncart_single_add_to_cart_clicked'
					) &&
				ModernCartFrontendScripts.shouldAutoOpenCartDrawer()
			) {
				// Show slide out cart after a small delay.
				setTimeout( function () {
					ModernCartFrontendScripts.showSlideOut( e );
				}, 300 );
			}
			// Clear the localStorage flag.
			localStorage.removeItem( 'moderncart_single_add_to_cart_clicked' );
		}
	} );

	document.addEventListener( 'DOMContentLoaded', function () {
		moderncartReloadSliderScripts();
		moderncartInitEmptyCartProductRecommendation();
	} );

	function moderncartInitEmptyCartProductRecommendation() {
		if ( 'disabled' === moderncart_ajax_object.empty_cart_recommendation ) {
			return;
		}

		const productCount = 1;

		const splideElement = document.querySelector(
			'.moderncart-slide-out-empty-cart-recommendations .moderncart-empty-cart-recommendation-slider'
		);
		if ( splideElement ) {
			const splide = new Splide( splideElement, {
				arrows: true,
				drag: 'free',
				snap: true,
				perPage: productCount,
				direction:
					document.documentElement.getAttribute( 'dir' ) === 'rtl'
						? 'rtl'
						: 'ltr',
			} ).mount();
			const slideCount = splide.length;

			const prevArrow = splide.root.querySelector(
				'.splide__arrow--prev'
			);
			const nextArrow = splide.root.querySelector(
				'.splide__arrow--next'
			);

			prevArrow.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
				<path d="M10.6 6L9.40002 7L14 12L9.40002 17L10.6 18L16 12L10.6 6Z" fill="#111827"/>
			</svg>`;

			nextArrow.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
				<path d="M10.6 6L9.40002 7L14 12L9.40002 17L10.6 18L16 12L10.6 6Z" fill="#111827"/>
			</svg>`;

			// Enable arrows only if the slide count is greater product count
			if ( slideCount > productCount ) {
				prevArrow.style.display = 'block';
				nextArrow.style.display = 'block';
			} else {
				prevArrow.style.display = 'none';
				nextArrow.style.display = 'none';
			}
		}
	}

	function moderncartReloadSliderScripts() {
		if (
			'disabled' === moderncart_ajax_object.recommendation_types ||
			'style1' !== moderncart_ajax_object.recommendation_list_style
		) {
			return;
		}

		const productCount = $( 'body' ).width() < 500 ? 2 : 3;
		const splideElement = document.querySelector(
			'.moderncart-slide-out-recommendations .moderncart-splide'
		);
		if ( splideElement ) {
			const splide = new Splide( splideElement, {
				arrows: true,
				drag: 'free',
				snap: true,
				perPage: productCount,
				direction:
					document.documentElement.getAttribute( 'dir' ) === 'rtl'
						? 'rtl'
						: 'ltr',
			} ).mount();
			const slideCount = splide.length;

			const prevArrow = splide.root.querySelector(
				'.splide__arrow--prev'
			);
			const nextArrow = splide.root.querySelector(
				'.splide__arrow--next'
			);

			prevArrow.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
				<path d="M10.6 6L9.40002 7L14 12L9.40002 17L10.6 18L16 12L10.6 6Z" fill="#111827"/>
			</svg>`;

			nextArrow.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
				<path d="M10.6 6L9.40002 7L14 12L9.40002 17L10.6 18L16 12L10.6 6Z" fill="#111827"/>
			</svg>`;

			// Enable arrows only if the slide count is greater product count
			if ( slideCount > productCount ) {
				prevArrow.style.display = 'block';
				nextArrow.style.display = 'block';
			} else {
				prevArrow.style.display = 'none';
				nextArrow.style.display = 'none';
			}
		}
	}

	function moderncartDebounce( wait, func, immediate ) {
		let timeout;

		return function () {
			const context = this;
			const args = arguments;

			const later = function () {
				timeout = null;
				if ( ! immediate ) {
					func.apply( context, args );
				}
			};

			const callNow = immediate && ! timeout;

			clearTimeout( timeout );

			timeout = setTimeout( later, wait );

			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	/**
	 * Process form data and convert it to a structured object
	 *
	 * @param {FormData} formData - The FormData object to process
	 * @return {Object} The processed form data
	 */
	function moderncartProcessFormData( formData ) {
		const result = {};

		// First pass: collect all form fields
		for ( const [ key, value ] of formData.entries() ) {
			if ( key.includes( '[' ) ) {
				moderncartProcessArrayField( key, value, result );
			} else {
				result[ key ] = value;
			}
		}

		return result;
	}

	/**
	 * Process a form field with array notation
	 *
	 * Converts bracket-notation keys like "name[0][field]" into nested objects.
	 *
	 * @param {string} key    - The field key (e.g. "recipients[0][email]")
	 * @param {string} value  - The field value
	 * @param {Object} result - The result object to store the processed data
	 */
	function moderncartProcessArrayField( key, value, result ) {
		// Split the key into separate parts: base name and each bracketed segment.
		const parts = [];
		const regex = /([^\[\]]+)|\[(.*?)\]/g;
		let match;
		while ( ( match = regex.exec( key ) ) !== null ) {
			if ( match[ 1 ] !== undefined ) {
				parts.push( match[ 1 ] );
			} else if ( match[ 2 ] !== undefined ) {
				parts.push( match[ 2 ] );
			}
		}

		if ( ! parts.length ) {
			return;
		}

		let current = result;
		for ( let i = 0; i < parts.length; i++ ) {
			const part = parts[ i ];
			const isLast = i === parts.length - 1;

			if ( isLast ) {
				if ( part === '' ) {
					// Empty bracket "[]" means array push.
					if ( ! Array.isArray( current ) ) {
						break;
					}
					current.push( value );
				} else {
					current[ part ] = value;
				}
			} else {
				// Determine what the next container should be.
				const nextPart = parts[ i + 1 ];

				if ( part === '' ) {
					// Empty bracket in the middle — push into array.
					if ( ! Array.isArray( current ) ) {
						break;
					}
					if (
						! current.length ||
						typeof current[ current.length - 1 ] !== 'object'
					) {
						current.push( {} );
					}
					current = current[ current.length - 1 ];
				} else if ( ! ( part in current ) ) {
					// Create the next level: array if next part is "" or numeric, object otherwise.
					current[ part ] =
						nextPart === '' ||
						( nextPart !== undefined && /^\d+$/.test( nextPart ) )
							? []
							: {};
					current = current[ part ];
				} else {
					current = current[ part ];
				}
			}
		}
	}
} )( jQuery );
