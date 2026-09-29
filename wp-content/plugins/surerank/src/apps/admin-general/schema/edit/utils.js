/**
 * Checks if a schema has display conditions configured.
 *
 * @param {Object} schemaData - The schema data object containing show_on and not_show_on rules
 * @return {boolean} True if display conditions are set, false otherwise
 */
export const hasDisplayConditions = ( schemaData ) => {
	const hasShowOn =
		schemaData?.show_on?.rules?.length > 0 &&
		schemaData.show_on.rules.some( ( rule ) => rule && rule.trim() !== '' );

	const hasNotShowOn =
		schemaData?.not_show_on?.rules?.length > 0 &&
		schemaData.not_show_on.rules.some(
			( rule ) => rule && rule.trim() !== ''
		);

	return hasShowOn || hasNotShowOn;
};
