/**
 * Interface Logic
 *
 * Manages the application shell: sidebar, navigation, and panel interactions.
 */
import { getCtcStorageItem, setCtcStorageItem } from '../core/Storage.js';
import { log } from '../core/Utils.js';

export default class Interface {
	static init ( app ) {
		log( 'Interface', 'Initializing...' );
		this.app = app;

		// Deep-link target, held until its tab has actually rendered.
		this.pendingTarget = null;

		// Scroll to deep-link target once the tab has rendered.
		this.app.events?.on( 'tab:changed', ( tabId ) => {
			if ( ! this.pendingTarget ) { return; }

			if ( ! document.getElementById( tabId )?.classList.contains( 'settings-panel' ) ) { return; }

			const target = this.pendingTarget;
			this.pendingTarget = null;
			this.scrollToElement( target );
		} );

		document.addEventListener( 'DOMContentLoaded', () => {
			this.initSidebar();
			this.initNavigation();
			this.initTabs();

			this.app.utils.initConditionalFieldLogic( document );

			this.initHelpIcons();
			this.initGreetingsImage();
			this.initRightSidebar();
		} );
	}

	/**
	 * Sidebar & Toggle Menu Logic
	 */
	static initSidebar () {
		const menuToggle = document.getElementById( 'menu-toggle' );
		const closeSidebar = document.getElementById( 'close-sidebar' );
		const sidebar = document.getElementById( 'sidebar' );
		const settingsToggle = document.getElementById( 'settings-toggle' );
		const settingsDropdown = document.getElementById( 'settings-dropdown' );

		this.desktopQuery = window.matchMedia( '(min-width: 768px)' );

		if ( this.desktopQuery.matches && sidebar ) {
			sidebar.classList.toggle( 'expanded', true !== getCtcStorageItem( 'sidebar-collapsed' ) );
		}

		this.syncMenuToggleState( menuToggle, sidebar );

		const closeDrawer = () => {
			sidebar.classList.remove( 'open' );
			document.body.style.overflow = '';
			this.syncMenuToggleState( menuToggle, sidebar );
		};

		if ( menuToggle && sidebar ) {
			menuToggle.addEventListener( 'click', () => {
				if ( this.desktopQuery.matches ) {
					const isExpanded = sidebar.classList.toggle( 'expanded' );
					setCtcStorageItem( 'sidebar-collapsed', ! isExpanded );
				} else {
					sidebar.classList.toggle( 'open' );
					document.body.style.overflow = sidebar.classList.contains( 'open' ) ? 'hidden' : '';
				}
				this.syncMenuToggleState( menuToggle, sidebar );
			} );
		}

		if ( closeSidebar && sidebar ) {
			closeSidebar.addEventListener( 'click', () => {
				closeDrawer();
			} );
		}

		document.addEventListener( 'click', ( event ) => {
			if (
				! this.desktopQuery.matches &&
				sidebar &&
				menuToggle &&
				! sidebar.contains( event.target ) &&
				! menuToggle.contains( event.target ) &&
				sidebar.classList.contains( 'open' )
			) {
				closeDrawer();
			}
		} );

		document.addEventListener( 'keydown', ( event ) => {
			if ( 'Escape' !== event.key || ! sidebar ) { return; }
			if ( this.desktopQuery.matches || ! sidebar.classList.contains( 'open' ) ) { return; }

			closeDrawer();
			menuToggle?.focus();
		} );

		this.desktopQuery.addEventListener( 'change', () =>
			this.syncMenuToggleState( menuToggle, sidebar ) );

		if ( settingsToggle && settingsDropdown ) {
			const setDropdown = ( open ) => {
				settingsDropdown.classList.toggle( 'hidden', ! open );
				settingsToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			};

			settingsToggle.addEventListener( 'click', ( event ) => {
				event.stopPropagation();
				setDropdown( settingsDropdown.classList.contains( 'hidden' ) );
			} );
			document.addEventListener( 'click', ( event ) => {
				if (
					! settingsDropdown.contains( event.target ) &&
					! settingsToggle.contains( event.target )
				) {
					setDropdown( false );
				}
			} );

			document.addEventListener( 'keydown', ( event ) => {
				if ( 'Escape' !== event.key || settingsDropdown.classList.contains( 'hidden' ) ) { return; }
				setDropdown( false );
				settingsToggle.focus();
			} );
		}
	}

