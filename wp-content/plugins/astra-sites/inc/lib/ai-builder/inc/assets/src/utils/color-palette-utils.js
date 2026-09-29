import { DARK_PALETTES, LIGHT_PALETTES } from '../ui/colors';

/**
 * Get a random color palette for a template based on its color scheme.
 *
 * @param {Object} template - The template object with design_defaults.
 * @return {Object} A random palette object from the matching palette set.
 */
export const getRandomColorPaletteForTemplate = ( template ) => {
	const colorScheme = template?.design_defaults?.color_scheme;
	const isDark = Array.isArray( colorScheme )
		? colorScheme.length > 0
		: !! colorScheme;

	return getRandomFallbackPalette( isDark );
};

/**
 * Get a random palette from the dark or light palette set.
 *
 * @param {boolean} isDarkScheme - Whether to use dark palettes.
 * @return {Object} A random palette object.
 */
export const getRandomFallbackPalette = ( isDarkScheme ) => {
	const palettes = isDarkScheme ? DARK_PALETTES : LIGHT_PALETTES;
	const randomIndex = Math.floor( Math.random() * palettes.length );
	return palettes[ randomIndex ];
};
