/**
 * Meta Options build.
 */
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PluginMoreMenuItem,
	PluginPostPublishPanel,
	PluginDocumentSettingPanel,
} from '@wordpress/editor';
import {
	useEffect,
	useState,
	useCallback,
	createPortal,
} from '@wordpress/element';
import { Button as WPButton } from '@wordpress/components';
import { STORE_NAME as storeName } from '@Store/constants';
import { SureRankMonoSmallLogo } from '@GlobalComponents/icons';
import PageCheckStatusIndicator from '@AdminComponents/page-check-status-indicator';
import usePageCheckStatus from './hooks/usePageCheckStatus';
import { getTooltipText } from './utils/page-checks-status-tooltip-text';
import { isPageBuilderActive } from '@SeoPopup/components/page-seo-checks/analyzer/utils/page-builder';

// Inject a toolbar button directly into the Gutenberg header via a React portal.
const SureRankToolbarButtonPortal = () => {
	const { updateModalState } = useDispatch( storeName );
	const [ hostEl, setHostEl ] = useState( null );
	const isModalOpen = useSelect( ( select ) => {
		try {
			return !! select( storeName ).getModalState();
		} catch ( error ) {
			return false;
		}
	}, [] );

	// Get page checks status for indicator
	const { status, initializing, counts } = usePageCheckStatus();

	// Stable handler to open the modal; helps avoid unnecessary re-renders.
	const handleOpenModal = useCallback(
		() => updateModalState( true ),
		[ updateModalState ]
	);

	useEffect( () => {
		const findOrCreateHost = () => {
			// 1) Preferred: insert as FIRST item inside the pinned items container.
			const pinned = document.querySelector( '.interface-pinned-items' );
			if ( pinned ) {
				let host = pinned.querySelector( '#surerank-toolbar-portal' );
				if ( ! host ) {
					host = document.createElement( 'div' );
					host.id = 'surerank-toolbar-portal';
					host.className = 'surerank-root';
					if ( pinned.firstChild ) {
						pinned.insertBefore( host, pinned.firstChild );
					} else {
						pinned.appendChild( host );
					}
				} else if ( host.parentElement !== pinned ) {
					// Reparent if necessary to ensure first position in pinned area.
					if ( pinned.firstChild && pinned.firstChild !== host ) {
						pinned.insertBefore( host, pinned.firstChild );
					} else {
						pinned.appendChild( host );
					}
				}
				return host;
			}

			// 2) Fallback: right-side actions/settings area of the header (post and site editor variants).
			const rightArea = document.querySelector(
				[
					'.edit-post-header__settings',
					'.editor-header__actions',
					'.edit-site-header__actions',
				].join( ', ' )
			);
			if ( ! rightArea ) {
				return null;
			}

			let host = rightArea.querySelector( '#surerank-toolbar-portal' );
			if ( ! host ) {
				host = document.createElement( 'div' );
				host.id = 'surerank-toolbar-portal';
				host.className = 'surerank-root';

				// Try to place AFTER the View (responsive) button/dropdown when available.
				const viewCandidates = rightArea.querySelector(
					[
						'.edit-post-post-preview__button',
						'.editor-preview-dropdown',
					].join( ', ' )
				);

				const insertAfter = ( referenceNode, newNode ) => {
					if ( ! referenceNode || ! referenceNode.parentNode ) {
						rightArea.appendChild( newNode );
						return;
					}
					if ( referenceNode.nextSibling ) {
						referenceNode.parentNode.insertBefore(
							newNode,
							referenceNode.nextSibling
						);
					} else {
						referenceNode.parentNode.appendChild( newNode );
					}
				};

				if ( viewCandidates ) {
					const refNode =
						viewCandidates.closest( 'button, div, span' ) ||
						viewCandidates;
					insertAfter( refNode, host );
				} else {
					rightArea.appendChild( host );
				}
			}
			return host;
		};

		let host = findOrCreateHost();
		if ( host ) {
			setHostEl( host );
		}

		// Recreate if the header re-renders.
		const Observer =
			window.MutationObserver ||
			window.WebKitMutationObserver ||
			window.MozMutationObserver;
		if ( Observer ) {
			const observer = new Observer( () => {
				const current = document.getElementById(
					'surerank-toolbar-portal'
				);
				const pinned = document.querySelector(
					'.interface-pinned-items'
				);
				// If missing, recreate. If present but not inside pinned when pinned exists, move it.
				if ( ! current ) {
					host = findOrCreateHost();
					if ( host ) {
						setHostEl( host );
					}
				} else if ( pinned && current.parentElement !== pinned ) {
					if ( pinned.firstChild && pinned.firstChild !== current ) {
						pinned.insertBefore( current, pinned.firstChild );
					} else {
						pinned.appendChild( current );
					}
				}
			} );
			// Observe the header only.
			const editorHeader = document.querySelector(
				'.editor-header, .edit-post-header'
			);
			observer.observe( editorHeader, {
				childList: true,
				subtree: true,
			} );
			return () => observer.disconnect();
		}

		// Fallback: periodic check.
		const interval = window.setInterval( () => {
			const current = document.getElementById(
				'surerank-toolbar-portal'
			);
			if ( ! current ) {
				host = findOrCreateHost();
				if ( host ) {
					setHostEl( host );
				}
			}
		}, 1500 );
		return () => window.clearInterval( interval );
	}, [] );

	if ( ! hostEl ) {
		return null;
	}

	return createPortal(
		<div className="relative">
			<WPButton
				icon={ <SureRankMonoSmallLogo /> }
				label={ getTooltipText( counts ) }
				aria-label={ __( 'Open SureRank Meta Box', 'surerank' ) }
				aria-haspopup="dialog"
				showTooltip
				isPressed={ isModalOpen }
				aria-pressed={ isModalOpen }
				type="button"
				size="compact"
				onClick={ handleOpenModal }
			>
				<PageCheckStatusIndicator
					status={ status }
					errorAndWarnings={ counts.errorAndWarnings }
					initializing={ initializing }
				/>
			</WPButton>
		</div>,
		hostEl
	);
};

