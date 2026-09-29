import { Tooltip } from '@bsf/force-ui';
import PropTypes from 'prop-types';

const AppTooltip = ( {
	content,
	position = 'top',
	children,
	className = '',
} ) => {
	return (
		<Tooltip
			arrow
			content={ content }
			placement={ position }
			variant="dark"
			className={ className }
			tooltipPortalId="wcar-admin-app"
		>
			{ children }
		</Tooltip>
	);
};

AppTooltip.propTypes = {
	content: PropTypes.node.isRequired,
	position: PropTypes.oneOf( [ 'top', 'bottom', 'left', 'right' ] ),
	children: PropTypes.node.isRequired,
	className: PropTypes.string,
};

export default AppTooltip;
