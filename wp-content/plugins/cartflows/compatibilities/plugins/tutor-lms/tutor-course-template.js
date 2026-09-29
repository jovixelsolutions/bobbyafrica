( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const select = document.getElementById( 'cartflows_course_template' );

		if ( ! select || ! window.cartflowsTutorData ) {
			return;
		}

		const statusEl = document.getElementById( 'cartflows-template-status' );

		const { ajaxUrl, nonce, courseId } = window.cartflowsTutorData;

		select.addEventListener( 'change', function ( event ) {
			const templateId = event.target.value;

			setStatus( 'saving', 'Saving…' );

			const formData = new FormData();
			formData.append( 'action', 'cartflows_save_tutor_course_template' );
			formData.append( 'course_id', courseId );
			formData.append( 'template_id', templateId );
			formData.append( 'nonce', nonce );

			fetch( ajaxUrl, {
				method: 'POST',
				body: formData,
			} )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Request failed' );
					}
					return response.json();
				} )
				.then( ( data ) => {
					if ( data.success ) {
						setStatus( 'success', 'Saved' );
					} else {
						setStatus( 'error', 'Error saving' );
					}
					clearStatus();
				} )
				.catch( ( error ) => {
					setStatus( 'error', 'Error saving' );
					clearStatus();
					console.error( error );
				} );
		} );

		function setStatus( type, text ) {
			if ( ! statusEl ) {
				return;
			}

			statusEl.textContent = text;
			statusEl.className =
				'cartflows-template-status is-visible is-' + type;
		}

		function clearStatus() {
			if ( ! statusEl ) {
				return;
			}

			setTimeout( () => {
				statusEl.className = 'cartflows-template-status';
				statusEl.textContent = '';
			}, 2000 );
		}
	} );
} )();