// Component to show SEO status message in post-publish panel
const PostPublishSEOMessage = () => {
	const { status, initializing } = usePageCheckStatus();

	if ( initializing ) {
		return null;
	}

	if ( status === 'success' ) {
		return __( 'Your page is live, and well optimized.', 'surerank' );
	}

	return __( 'Your page is live, but not yet SEO optimized.', 'surerank' );
};

const SpectraPageSettingsPopup = () => {
	const { updateModalState } = useDispatch( storeName );
	const { initializing, counts, status } = usePageCheckStatus();

	const MANAGE_SEO_LABEL = __( 'Manage Your SEO', 'surerank' );
	const OPTIMIZE_LABEL = __( 'Optimize Here', 'surerank' );

	// Redner only Gutenberg
	const isNotPageBuilder = ! isPageBuilderActive();

	const handleMenuClick = useCallback( () => {
		// Open the Drawer instead of sidebar
		updateModalState( true );
	}, [ updateModalState ] );

	// Determine button text based on errors and warnings
	const getButtonText = () => {
		if ( status === 'success' || counts.errorAndWarnings === 0 ) {
			return MANAGE_SEO_LABEL;
		}
		return OPTIMIZE_LABEL;
	};

	return (
		<>
			<PluginMoreMenuItem
				onClick={ handleMenuClick }
				icon={ <SureRankMonoSmallLogo /> }
				aria-label={ __( 'Open SureRank Meta Box', 'surerank' ) }
				aria-haspopup="dialog"
			>
				{ __( 'SureRank Meta Box', 'surerank' ) }
			</PluginMoreMenuItem>

			{ /* Post Settings Sidebar Panel - Gutenberg only */ }
			{ isNotPageBuilder && (
				<PluginDocumentSettingPanel
					name="surerank-panel"
					title={ 'SureRank SEO' }
				>
					<WPButton
						variant="primary"
						onClick={ handleMenuClick }
						className="w-fit"
					>
						{ getButtonText() }
					</WPButton>
				</PluginDocumentSettingPanel>
			) }

			{ /* Post-Publish Panel Message - Gutenberg only */ }
			{ isNotPageBuilder && ! initializing && (
				<PluginPostPublishPanel
					title={ null }
					initialOpen={ true }
					className="surerank-root"
				>
					<div className="flex gap-4 flex-col">
						<div className="flex items-center gap-2">
							<h3 className="m-0 text-sm font-semibold">
								{ __( 'Manage your SEO', 'surerank' ) }
							</h3>
						</div>
						<p className="m-0 text-[13px] leading-[1.4] text-[#1e1e1e]">
							<PostPublishSEOMessage />
						</p>
						<WPButton
							variant="primary"
							onClick={ handleMenuClick }
							className="w-fit"
						>
							{ getButtonText() }
						</WPButton>
					</div>
				</PluginPostPublishPanel>
			) }

			{ /* Portal-injected toolbar button */ }
			<SureRankToolbarButtonPortal />
		</>
	);
};
export default SpectraPageSettingsPopup;
