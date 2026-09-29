import { dispatch, select } from '@wordpress/data';
import { STORE_NAME } from '@Store/constants';
import domReady from '@wordpress/dom-ready';
import { handleRefreshWithBrokenLinks } from '@/apps/elementor/page-checks';
import './style.scss';

domReady( () => {
	let intervalId = null;
	let attempts = 0;
	const maxAttempts = 20;
	const intervalTime = 3000; // 3 seconds

	const { getMetaboxState, getPageSeoChecks } = select( STORE_NAME );
	intervalId = setInterval( () => {
		if ( surerank_seo_popup?.is_taxonomy ) {
			attempts = maxAttempts;
			clearInterval( intervalId );
			return;
		}
		attempts++;
		const initialized = getMetaboxState();
		const pageChecks = getPageSeoChecks();

		if ( pageChecks.initializing ) {
			dispatch( STORE_NAME ).setPageSeoCheck( 'initializing', false );
		}

		if ( initialized ) {
			handleRefreshWithBrokenLinks();
			clearInterval( intervalId );
		} else if ( attempts >= maxAttempts ) {
			clearInterval( intervalId );
		}
	}, intervalTime );

	document.addEventListener( 'click', ( event ) => {
		if ( event.target.closest( '#wp-admin-bar-surerank-meta-box' ) ) {
			event.preventDefault();
			const { updateModalState } = dispatch( STORE_NAME );
			updateModalState( true );
		}
	} );
} );
