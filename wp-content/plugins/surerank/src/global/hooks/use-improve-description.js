import { useState, useMemo, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { toast } from '@bsf/force-ui';
import { countWords } from '@/functions/utils';

/**
 * Custom hook for improving business descriptions with AI
 *
 * @param {Object}   config                     - Configuration object
 * @param {string}   config.businessDescription - The current business description
 * @param {string}   config.websiteName         - The website name
 * @param {string}   config.organizationType    - The organization type
 * @param {Function} config.onSuccess           - Callback function called with improved description on success
 * @return {Object} Hook return value containing isImproving, improveDescription, currentWordCount, and hasMinimumWords
 */
const useImproveDescription = ( {
	businessDescription,
	websiteName,
	organizationType,
	onSuccess,
} ) => {
	const [ isImproving, setIsImproving ] = useState( false );

	// Calculate current word count
	const currentWordCount = useMemo(
		() => countWords( businessDescription ),
		[ businessDescription ]
	);

	// Check if minimum word count is met
	const hasMinimumWords = currentWordCount >= 5;

	/**
	 * Improve business description with AI
	 */
	const improveDescription = useCallback( async () => {
		const description = businessDescription;
		const wordCount = countWords( description );

		// Check if description has minimum required words
		if ( wordCount < 5 ) {
			toast.error(
				__(
					'Please add at least 5 words to your description before improving.',
					'surerank'
				)
			);
			return;
		}

		setIsImproving( true );

		try {
			const response = await apiFetch( {
				path: '/surerank/v1/onboarding/improve-description',
				method: 'POST',
				data: {
					business_name: websiteName || '',
					business_desc: description,
					business_category: organizationType || '',
					language: 'en',
				},
			} );

			if ( response?.success && response?.description ) {
				// Call the success callback with the improved description
				if ( typeof onSuccess === 'function' ) {
					onSuccess( response.description );
				}

				toast.success(
					__( 'Description improved successfully!', 'surerank' )
				);
			} else {
				throw new Error( 'Invalid response format' );
			}
		} catch ( error ) {
			toast.error(
				__(
					'Failed to improve description. Please try again.',
					'surerank'
				),
				{
					description: error?.message || '',
				}
			);
		} finally {
			setIsImproving( false );
		}
	}, [ businessDescription, websiteName, organizationType, onSuccess ] );

	return {
		isImproving,
		improveDescription,
		currentWordCount,
		hasMinimumWords,
	};
};

export default useImproveDescription;