	/**
	 * Synchronize the menu toggle button's aria-expanded and data-tip state with the sidebar.
	 *
	 * @param {HTMLElement|null} menuToggle Hamburger button.
	 * @param {HTMLElement|null} sidebar    Sidebar element.
	 */
	static syncMenuToggleState ( menuToggle, sidebar ) {
		if ( ! menuToggle || ! sidebar ) { return; }

		const isOpen = this.desktopQuery.matches ?
			sidebar.classList.contains( 'expanded' ) :
			sidebar.classList.contains( 'open' );

		menuToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		menuToggle.setAttribute( 'data-tip', isOpen ? 'Collapse menu' : 'Expand menu' );
	}

	/**
	 * Main Navigation & Deep Link Handling
	 */
	static initNavigation () {
		log( 'Interface', 'Initializing Navigation...' );
		const navItems = document.querySelectorAll( '.nav-item' );

		const urlParams = new URLSearchParams( window.location.search );
		const urlTabId = urlParams.get( 'tab' );
		const hashTabId = window.location.hash.replace( '#', '' )
			.split( '/' )[ 0 ];

		const activeTabId = urlTabId || hashTabId || getCtcStorageItem( 'active-tab' );

		const activeTab = activeTabId ?
			document.querySelector( `.nav-item[data-tab="${activeTabId}"]` ) :
			null;
		const activePanel = activeTabId ? document.getElementById( activeTabId ) : null;

		if ( activeTab && activePanel ) {
			const initialSectionId = window.location.hash.replace( '#', '' )
				.split( '/' )[ 1 ];

			this.pendingTarget = initialSectionId || null;
			this.activateTab( activeTabId, !! initialSectionId );
		} else {
			const defaultActive = document.querySelector( '.nav-item.active' );
			if ( defaultActive ) {
				this.activateTab( defaultActive.getAttribute( 'data-tab' ) );
			}
		}

		Interface.updateMobileSectionLabel();

		navItems.forEach( ( item ) => {
			item.addEventListener( 'click', () => {
				this.activateTab( item.getAttribute( 'data-tab' ) );
			} );
		} );

		const logoHome = document.getElementById( 'logo-home' );
		if ( logoHome ) {
			const goHome = () => {
				const generalTab = document.querySelector( '.nav-item[data-tab="general-settings"]' );
				if ( generalTab ) { generalTab.click(); }
			};

			logoHome.addEventListener( 'click', goHome );
			logoHome.addEventListener( 'keydown', ( event ) => {
				if ( 'Enter' !== event.key && ' ' !== event.key ) { return; }

				event.preventDefault();
				goHome();
			} );
		}

		// 5. Dynamic Module Scroll Synchronization
		// Some sections are created ONLY when they enter the viewport (Lazy Sections)
		document.addEventListener( 'ht_ctc_register_section_dynamic', ( event ) => {
			const { element, id } = event.detail;
			if ( element && id && 'IntersectionObserver' in window ) {
				const observer = new IntersectionObserver( ( entries, obs ) => {
					entries.forEach( entry => {
						if ( entry.isIntersecting ) {
							this.app.loadTabSettings( id );
							obs.unobserve( entry.target );
						}
					} );
				}, { rootMargin: '200px' } );
				observer.observe( element );
			} else if ( id ) {
				this.app.loadTabSettings( id );
			}
		} );

		// Global link interceptor for #tab-id/section-id deep links.
		document.addEventListener( 'click', async ( event ) => {
			const link = event.target.closest( 'a[href^="#"]' );
			if ( ! link ) { return; }

			const fullHash = link.getAttribute( 'href' )
				.replace( '#', '' );
			if ( ! fullHash ) { return; }

			const [ tabId, sectionId ] = fullHash.split( '/' );
			const navItem = document.querySelector( `.nav-item[data-tab="${tabId}"]` );

			if ( navItem ) {
				event.preventDefault();
				this.pendingTarget = sectionId || null;
				await this.activateTab( tabId, !! sectionId );
			}
		} );

		// Drill-down sidebar submenus.
		document.addEventListener( 'click', ( event ) => {
			const drillDownBtn = event.target.closest( '.drill-down-btn' );
			const backBtn = event.target.closest( '.drill-down-back-btn' );

			if ( drillDownBtn ) {
				const targetMenuId = drillDownBtn.getAttribute( 'data-target' );
				const targetMenu = document.getElementById( targetMenuId );
				if ( targetMenu ) {
					document.querySelectorAll( '.sidebar-menu' )
						.forEach( menu => menu.classList.remove( 'active' ) );
					targetMenu.classList.add( 'active' );
					( targetMenu.querySelector( '.nav-item.active' ) || targetMenu.querySelector( '.nav-item' ) )?.click();
				}
			}

			if ( backBtn ) {
				const targetMenuId = backBtn.getAttribute( 'data-target' );
				const targetMenu = document.getElementById( targetMenuId );
				if ( targetMenu ) {
					document.querySelectorAll( '.sidebar-menu' )
						.forEach( menu => menu.classList.remove( 'active' ) );
					targetMenu.classList.add( 'active' );
					( targetMenu.querySelector( '.nav-item.active' ) || targetMenu.querySelector( '.nav-item' ) )?.click();
				}
			}
		} );
	}

