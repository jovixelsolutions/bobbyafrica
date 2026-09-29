import PropTypes from 'prop-types';

/**
 * Skeleton loader component for showing loading state
 *
 * @param {Object} props           Component props
 * @param {string} props.height    Height of the skeleton loader
 * @param {string} props.width     Width of the skeleton loader
 * @param {string} props.className Additional class names for the skeleton loader
 * @return {JSX.Element} Skeleton loader component
 */
const SkeletonLoader = ( { height, width, className } ) => {
	return (
		<div
			className={ `animate-pulse bg-gray-200 rounded-md ${ className }` }
			style={ {
				height: height || '1rem',
				width: width || '100%',
			} }
		></div>
	);
};

SkeletonLoader.propTypes = {
	height: PropTypes.string,
	width: PropTypes.string,
	className: PropTypes.string,
};

export default SkeletonLoader;
