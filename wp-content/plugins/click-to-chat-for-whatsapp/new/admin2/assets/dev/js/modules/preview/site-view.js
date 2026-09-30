/**
 * Site View Preview
 *
 * Loads the site's front page in an iframe to display the live unsaved widget
 * over the actual site layout, styles, and colors.
 */
import { log, safeUrl } from '../core/Utils.js';

// Injected into the frame to hide the live chat widget and admin bar.
const FRAME_CSS = [
	'#ht-ctc-chat,.ht-ctc-chat{display:none !important;}',
	'#wpadminbar{display:none !important;}',
	'html{margin-top:0 !important;scroll-behavior:auto !important;}',
].join( '' );

const FRAME_STYLE_ID = 'ctc-site-view-style';
const MOBILE_VIEWPORT_WIDTH = 390;
const NOTE_DURATION = 8000;

const DEVICE_OPTIONS = [
	{
		value: 'desktop',
		text: 'Desktop',
		icon: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
			'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
			'<rect x="2" y="3" width="20" height="14" rx="2"></rect>' +
			'<line x1="8" y1="21" x2="16" y2="21"></line>' +
			'<line x1="12" y1="17" x2="12" y2="21"></line></svg>',
	},
	{
		value: 'mobile',
		text: 'Mobile',
		icon: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
			'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
			'<rect x="5" y="2" width="14" height="20" rx="2"></rect>' +
			'<line x1="12" y1="18" x2="12.01" y2="18"></line></svg>',
	},
];

export default class SiteView {

	/**
	 * @param {Object}   options
	 * @param {string}   options.url       Front page URL to load.
	 * @param {Function} [options.onOpen]  Called once overlay opens.
	 * @param {Function} [options.onClose] Called after teardown.
	 */
	constructor ( { url, onOpen, onClose } ) {
		this.url = url;
		this.onOpen = onOpen;
		this.onClose = onClose;
		this.root = null;
		this.viewport = null;
		this.frame = null;
		this.loading = null;
		this.noteText = null;
		this.noteTimer = 0;
		this.frameWarning = '';
		this.previewNote = '';
		this.dismissedNote = '';
		this.device = 'desktop';
		this.desktopWidth = null;
		this.message = null;
		this.expandBtn = null;
		this.isOpen = false;
		this.isExpanded = false;
		this.isDragging = false;
		this.dragStartX = 0;
		this.dragStartY = 0;
		this.initialLeft = 0;
		this.initialTop = 0;
		this.isResizing = false;
		this.resizeStartX = 0;
		this.resizeStartY = 0;
		this.initialWidth = 0;
		this.initialHeight = 0;
		this.savedPos = null;
		this.bar = null;
		this.returnFocusTo = null;

		this.onKeydown = ( event ) => {
			if ( event.key === 'Escape' ) { this.close(); }
		};

		this.onPointerDown = ( event ) => this.startDrag( event );
		this.onPointerMove = ( event ) => this.onDrag( event );
		this.onPointerUp = ( event ) => this.stopDrag( event );
	}

	open () {
		if ( this.isOpen ) { return; }
		if ( ! this.url ) {
			log( 'SiteView', 'No site URL configured' );
			return;
		}

		if ( ! this.root ) {
			this.build();
		} else {
			this.root.classList.remove( 'is-idle' );
		}

		this.isOpen = true;
		if ( this.isExpanded ) {
			document.body.classList.add( 'ctc-site-view-open' );
		}
		document.addEventListener( 'keydown', this.onKeydown );
		this.onOpen?.();

		this.returnFocusTo = document.activeElement;
		this.root.focus();
	}

	close () {
		if ( ! this.isOpen ) { return; }

		this.stopDrag();
		this.stopResize();

		window.clearTimeout( this.noteTimer );
		document.removeEventListener( 'keydown', this.onKeydown );
		document.body.classList.remove( 'ctc-site-view-open' );

		const returnTo = this.root?.contains( document.activeElement ) ? this.returnFocusTo : null;

		this.root?.classList.add( 'is-idle' );

		if ( returnTo && returnTo.isConnected && returnTo.focus ) { returnTo.focus(); }
		this.returnFocusTo = null;

		this.isOpen = false;
		this.onClose?.();
	}

