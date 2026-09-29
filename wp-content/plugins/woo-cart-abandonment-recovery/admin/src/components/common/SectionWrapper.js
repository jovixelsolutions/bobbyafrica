import PropTypes from 'prop-types';

const SectionWrapper = ( { className = '', children } ) => {
	return (
		<div
			className={ `p-4 rounded-xl border border-solid border-gray-200 bg-white shadow-sm ${ className }` }
		>
			{ children }
		</div>
	);
};

SectionWrapper.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node.isRequired,
};

export default SectionWrapper;
