import { Tooltip } from '@/apps/admin-components/tooltip';
import { SparklesIconSolid } from '@GlobalComponents/icons';
import { Button } from '@bsf/force-ui';
const AdminMagicButton = ( { onClick, tooltip } ) => {
	const button = (
		<Button
			size="xs"
			variant="ghost"
			className="p-0.5 text-icon-interactive outline-brand-200 rounded-sm"
			icon={ <SparklesIconSolid /> }
			onClick={ onClick }
		/>
	);

	if ( tooltip ) {
		return (
			<Tooltip title={ tooltip } placement="top-end">
				{ button }
			</Tooltip>
		);
	}

	return button;
};

export default AdminMagicButton;
