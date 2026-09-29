import { Badge } from '@bsf/force-ui';

const FeatureBadge = ( { feature = 'NEW', size = 'sm' } ) => {
	return (
		<Badge
			label={ feature }
			size={ size }
			type="pill"
			variant="neutral"
			className="w-fit bg-primary-25 text-primary-600 border-flamingo-400 hover:bg-flamingo-50"
		/>
	);
};

export default FeatureBadge;
