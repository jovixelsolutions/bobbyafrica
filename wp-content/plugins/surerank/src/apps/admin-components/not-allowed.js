import { Text } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

const NotAllowedMessage = () => {
	return (
		<Text
			size={ 13 }
			weight={ 400 }
			color="secondary"
			className="text-center"
		>
			{ __(
				"You don't have permission to perform this action. Please contact administrator.",
				'surerank'
			) }
		</Text>
	);
};

export default NotAllowedMessage;