	build () {
		this.root = document.createElement( 'div' );
		this.root.className = 'ctc-site-view';
		this.root.setAttribute( 'role', 'dialog' );
		this.root.setAttribute( 'aria-label', 'Preview on your site' );
		this.root.tabIndex = -1;

		this.viewport = document.createElement( 'div' );
		this.viewport.className = 'ctc-site-view-viewport';

		this.frame = document.createElement( 'iframe' );
		this.frame.className = 'ctc-site-view-frame';
		this.frame.title = 'Your site';

		this.frame.addEventListener( 'load', () => {
			this.setLoading( false );
			this.prepareFrame();
		} );

		this.frame.addEventListener( 'error', () => this.setLoading( false ) );
		this.frame.src = safeUrl( this.url );

		this.loading = document.createElement( 'div' );
		this.loading.className = 'ctc-site-view-loading';
		this.loading.setAttribute( 'role', 'status' );
		this.loading.innerHTML =
			'<span class="ctc-site-view-spinner" aria-hidden="true"></span>' +
			'<span>Loading your site…</span>';

		this.message = document.createElement( 'div' );
		this.message.className = 'ctc-site-view-note';
		this.message.setAttribute( 'role', 'status' );

		this.noteText = document.createElement( 'span' );

		const noteClose = document.createElement( 'button' );
		noteClose.type = 'button';
		noteClose.className = 'ctc-site-view-note-close';
		noteClose.title = 'Dismiss';
		noteClose.setAttribute( 'aria-label', 'Dismiss this note' );
		noteClose.textContent = '\u00d7';
		noteClose.addEventListener( 'mousedown', ( event ) => event.stopPropagation() );
		noteClose.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			event.stopPropagation();

			this.dismissedNote = this.frameWarning || this.previewNote || '';
			this.frameWarning = '';
			this.previewNote = '';
			this.setMessage( '' );
		} );

		this.message.append( this.noteText, noteClose );

		this.viewport.appendChild( this.frame );
		this.viewport.appendChild( this.loading );
		this.viewport.appendChild( this.message );
		this.setLoading( true );

		this.root.appendChild( this.buildBar() );
		this.root.appendChild( this.viewport );

		// 8-directional resize hitboxes around all 4 sides and 4 corners
		const directions = [ 'n', 's', 'e', 'w', 'nw', 'ne', 'sw', 'se' ];
		directions.forEach( ( dir ) => {
			const edge = document.createElement( 'div' );
			edge.className = `ctc-site-view-edge ctc-edge-${dir}`;
			edge.addEventListener( 'mousedown', ( event ) => this.startResize( event, dir ) );
			edge.addEventListener( 'touchstart', ( event ) => this.startResize( event, dir ), { passive: true } );
			this.root.appendChild( edge );
		} );

		document.body.appendChild( this.root );
		this.setDevice( this.device );
	}

	/**
	 * Gets the framed document, or null if unreachable (e.g. cross-origin).
	 *
	 * @returns {Document|null}
	 */
	frameDocument () {
		try {
			return this.frame?.contentDocument || null;
		} catch ( error ) {
			log( 'SiteView', 'Frame not reachable', error );
			return null;
		}
	}

	/**
	 * Prepares the framed site document (injects hiding styles and checks scrollbar/widget).
	 */
	prepareFrame () {
		const doc = this.frameDocument();

		if ( ! doc || ! doc.head ) {
			this.setFrameWarning( 'Your site loads from a different address, so its own widget is still showing below the preview.' );
			return;
		}

		if ( ! doc.getElementById( FRAME_STYLE_ID ) ) {
			const style = doc.createElement( 'style' );
			style.id = FRAME_STYLE_ID;
			style.textContent = FRAME_CSS;
			doc.head.appendChild( style );
		}

		// Pass framed scrollbar width to CSS so preview aligns with page.
		const view = doc.defaultView;
		const scrollbar = Math.max( 0, view.innerWidth - doc.documentElement.clientWidth );
		this.root.style.setProperty( '--ctc-frame-scrollbar', `${scrollbar}px` );

		const hasWidget = Boolean( doc.querySelector( '#ht-ctc-chat, .ht-ctc-chat' ) );
		this.setFrameWarning( hasWidget ? '' : 'This page does not show the widget itself — check your Display settings, or a coming-soon plugin.' );
	}

	/**
	 * Sets the simulated device for the site view.
	 *
	 * @param {string} device 'desktop' | 'mobile'
	 */
	setDevice ( device ) {
		this.device = device === 'mobile' ? 'mobile' : 'desktop';
		this.root?.classList.toggle( 'is-mobile', this.device === 'mobile' );
		this.applyDeviceWidth();
	}

	/**
	 * Applies device-specific width to the window or viewport.
	 */
	applyDeviceWidth () {
		if ( ! this.root || this.isExpanded ) { return; }

		if ( this.device === 'mobile' ) {
			if ( null === this.desktopWidth ) {
				const current = this.root.style.width || '';
				this.desktopWidth = current === `${MOBILE_VIEWPORT_WIDTH}px` ? '' : current;
			}
			this.root.style.width = `${MOBILE_VIEWPORT_WIDTH}px`;
			return;
		}

		if ( null !== this.desktopWidth ) {
			this.root.style.width = this.desktopWidth;
			this.desktopWidth = null;
		}
	}

	/**
	 * Sets the loading state.
	 *
	 * @param {boolean} loading Whether iframe is loading.
	 */
	setLoading ( loading ) {
		this.root?.classList.toggle( 'is-loading', loading );
	}

	/**
	 * Shows or clears a note banner at the top of the view.
	 *
	 * @param {string} text Note text, or '' to dismiss.
	 */
	setMessage ( text ) {
		if ( ! this.message || ! this.noteText ) { return; }

		window.clearTimeout( this.noteTimer );
		this.noteText.textContent = text;
		this.message.classList.toggle( 'is-visible', text !== '' );

		if ( text !== '' && this.frameWarning === '' ) {
			this.noteTimer = window.setTimeout( () => {
				this.message?.classList.remove( 'is-visible' );
			}, NOTE_DURATION );
		}
	}

	/**
	 * Sets a persistent frame warning message.
	 *
	 * @param {string} text Warning text.
	 */
	setFrameWarning ( text ) {
		this.frameWarning = text || '';
		this.updateMessage();
	}

	/**
	 * Sets a preview note forwarded from PreviewManager.
	 *
	 * @param {string} text Note text.
	 */
	setPreviewNote ( text ) {
		this.previewNote = text || '';
		this.updateMessage();
	}

	/**
	 * Updates the displayed message (frame warnings take priority over preview notes).
	 */
	updateMessage () {
		const text = this.frameWarning || this.previewNote || '';

		if ( text !== '' && text === this.dismissedNote ) {
			this.setMessage( '' );
			return;
		}

		if ( text !== '' ) { this.dismissedNote = ''; }

		this.setMessage( text );
	}

	toggleExpand () {
		this.isExpanded = ! this.isExpanded;
		if ( ! this.root ) { return; }

		this.root.classList.toggle( 'is-expanded', this.isExpanded );

		if ( this.isExpanded ) {
			this.savedPos = {
				left: this.root.style.left,
				top: this.root.style.top,
				right: this.root.style.right,
				bottom: this.root.style.bottom,
				width: this.root.style.width,
				height: this.root.style.height,
			};
			this.root.style.left = '';
			this.root.style.top = '';
			this.root.style.right = '';
			this.root.style.bottom = '';
			this.root.style.width = '';
			this.root.style.height = '';
			document.body.classList.add( 'ctc-site-view-open' );
		} else {
			if ( this.savedPos ) {
				this.root.style.left = this.savedPos.left;
				this.root.style.top = this.savedPos.top;
				this.root.style.right = this.savedPos.right;
				this.root.style.bottom = this.savedPos.bottom;
				this.root.style.width = this.savedPos.width;
				this.root.style.height = this.savedPos.height;
			}
			document.body.classList.remove( 'ctc-site-view-open' );

			this.desktopWidth = null;
			this.applyDeviceWidth();
		}

		this.updateExpandButton();
	}

	updateExpandButton () {
		if ( ! this.expandBtn ) { return; }
		const expandIcon = this.expandBtn.querySelector( '[data-icon="expand"]' );
		const collapseIcon = this.expandBtn.querySelector( '[data-icon="collapse"]' );
		const label = this.expandBtn.querySelector( 'span' );
		if ( expandIcon ) { expandIcon.style.display = this.isExpanded ? 'none' : ''; }
		if ( collapseIcon ) { collapseIcon.style.display = this.isExpanded ? '' : 'none'; }
		if ( label ) { label.textContent = this.isExpanded ? 'Collapse' : 'Expand'; }

		const hint = this.isExpanded ? 'Collapse to floating window' : 'Expand to full screen';
		this.expandBtn.setAttribute( 'title', hint );
		this.expandBtn.setAttribute( 'aria-label', hint );
	}

	startResize ( event, direction = 'se' ) {
		if ( this.isExpanded ) { return; }
		event.stopPropagation();

		const clientX = event.touches ? event.touches[ 0 ].clientX : event.clientX;
		const clientY = event.touches ? event.touches[ 0 ].clientY : event.clientY;

		const rect = this.root.getBoundingClientRect();
		this.isResizing = true;
		this.resizeDir = direction;
		this.resizeStartX = clientX;
		this.resizeStartY = clientY;
		this.initialRect = {
			left: rect.left,
			top: rect.top,
			width: rect.width,
			height: rect.height,
		};

		this.root.classList.add( 'is-resizing' );
		if ( this.frame ) {
			this.frame.style.pointerEvents = 'none';
		}

		this.onResizeMove = ( event ) => this.onResize( event );
		this.onResizeEnd = () => this.stopResize();

		window.addEventListener( 'mousemove', this.onResizeMove );
		window.addEventListener( 'mouseup', this.onResizeEnd );
		window.addEventListener( 'touchmove', this.onResizeMove, { passive: false } );
		window.addEventListener( 'touchend', this.onResizeEnd );
	}

	onResize ( event ) {
		if ( ! this.isResizing || ! this.initialRect ) { return; }
		if ( event.cancelable ) {
			event.preventDefault();
		}

		const clientX = event.touches ? event.touches[ 0 ].clientX : event.clientX;
		const clientY = event.touches ? event.touches[ 0 ].clientY : event.clientY;

		const deltaX = clientX - this.resizeStartX;
		const deltaY = clientY - this.resizeStartY;
		const dir = this.resizeDir || 'se';

		let newWidth = this.initialRect.width;
		let newHeight = this.initialRect.height;
		let newLeft = this.initialRect.left;
		let newTop = this.initialRect.top;

		// Width handling (East / West)
		if ( dir.includes( 'e' ) ) {
			const maxW = window.innerWidth - this.initialRect.left - 10;
			newWidth = Math.max( 300, Math.min( maxW, this.initialRect.width + deltaX ) );
		} else if ( dir.includes( 'w' ) ) {
			const maxW = this.initialRect.left + this.initialRect.width - 10;
			newWidth = Math.max( 300, Math.min( maxW, this.initialRect.width - deltaX ) );
			newLeft = this.initialRect.left + ( this.initialRect.width - newWidth );
		}

		// Height handling (South / North)
		if ( dir.includes( 's' ) ) {
			const maxH = window.innerHeight - this.initialRect.top - 10;
			newHeight = Math.max( 300, Math.min( maxH, this.initialRect.height + deltaY ) );
		} else if ( dir.includes( 'n' ) ) {
			const maxH = this.initialRect.top + this.initialRect.height - 10;
			newHeight = Math.max( 300, Math.min( maxH, this.initialRect.height - deltaY ) );
			newTop = this.initialRect.top + ( this.initialRect.height - newHeight );
		}

		this.root.style.width = `${newWidth}px`;
		this.root.style.height = `${newHeight}px`;
		this.root.style.left = `${newLeft}px`;
		this.root.style.top = `${newTop}px`;
		this.root.style.right = 'auto';
		this.root.style.bottom = 'auto';
	}

	stopResize () {
		if ( ! this.isResizing ) { return; }
		this.isResizing = false;
		this.root?.classList.remove( 'is-resizing' );
		if ( this.frame ) {
			this.frame.style.pointerEvents = 'auto';
		}

		if ( this.onResizeMove ) {
			window.removeEventListener( 'mousemove', this.onResizeMove );
			window.removeEventListener( 'touchmove', this.onResizeMove );
		}
		if ( this.onResizeEnd ) {
			window.removeEventListener( 'mouseup', this.onResizeEnd );
			window.removeEventListener( 'touchend', this.onResizeEnd );
		}
	}

	startDrag ( event ) {
		if ( this.isExpanded ) { return; }

		// Ignore drag on interactive controls.
		if ( event.target.closest( 'button, a, input, select' ) ) { return; }

		const clientX = event.touches ? event.touches[ 0 ].clientX : event.clientX;
		const clientY = event.touches ? event.touches[ 0 ].clientY : event.clientY;

		const rect = this.root.getBoundingClientRect();
		this.isDragging = true;
		this.dragStartX = clientX;
		this.dragStartY = clientY;
		this.initialLeft = rect.left;
		this.initialTop = rect.top;

		this.root.classList.add( 'is-dragging' );
		if ( this.frame ) {
			this.frame.style.pointerEvents = 'none';
		}

		window.addEventListener( 'mousemove', this.onPointerMove );
		window.addEventListener( 'mouseup', this.onPointerUp );
		window.addEventListener( 'touchmove', this.onPointerMove, { passive: false } );
		window.addEventListener( 'touchend', this.onPointerUp );
	}

	onDrag ( event ) {
		if ( ! this.isDragging ) { return; }
		if ( event.cancelable ) {
			event.preventDefault();
		}

		const clientX = event.touches ? event.touches[ 0 ].clientX : event.clientX;
		const clientY = event.touches ? event.touches[ 0 ].clientY : event.clientY;

		const deltaX = clientX - this.dragStartX;
		const deltaY = clientY - this.dragStartY;

		let newLeft = this.initialLeft + deltaX;
		let newTop = this.initialTop + deltaY;

		const rect = this.root.getBoundingClientRect();
		const maxLeft = window.innerWidth - rect.width;
		const maxTop = window.innerHeight - rect.height;

		newLeft = Math.max( 0, Math.min( maxLeft, newLeft ) );
		newTop = Math.max( 0, Math.min( maxTop, newTop ) );

		this.root.style.left = `${newLeft}px`;
		this.root.style.top = `${newTop}px`;
		this.root.style.right = 'auto';
		this.root.style.bottom = 'auto';
	}

	stopDrag () {
		if ( ! this.isDragging ) { return; }
		this.isDragging = false;
		this.root?.classList.remove( 'is-dragging' );
		if ( this.frame ) {
			this.frame.style.pointerEvents = 'auto';
		}

		window.removeEventListener( 'mousemove', this.onPointerMove );
		window.removeEventListener( 'mouseup', this.onPointerUp );
		window.removeEventListener( 'touchmove', this.onPointerMove );
		window.removeEventListener( 'touchend', this.onPointerUp );
	}

	/**
	 * Floating toolbar / Drag handle.
	 *
	 * @returns {HTMLElement}
	 */
	buildBar () {
		const bar = document.createElement( 'div' );
		bar.className = 'ctc-site-view-bar';

		const dragGrip = document.createElement( 'span' );
		dragGrip.className = 'ctc-site-view-grip';
		dragGrip.title = 'Drag to move window';
		dragGrip.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="4" r="2"/><circle cx="16" cy="4" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="8" cy="20" r="2"/><circle cx="16" cy="20" r="2"/></svg>';

		const label = document.createElement( 'span' );
		label.className = 'ctc-site-view-label';
		label.textContent = 'Site preview';

		const device = document.createElement( 'div' );
		device.className = 'ctc-site-view-device';
		device.setAttribute( 'role', 'radiogroup' );
		device.setAttribute( 'aria-label', 'Preview device' );
		DEVICE_OPTIONS.forEach( ( { value, text, icon } ) => {
			const option = document.createElement( 'label' );
			option.className = 'ctc-site-view-device-option';
			option.title = `Preview as ${text.toLowerCase()}`;

			const input = document.createElement( 'input' );
			input.type = 'radio';
			input.name = 'ctc-preview-device-siteview';
			input.value = value;
			input.setAttribute( 'data-ctc-device', value );
			input.setAttribute( 'data-ctc-no-track', 'true' );

			const caption = document.createElement( 'span' );
			// eslint-disable-next-line no-unsanitized/property -- Static markup from the DEVICE_OPTIONS constant
			caption.innerHTML = icon;

			const captionText = document.createElement( 'span' );
			captionText.className = 'ctc-site-view-device-text';
			captionText.textContent = text;
			caption.appendChild( captionText );

			option.append( input, caption );
			device.appendChild( option );
		} );

		device.addEventListener( 'mousedown', ( event ) => event.stopPropagation() );
		device.addEventListener( 'touchstart', ( event ) => event.stopPropagation() );

		const openTab = document.createElement( 'a' );
		openTab.className = 'ctc-site-view-link';
		openTab.href = safeUrl( this.url );
		openTab.target = '_blank';
		openTab.rel = 'noopener';
		openTab.title = 'Open your live site in a new tab';
		openTab.innerHTML = '<span>Open site</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
		openTab.addEventListener( 'mousedown', ( event ) => event.stopPropagation() );
		openTab.addEventListener( 'touchstart', ( event ) => event.stopPropagation() );

		this.expandBtn = document.createElement( 'button' );
		this.expandBtn.type = 'button';
		this.expandBtn.className = 'ctc-site-view-expand';
		this.expandBtn.innerHTML = '<svg data-icon="expand" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg><svg data-icon="collapse" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="10" y1="14" x2="3" y2="21"></line></svg><span></span>';
		this.updateExpandButton();
		this.expandBtn.addEventListener( 'mousedown', ( event ) => event.stopPropagation() );
		this.expandBtn.addEventListener( 'touchstart', ( event ) => event.stopPropagation() );
		this.expandBtn.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			event.stopPropagation();
			this.toggleExpand();
		} );

		const close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'ctc-site-view-close';
		close.title = 'Close preview';
		close.setAttribute( 'aria-label', 'Close preview' );
		close.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
		close.addEventListener( 'mousedown', ( event ) => event.stopPropagation() );
		close.addEventListener( 'touchstart', ( event ) => event.stopPropagation() );
		close.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			event.stopPropagation();
			this.close();
		} );

		const spacer = document.createElement( 'span' );
		spacer.className = 'ctc-site-view-spacer';
		spacer.setAttribute( 'aria-hidden', 'true' );

		bar.append(
			dragGrip, label, device, spacer,
			this.expandBtn, openTab, close,
		);

		bar.addEventListener( 'mousedown', this.onPointerDown );
		bar.addEventListener( 'touchstart', this.onPointerDown, { passive: true } );

		this.bar = bar;
		return bar;
	}
}

