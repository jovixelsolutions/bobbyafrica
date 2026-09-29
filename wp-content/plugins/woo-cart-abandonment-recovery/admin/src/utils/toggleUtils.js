/**
 * Normalizes toggle values to handle both old checkbox values ('on'/'off') and new toggle values ('true'/'false')
 *
 * @param {string|boolean} value - The value to normalize
 * @return {boolean} - The normalized boolean value
 */
export const normalizeToggleValue = ( value ) => {
	if ( value === 'on' || value === 'true' ) {
		return true;
	}
	if ( value === 'off' || value === 'false' ) {
		return false;
	}
	return value ?? false;
};

/**
 * Converts a boolean value to a string for database storage
 *
 * @param {boolean} value - The boolean value to convert
 * @return {string} - The string representation ('true'/'false')
 */
export const convertToggleToStorage = ( value ) => {
	return String( value );
};
