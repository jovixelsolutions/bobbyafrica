/**
 * Admin Entry Point
 *
 * Bootstraps the application.
 *
 * Architecture Overview:
 * - Render/*:   Responsible for generating and displaying the HTML output.
 * - Logic/*:    Handles the core business logic and flow of the application.
 * - Managers/*: Manages specific domains (e.g., Settings, Themes, UI updates) after rendering.
 *               They handle saving changes, updating visuals, and synchronizing fields.
 */
import App from './modules/core/App.js'; // Main Application Orchestrator: Initializes modules and manages state

import SettingsManager from './modules/managers/SettingsManager.js'; // Manages settings data, saving, and AJAX requests

// import OmniManager from './modules/managers/OmniManager.js'; // Specific logic for Omni-Channel settings (drag & drop, toggles)

// Import Managers
import ThemeManager from './modules/managers/ThemeManager.js'; // Manages admin theme selection (light/dark mode)
import UIManager from './modules/managers/UIManager.js'; // UI Feedback: Toasts, Loading states, etc.
import Interface from './modules/logic/Interface.js'; // layout Logic: Sidebar navigation, Tab switching, dynamic loading
// import Analytics from './modules/logic/Analytics.js'; // Analytics tracking integration

import { registerDefaultRenderers } from './modules/renderers/Registry.js';

( function bootstrapAdminRuntime ( window, document ) {

	// 1. Get Configuration from Server
	// 'ht_ctc_admin_var' is a global object localized by WordPress, containing settings, nonces, and paths.
	const config = window.ht_ctc_admin_var || {};

	// 2. Initialize Main Application
	// The App class acts as the central orchestrator, managing state and modules.
	const app = new App( config );

	// Expose for debugging purposes
	window.HTCtcAdminApp = app;

	// 3. Register Managers
	// We use Dependency Injection to register feature modules.
	// This approach decouples the App class from specific features, preventing circular dependencies
	// and making the codebase easier to maintain and extend.
	app.registerManager( 'ui', UIManager );
	app.registerManager( 'settings', SettingsManager );
	app.registerManager( 'theme', ThemeManager );

	// PreviewManager is loaded as a delayed module (see loadDelayedModules below)
	// so the live-preview code stays out of the initial bundle.
	// RepeaterManager now loaded dynamically in App.js when required

	// app.registerManager('omni', OmniManager); // Omni-Channel features (disabled)

	// ...

	// 4. Register Renderers
	// Registers the functions responsible for generating HTML for different field types.
	registerDefaultRenderers( app );

	// 5. Initialize Logic/Interface
	// Initialize the Interface module which handles the sidebar navigation,
	// tab switching, and dynamic content loading.
	Interface.init( app );

	// 6. Deferred boot work — run when the browser is idle (guaranteed within
	// the timeout, so it still runs on a busy main thread) so none of it
	// competes with the initial render or first interaction.
	const runDeferredBootTasks = () => {
		// Global, non-tab features (e.g. the live Preview) stay out of the
		// initial bundle; each self-schedules by its own `delay` in modulesPath.
		app.loadDelayedModules();

		// Silently pre-cache inactive tabs into localStorage for 0ms tab switches.
		setTimeout( () => app.preloadBackgroundTabs(), 1500 );
	};

	if ( 'requestIdleCallback' in window ) {
		window.requestIdleCallback( runDeferredBootTasks, { timeout: 2000 } );
	} else {
		runDeferredBootTasks();
	}

} )( window, document );
