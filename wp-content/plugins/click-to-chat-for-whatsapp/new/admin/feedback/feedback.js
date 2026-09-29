// immediate invoke function to avoid global scope pollution
( function initFeedbackModal () {

	const deactivateBtn = document.querySelector( '#deactivate-click-to-chat-for-whatsapp' );
	const localVars = window.ht_ctc_admin_deactivate_feedback || {};

	var is_mobile = typeof screen.width !== 'undefined' && screen.width > 600 ? 'no' : 'yes';

	if ( is_mobile === 'yes' ) {
		console.log( 'Mobile device detected - skipping feedback modal, ' +
			'letting default deactivate link proceed.' );
		return;
	}

	if ( ! deactivateBtn || ! localVars || Object.keys( localVars ).length === 0 ) {
		console.log( 'Feedback modal: required deactivateBtn or localVars missing - exiting early.' );
		return;
	}

	const deactivateUrl = deactivateBtn.getAttribute( 'href' );
	if ( ! deactivateUrl ) {
		console.log( 'Deactivate URL missing - exit early.' );
		return;
	}

	// Check sessionStorage flag: if set, skip attaching listeners entirely
	try {
		if ( sessionStorage.getItem( 'ht_ctc_feedback_opened' ) === '1' ) {
			console.log( 'Feedback modal already opened this session - skipping modal, ' +
				'letting default deactivate link proceed.' );
			return; // don't attach listeners → default WP behavior
		}
	} catch ( error ) {
		console.warn( 'SessionStorage error - skipping modal as fallback.', error );
		return; // fail-safe: let default deactivate behavior continue
	}

	// If we got here, feedback modal hasn't been opened: attach listeners etc.

	const modal = document.querySelector( '.ht-ctc-deactivate-feedback-modal' );
	const modalContent = document.querySelector( '.ht-ctc-df-modal-content' );
	const closeButton = document.querySelector( '.ht-ctc-df-close' );
	const sendFeedbackButton = document.querySelector( '.ht-ctc-df-send' );
	const feedbackTextarea = document.getElementById( 'ht-ctc-df-textarea' );
	const emailInput = document.getElementById( 'ht-ctc-df-email' );

	if ( ! modal || ! modalContent ) {
		console.log( 'Feedback modal elements missing - exit early.' );
		return;
	}

	// Open modal
	function openModal () {

		// to make sure it opens only once per session - can comment this for testing
		// if (sessionStorage.getItem('ht_ctc_feedback_opened')) {
		//     window.location.href = deactivateUrl;
		//     return;
		// }

		modal.style.display = 'block';
		document.body.style.overflow = 'hidden'; // Prevent background scrolling
		document.addEventListener( 'keydown', escKeyClose );

		try {
			// to make sure it opens only once per session - can comment this for testing
			// sessionStorage.setItem('ht_ctc_feedback_opened', '1');
		} catch ( error ) {
			console.warn( 'Session storage error when setting modal flag:', error );
		}
	}

	// Close modal
	function closeModal () {
		modal.style.display = 'none';
		document.body.style.overflow = '';

		// todo: research with ai.. will it remove the event listener for all instances related..
		document.removeEventListener( 'keydown', escKeyClose );
	}

	// Close modal on Escape key
	function escKeyClose ( event ) {
		try {
			// Check if the key pressed is 'Escape'
			if ( event.key === 'Escape' ) {
				closeModal();
			}
		} catch ( error ) {
			console.warn( 'Error while handling Escape key for modal close:', error );
		}
	}

	// Open modal on deactivate button click
	deactivateBtn.addEventListener( 'click', function handleDeactivateClick ( event ) {
		event.preventDefault();
		openModal();
	} );

	// Close modal on close button click
	if ( closeButton ) {
		closeButton.addEventListener( 'click', function handleCloseClick ( event ) {
			event.preventDefault();
			closeModal();
		} );
	}

	// Close modal when clicking outside modal content
	modal.addEventListener( 'click', function handleOutsideClick ( event ) {
		if ( ! modalContent.contains( event.target ) ) {
			closeModal();
		}
	} );

	// inside clicks to stop propagation (prevent accidental modal close)
	// modalContent.addEventListener('click', function (event) {
	//     event.stopPropagation();
	// });

	// todo: add try catch..

	// Skip & Deactivate button
	const skipButton = document.querySelector( '.ht-ctc-df-skip' );
	if ( skipButton ) {
		skipButton.addEventListener( 'click', function handleSkipClick ( event ) {
			event.preventDefault();

			window.location.href = deactivateUrl;
		} );
	}

	// Feedback submission
	if ( sendFeedbackButton ) {
		sendFeedbackButton.addEventListener( 'click', function handleSendFeedback ( event ) {
			try {
				event.preventDefault();

				const feedback = feedbackTextarea ? feedbackTextarea.value.trim() : '';
				const email = emailInput ? emailInput.value.trim() : '';

				// Use localized variables for ajaxurl and nonce
				const nonce = localVars.nonce || '';

				// ajaxurl - window.ajaxurl || localVars.ajaxurl || '';
				const ajax_url = window.ajaxurl || localVars.ajaxurl || '';

				if ( ! ajax_url || ! nonce ) {
					console.log( 'Ajax URL or nonce is missing. Cannot send feedback.' );

					window.location.href = deactivateUrl;
					return;
				}

				sendFeedbackButton.disabled = true;
				sendFeedbackButton.textContent = 'Deactivating...';

				fetch( ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams( {
						action: 'ht_ctc_deactivate_feedback_details',
						userFeedback: feedback,
						userEmail: email,
						nonce: nonce,
					} ),
				} )
					.then( ( res ) => {
						if ( ! res.ok ) { throw new Error( 'Network response was not ok' ); }
						return res.json();
					} )
					.then( ( data ) => {
						console.log( 'Feedback sent successfully:', data );
					} )
					.catch( ( err ) => {
						console.error( 'fetch catch - Error sending feedback:', err );
					} )
					.finally( () => {
						// This will always run, whether the request succeeded or failed

						window.location.href = deactivateUrl; // or use window.open(deactivateUrl)
					} );
			} catch ( error ) {
				console.error( 'catch: Error in feedback submission:', error );

				// Fallback: redirect to deactivate URL

				window.location.href = deactivateUrl;
			}
		} );
	}
} )();
