import React from 'react';
import PropTypes from 'prop-types';
import { Container, Label } from '@bsf/force-ui';
import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';

/**
 * EmptyBlock component for displaying empty state messages.
 *
 * @param {Object}          props             - Component props.
 * @param {React.ReactNode} props.icon        - Icon to display (defaults to ExclamationTriangle).
 * @param {string}          props.title       - Primary message to display.
 * @param {string}          props.description - Secondary message with additional context.
 * @param {string}          props.className   - Additional CSS classes.
 * @param {number|string}   props.height      - Height of the empty block (defaults to 200px).
 * @return {JSX.Element} EmptyBlock component.
 */
const EmptyBlock = ( {
	icon = <ExclamationTriangleIcon className="h-12 w-12 text-yellow-500" />,
	title,
	description,
	className = '',
	height = '200px',
} ) => {
	return (
		<Container
			className={ `flex-1 h-[${ height }] flex items-center justify-center bg-white rounded-md ${ className }` }
			containerType="flex"
			direction="column"
			align="center"
		>
			<Container.Item>{ icon }</Container.Item>
			<Container.Item className="mb-3">
				<Label className="text-gray-600 font-medium text-base text-center">
					{ title }
				</Label>
			</Container.Item>
			{ description && (
				<Container.Item className="mb-3">
					<Label className="text-gray-500 text-sm text-center">
						{ description }
					</Label>
				</Container.Item>
			) }
		</Container>
	);
};

EmptyBlock.propTypes = {
	icon: PropTypes.node,
	title: PropTypes.string.isRequired,
	description: PropTypes.string,
	className: PropTypes.string,
	height: PropTypes.oneOfType( [ PropTypes.string, PropTypes.number ] ),
};

export default EmptyBlock;