	/**
	 * Activates a settings tab.
	 *
	 * @param {string}  tabId      Tab ID to activate.
	 * @param {boolean} skipScroll Whether to skip scrolling to the top.
	 */
	static async activateTab ( tabId, skipScroll = false ) {
		const navItem = document.querySelector( `.nav-item[data-tab="${tabId}"]` );
		const panel = document.getElementById( tabId );
		if ( ! navItem || ! panel ) { return; }

		document.querySelectorAll( '.nav-item' )
			.forEach( nav => {
				nav.classList.remove( 'active' );
				nav.removeAttribute( 'aria-current' );
			} );
		document.querySelectorAll( '.settings-panel' )
			.forEach( panel => panel.classList.remove( 'active' ) );

		navItem.classList.add( 'active' );
		navItem.setAttribute( 'aria-current', 'page' );
		panel.classList.add( 'active' );
		setCtcStorageItem( 'active-tab', tabId );

		const parentMenu = navItem.closest( '.sidebar-menu' );
		if ( parentMenu ) {
			document.querySelectorAll( '.sidebar-menu' )
				.forEach( menu => menu.classList.remove( 'active' ) );
			parentMenu.classList.add( 'active' );
		}

		if ( ! skipScroll ) {
			Interface.resetScrollTo();
		}

		if ( panel.dataset.loaded === 'false' ) {
			await Interface.app.loadTabSettings( tabId );
		}

		Interface.updateMobileSectionLabel();
		Interface.updateProWidget( tabId );

		Interface.app.events?.emit( 'tab:changed', tabId );

		// Close mobile drawer after navigation.
		const sidebar = document.getElementById( 'sidebar' );
		if ( ! Interface.desktopQuery.matches && sidebar ) {
			sidebar.classList.remove( 'open' );
			document.body.style.overflow = '';
			Interface.syncMenuToggleState( document.getElementById( 'menu-toggle' ), sidebar );
		}
	}

	/**
	 * Scrolls to an element inside the active panel and highlights it.
	 *
	 * @param {string} elementId Target element ID.
	 */
	static scrollToElement ( elementId ) {
		const panel = document.querySelector( '.settings-panel.active' );
		if ( ! panel || ! elementId ) { return; }

		const element = [ ...panel.querySelectorAll( '[id]' ) ]
			.find( ( el ) => el.id === elementId );

		if ( ! element ) {
			log( 'Interface', `scrollToElement: #${elementId} not found in ${panel.id}.` );
			return;
		}

		const target = element.closest( '.form-group' ) ||
			element.closest( '.ctc-card' ) ||
			element.closest( '.field-group' ) ||
			element;

		target.scrollIntoView( { behavior: 'smooth', block: 'start' } );

		target.classList.add( 'ctc-highlight-jump' );
		setTimeout( () => target.classList.remove( 'ctc-highlight-jump' ), 2000 );
	}

