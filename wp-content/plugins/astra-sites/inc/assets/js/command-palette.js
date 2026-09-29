/**
 * Starter Templates Command Palette Integration
 *
 * Registers Starter Templates customizer panels with WordPress Command Palette.
 *`
 * @package Astra Sites
 * @since x.x.x
 */

( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.data || ! wp.commands ) {
		return;
	}

	const { dispatch } = wp.data;
	const { store: commandsStore } = wp.commands;
	const { unregisterCommand, registerCommand } = dispatch( commandsStore );

	const { imageDir, customCommands } = window.stCommandsPalette;
	const { createElement } = wp.element;
	const stIcon = createElement( 'img', {
		src: `${ imageDir }logo.svg`,
		alt: 'Starter Templates',
		width: 20,
		height: 20,
	} );

	// Function to remove existing commands
	function removeExistingCommands() {
		const store = wp.data.select( 'core/commands' );
		if ( store ) {
			const commandsToRemove = [
				'themes.php-starter-templates',
				'themes.php-ai-builder',
			];
			commandsToRemove.forEach( ( commandName ) => {
				unregisterCommand( commandName );
			} );
		}
	}

	// Function to register commands.
	function registerSTCommands() {
		customCommands.forEach( function ( command ) {
			try {
				// Register the command.
				dispatch( commandsStore ).registerCommand( {
					name: command.name,
					label: command.label,
					searchLabel: command.searchLabel,
					icon: stIcon,
					callback: function () {
						window.location.href = command.url;
					},
				} );
			} catch ( error ) {
				console.error( 'Error registering command:', error );
			}
		} );
	}

	// Remove existing commands first, then register new ones
	if ( wp.domReady ) {
		wp.domReady( function () {
			removeExistingCommands();
			registerSTCommands();
		} );
	} else {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', function () {
				removeExistingCommands();
				registerSTCommands();
			} );
		} else {
			removeExistingCommands();
			registerSTCommands();
		}
	}
} )( window.wp );
