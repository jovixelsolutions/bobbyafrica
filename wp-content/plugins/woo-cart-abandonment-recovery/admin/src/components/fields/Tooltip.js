import { Tooltip as TooltipComponent } from '@bsf/force-ui';
import { CircleHelp, CircleAlertIcon } from 'lucide-react';

const Tooltip = ( {
	title,
	content,
	type = 'info',
	placement = 'bottom',
	arrow = true,
	interactive = false,
	portalId = 'wcar-admin-app',
	triggers = [ 'hover', 'focus' ],
	children,
	...props
} ) => {
	switch ( type ) {
		case 'help':
			children = (
				<CircleHelp className="cursor-pointer !w-4 !h-5 !text-gray-600 hover:!text-gray-700 mt-px ml-1" />
			);
			break;
		case 'info':
			children = (
				<CircleAlertIcon className="cursor-pointer !w-4 !h-5 !text-gray-600 hover:!text-gray-700 mt-px ml-1" />
			);
			break;
		default:
			children = children;
	}

	if ( ! children ) {
		return null;
	}

	return (
		<TooltipComponent
			title={ title }
			content={ content }
			placement={ placement }
			arrow={ arrow }
			interactive={ interactive }
			tooltipPortalId={ portalId }
			triggers={ triggers }
			{ ...props }
		>
			{ children }
		</TooltipComponent>
	);
};

export default Tooltip;