	/**
	 * Inner page tabs delegation.
	 */
	static initTabs () {
		document.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '.tab-button' );
			if ( ! button || button.classList.contains( 'nav-item' ) ) { return; }

			const tabContainer = button.closest( '.tabs' );
			if ( ! tabContainer ) { return; }

			event.preventDefault();
			const tabToShow = button.getAttribute( 'data-tab' );

			tabContainer.querySelectorAll( '.tab-button' )
				.forEach( btn => {
					btn.classList.remove( 'active' );
					btn.setAttribute( 'aria-selected', 'false' );
				} );
			button.classList.add( 'active' );
			button.setAttribute( 'aria-selected', 'true' );

			tabContainer.querySelectorAll( '.tab-content' )
				.forEach( content => content.classList.remove( 'active' ) );

			const targetContent = document.getElementById( `${tabToShow}-tab` );
			if ( targetContent ) { targetContent.classList.add( 'active' ); }

			Interface.app.events?.emit( 'tab:changed', tabToShow );
		} );
	}

	/**
	 * Adaptive help icons toggle.
	 */
	static initHelpIcons () {
		document.addEventListener( 'click', ( event ) => {
			const toggle = event.target.closest( '.help-toggle' );
			if ( ! toggle ) { return; }

			event.preventDefault();
			const parent = toggle.closest( '.form-group' );
			if ( parent ) {
				parent.classList.toggle( 'help-active' );
			}
		} );
	}

	/**
	 * WordPress Media Uploader integration for greetings header image.
	 */
	static initGreetingsImage () {
		let mediaUploader;
		document.addEventListener( 'click', ( event ) => {
			const addBtn = event.target.closest( '.ctc_add_image_wp' );
			const removeBtn = event.target.closest( '.ctc_remove_image_wp' );

			if ( addBtn ) {
				event.preventDefault();
				if ( mediaUploader ) { mediaUploader.open(); return; }
				if ( ! window.wp?.media ) { return; }

				mediaUploader = wp.media.frames.file_frame = wp.media( {
					title: 'Select Header Image',
					button: { text: 'Select' },
					multiple: false,
				} );

				mediaUploader.on( 'select', () => {
					const attachment = mediaUploader.state()
						.get( 'selection' )
						.first()
						.toJSON();
					if ( ! attachment ) { return; }
					const wrapper = addBtn.closest( '.ctc-image-upload-wrapper' ) || document;
					const input = wrapper.querySelector( '.g_header_image' );
					const preview = wrapper.querySelector( '.g_header_image_preview' );
					const remBtn = wrapper.querySelector( '.ctc_remove_image_wp' );

					if ( input ) {
						input.value = attachment.url;
						input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
					}
					if ( preview ) { preview.src = attachment.url; preview.style.display = 'block'; }
					if ( remBtn ) { remBtn.style.display = 'inline-block'; }
				} );
				mediaUploader.open();
			}

			if ( removeBtn ) {
				event.preventDefault();
				const wrapper = removeBtn.closest( '.ctc-image-upload-wrapper' ) || document;
				const input = wrapper.querySelector( '.g_header_image' );
				const preview = wrapper.querySelector( '.g_header_image_preview' );
				if ( input ) {
					input.value = '';
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				}
				if ( preview ) { preview.style.display = 'none'; }
				removeBtn.style.display = 'none';
			}
		} );
	}

	/**
	 * Resets scroll position to top.
	 */
	static resetScrollTo () {
		const target = document.querySelector( '.main-content' );
		if ( target ) { target.scrollTo( 0, 0 ); }
		window.scrollTo( 0, 0 );
	}

	/**
	 * Updates the current section label (Mobile Top Bar).
	 */
	static updateMobileSectionLabel () {
		const label = document.getElementById( 'mobile-section-label' );
		const activeNav = document.querySelector( '.nav-item.active' );
		if ( ! label || ! activeNav ) { return; }

		const span = activeNav.querySelector( 'span:not(.dashicons):not(.ctc-icon)' );
		label.textContent = span ? span.textContent.trim() : activeNav.textContent.trim();
	}

	/**
	 * Updates the right sidebar PRO widget items relevant to the active tab.
	 *
	 * @param {string} tabId Active nav tab ID.
	 */
	static updateProWidget ( tabId ) {
		const promoWidget = document.querySelector( '.ctc-pro-promo' );
		if ( ! promoWidget ) { return; }

		promoWidget.hidden = ( tabId === 'pro-features' );
		if ( promoWidget.hidden ) { return; }

		const items = [ ...promoWidget.querySelectorAll( '.ctc-pro-feature-list li[data-tabs]' ) ];
		if ( ! items.length ) { return; }

		const matches = ( li, key ) => li.dataset.tabs.split( ' ' )
			.includes( key );
		const key = items.some( li => matches( li, tabId ) ) ? tabId : 'default';

		items.forEach( li => {
			li.hidden = ! matches( li, key );
		} );
	}

	/**
	 * Right sidebar tabs (Support / Feedback / Preview) with keyboard navigation.
	 */
	static initRightSidebar () {
		const tabButtons = [ ...document.querySelectorAll( '.sidebar-tab-btn' ) ];
		const tabContents = [ ...document.querySelectorAll( '.sidebar-tab-content' ) ];
		if ( ! tabButtons.length ) { return; }

		const idOf = ( btn ) => btn.dataset.sidebarTab;
		let lastOpenId = idOf( tabButtons.find( btn => btn.classList.contains( 'active' ) ) || tabButtons[ 0 ] );

		/**
		 * @param {string|null} tabId Tab to open, or null to close the panel.
		 */
		const render = ( tabId ) => {
			if ( tabId ) { lastOpenId = tabId; }

			tabButtons.forEach( btn => {
				const isOpen = idOf( btn ) === tabId;
				btn.classList.toggle( 'active', isOpen );
				btn.setAttribute( 'aria-selected', isOpen ? 'true' : 'false' );
				btn.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
				btn.tabIndex = ( idOf( btn ) === lastOpenId ) ? 0 : -1;
			} );

			tabContents.forEach( content =>
				content.classList.toggle( 'active', content.id === `sidebar-tab-${tabId}` ) );
		};

		const switchTab = ( tabId ) => {
			const isOpen = tabButtons.some( btn => idOf( btn ) === tabId && btn.classList.contains( 'active' ) );
			render( isOpen ? null : tabId );
		};

		/**
		 * Resolves arrow/Home/End key navigation for tabs.
		 *
		 * @param {KeyboardEvent} event Keyboard event.
		 * @param {number}        index Index of the currently focused button.
		 * @returns {HTMLElement|null} Target button element or null.
		 */
		const nextFromKey = ( event, index ) => {
			if ( 'Home' === event.key ) { return tabButtons[ 0 ]; }
			if ( 'End' === event.key ) { return tabButtons[ tabButtons.length - 1 ]; }

			let step = 0;
			if ( 'ArrowRight' === event.key ) { step = 1; }
			if ( 'ArrowLeft' === event.key ) { step = -1; }
			if ( ! step ) { return null; }

			if ( 'rtl' === ( document.documentElement.dir || '' ) ) { step = -step; }

			const total = tabButtons.length;
			return tabButtons[ ( index + step + total ) % total ];
		};

		tabButtons.forEach( ( btn, index ) => {
			btn.addEventListener( 'click', () => switchTab( idOf( btn ) ) );

			btn.addEventListener( 'keydown', ( event ) => {
				const target = nextFromKey( event, index );
				if ( ! target ) { return; }

				event.preventDefault();
				render( idOf( target ) );
				target.focus();
			} );
		} );

		// Programmatic activation (e.g. PreviewManager).
		document.addEventListener( 'ctc_open_sidebar_tab', ( event ) => {
			const tabId = event.detail?.tab;
			if ( ! tabId ) { return; }
			const targetBtn = tabButtons.find( btn => idOf( btn ) === tabId );
			if ( ! targetBtn || targetBtn.classList.contains( 'active' ) ) { return; }
			render( tabId );
		} );

		render( idOf( tabButtons.find( btn => btn.classList.contains( 'active' ) ) || tabButtons[ 0 ] ) );
	}
}

