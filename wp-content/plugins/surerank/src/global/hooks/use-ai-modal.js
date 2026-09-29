import { useState } from '@wordpress/element';
import { SITE_SEO_CHECKS_CONTENT_GENERATION_MAPPING } from '@Global/constants';

/**
 * Custom hook for managing AI modal state and handlers.
 * Provides a unified interface for opening the AI modal with proper field type mapping.
 *
 * @param {Function} onContentApply Callback function to apply generated content
 * @return {Object}                 AI modal state and handlers
 */
const useAIModal = ( onContentApply ) => {
	const [ aiModal, setAiModal ] = useState( {
		open: false,
		fieldKey: null,
		fieldType: null,
	} );

	/**
	 * Handle applying content from AI modal
	 *
	 * @param {string} content - Generated content to apply
	 */
	const handleUseThis = async ( content ) => {
		if ( aiModal.fieldKey ) {
			await onContentApply( aiModal.fieldKey, content );
		}
	};

	/**
	 * Open AI modal for a specific field
	 * Maps field key to content generation type using SITE_SEO_CHECKS_CONTENT_GENERATION_MAPPING
	 *
	 * @param {string} fieldKey          - Field key to generate content for
	 * @param {string} overrideFieldType - Optional override for field type (used for social fields)
	 */
	const handleOpenAIModal = ( fieldKey, overrideFieldType = null ) => {
		const fieldType =
			overrideFieldType ||
			SITE_SEO_CHECKS_CONTENT_GENERATION_MAPPING[ fieldKey ];

		setAiModal( ( prev ) => ( {
			...prev,
			open: true,
			fieldKey,
			fieldType,
		} ) );
	};

	/**
	 * Close AI modal
	 */
	const handleCloseAIModal = () => {
		setAiModal( ( prev ) => ( {
			...prev,
			open: false,
		} ) );
	};

	return {
		aiModal,
		handleOpenAIModal,
		handleCloseAIModal,
		handleUseThis,
	};
};

export default useAIModal;
