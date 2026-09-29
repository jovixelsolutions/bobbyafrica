import { __ } from '@wordpress/i18n';
import { Button } from '@bsf/force-ui';
import { useOnboardingState } from '@Onboarding/store';
import { X } from 'lucide-react';

export const exitURL = `${ surerank_globals.wp_dashboard_url }?page=surerank&skip_onboarding=true`;

const ExitButton = () => {
	const [ , dispatch ] = useOnboardingState();

	const handleClickExit = () => {
		dispatch( { isExiting: true } );
		window.open( exitURL, '_self', 'noopener,noreferrer' );
	};
	return (
		<Button
			className="text-text-primary no-underline hover:no-underline"
			variant="link"
			size="xs"
			icon={ <X /> }
			iconPosition="right"
			onClick={ handleClickExit }
		>
			{ __( 'Exit Guided Setup', 'surerank' ) }
		</Button>
	);
};

export default ExitButton;
