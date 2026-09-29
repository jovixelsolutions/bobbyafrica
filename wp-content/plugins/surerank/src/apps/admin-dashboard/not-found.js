import { Button, Text } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

const NotFound = () => {
	const handleGoBack = () => {
		window.history.back();
	};

	const handleGoToDashboard = () => {
		window.location.hash = '#/dashboard';
	};

	return (
		<div className="flex flex-col items-center justify-center min-h-[60vh] p-8">
			<div className="flex flex-col items-center gap-6 max-w-md text-center">
				<div className="flex flex-col gap-2">
					<Text size={ 24 } weight={ 600 } color="primary">
						{ __( 'Page Not Found', 'surerank' ) }
					</Text>
					<Text size={ 14 } weight={ 400 } color="secondary">
						{ __(
							"Either the page does not exist or you don't have permission to access this page please contact your administrator.",
							'surerank'
						) }
					</Text>
				</div>

				<div className="flex gap-3">
					<Button variant="primary" onClick={ handleGoBack }>
						{ __( 'Go Back', 'surerank' ) }
					</Button>
					<Button variant="ghost" onClick={ handleGoToDashboard }>
						{ __( 'Go to Dashboard', 'surerank' ) }
					</Button>
				</div>
			</div>
		</div>
	);
};

export default NotFound;
