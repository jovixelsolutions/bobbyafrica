import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { PROCESS_STATUSES } from '@Global/constants';
import { generateContent as generateContentAPI } from '@Functions/api';
import GenerateContentRender from '@GlobalComponents/fix-it-for-me/generate-content';
import { UpgradeCPT } from '@GlobalComponents/fix-it-for-me';
import ContentGenerationError from '@GlobalComponents/content-generation-error';

const GenerateContent = ( { fieldType, onUseThis, fixing = false } ) => {
	const [ status, setStatus ] = useState( PROCESS_STATUSES.IDLE );
	const [ generatedContent, setGeneratedContent ] = useState( [] );
	const [ genError, setGenError ] = useState( null );

	const generateContent = async () => {
		if ( status === PROCESS_STATUSES.IN_PROGRESS ) {
			return;
		}

		try {
			setStatus( PROCESS_STATUSES.START );

			if ( ! fieldType ) {
				throw {
					message: __(
						'No content type selected for generation.',
						'surerank'
					),
					code: 'no_content_type',
				};
			}

			setStatus( PROCESS_STATUSES.IN_PROGRESS );

			const response = await generateContentAPI( fieldType );

			if ( ! response?.success ) {
				throw response;
			}

			const transformedContent = response.content.map(
				( item, index ) => ( {
					id: index + 1,
					text: item,
				} )
			);

			setGeneratedContent( transformedContent );
			setStatus( PROCESS_STATUSES.COMPLETED );
			setGenError( null );
		} catch ( err ) {
			setStatus( PROCESS_STATUSES.FAILED );
			setGenError( {
				message:
					err?.message ??
					__( 'An unexpected error occurred', 'surerank' ),
				code: err?.code || 'unknown_error',
			} );
		}
	};

	const handleRegenerate = () => {
		setGeneratedContent( [] );
		generateContent();
	};

	// Auto-start generation when component mounts
	useEffect( () => {
		if (
			status === PROCESS_STATUSES.IDLE &&
			generatedContent.length === 0
		) {
			generateContent();
		}
	}, [] );

	// Show content or empty state for completed state
	if (
		status === PROCESS_STATUSES.COMPLETED ||
		generatedContent.length > 0
	) {
		return (
			<GenerateContentRender
				contents={ generatedContent }
				onRegenerate={ handleRegenerate }
				onUseThis={ onUseThis }
				fixing={ fixing }
				fieldType={ fieldType }
			/>
		);
	}

	// Show error state
	if ( status === PROCESS_STATUSES.FAILED || genError ) {
		// If the error is due to not having a Pro plan and limit exceeded, show the upgrade component
		if ( genError?.code === 'require_pro' ) {
			return <UpgradeCPT />;
		}

		if ( genError?.code === 'limit_exceeded' ) {
			return (
				<UpgradeCPT
					title={ __( 'Daily Limit Reached', 'surerank' ) }
					description={ __(
						"You've used all your AI credits for today. Your credits will refresh automatically tomorrow, so you can continue creating content.",
						'surerank'
					) }
					showButton={ false }
				/>
			);
		}

		// For other errors, show the ContentGenerationError component
		return (
			<ContentGenerationError
				error={ genError }
				onRetry={ handleRegenerate }
				title={ __( 'Generation Failed', 'surerank' ) }
				supportText={ __(
					'Click here to contact support.',
					'surerank'
				) }
				retryText={ __( 'Retry', 'surerank' ) }
			/>
		);
	}

	// Show loading state
	return (
		<GenerateContentRender
			contents={ [] }
			onRegenerate={ handleRegenerate }
			onUseThis={ onUseThis }
			generating={
				status === PROCESS_STATUSES.IN_PROGRESS ||
				status === PROCESS_STATUSES.START
			}
			error={ genError }
			fixing={ fixing }
			fieldType={ fieldType }
		/>
	);
};

export default GenerateContent;
