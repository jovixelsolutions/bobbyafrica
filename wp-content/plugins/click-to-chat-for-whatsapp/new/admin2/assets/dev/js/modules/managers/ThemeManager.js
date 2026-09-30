/**
 * Theme Manager
 * Handles theme switching (light/dark/system).
 *
 * JS Concept: "CSS Variables" (Custom Properties)
 * Instead of changing many styles in JS, we just change one attribute on the `<html>` tag
 * (data-theme="dark"). The CSS then reacts to this attribute using variables.
 */
import { log } from '../core/Utils.js';

export default class ThemeManager {

	/**
	 * Initializes the ThemeManager.
	 * Safe to call multiple times (e.g. if the app config is updated).
	 * Event listeners are guarded internally to prevent duplication.
	 *
	 * @param {Object} app The main application instance.
	 */
	static init ( app ) {
		log( 'Theme', 'Initializing...' );
		this.app = app;

		// Cache the media query list to avoid re-evaluating it repeatedly
		if ( ! this.mediaQuery && window.matchMedia ) {
			this.mediaQuery = window.matchMedia( '(prefers-color-scheme: dark)' );
		}

		this.currentThemeSetting = app.config.theme || 'light';
		this.applyTheme( this.currentThemeSetting );

		// Prevent duplicate event listeners if init is called multiple times
		if ( this.eventsAttached ) {
			return;
		}
		this.eventsAttached = true;

		// 1. Listen for theme selector changes via event delegation
		document.addEventListener( 'change', ( event ) => {
			if ( event.target?.name === 'ht_ctc_admin_settings[theme]' ) {
				this.currentThemeSetting = event.target.value;
				this.applyTheme( this.currentThemeSetting );

				// Mark all radios as changed for auto-save
				document.querySelectorAll( 'input[name="ht_ctc_admin_settings[theme]"]' )
					.forEach( radio => { radio.dataset.changed = 'true'; } );
			}
		} );

		// 2. Listen for OS/System theme changes (Set up ONLY ONCE)
		if ( this.mediaQuery ) {
			this.mediaQuery.addEventListener( 'change', () => {
				// Only re-apply if the user actually wants the system theme
				if ( this.currentThemeSetting === 'system' ) {
					this.applyTheme( 'system' );
				}
			} );
		}
	}

	static applyTheme ( themeSetting ) {
		let appliedTheme = themeSetting;

		if ( themeSetting === 'system' ) {
			// Check our cached mediaQuery first.
			// Fallback to `window.matchMedia?.(...)?.matches` just in case applyTheme is called before init()
			const systemDark = this.mediaQuery?.matches ?? window.matchMedia?.( '(prefers-color-scheme: dark)' )?.matches;
			appliedTheme = systemDark ? 'dark' : 'light';
		}

		document.documentElement.setAttribute( 'data-theme', appliedTheme );
		log( 'Theme', `Applied theme: ${appliedTheme} (Setting: ${themeSetting})` );
	}
}
