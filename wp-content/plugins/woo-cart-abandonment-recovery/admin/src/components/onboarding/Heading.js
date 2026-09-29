import { Text } from '@bsf/force-ui';

const Heading = ( { title, description } ) => {
	return (
		<div className="space-y-1.5">
			<Text as="h2" size={ 24 } weight={ 600 }>
				{ title }
			</Text>
			<Text size={ 14 } weight={ 400 } color="secondary">
				{ description }
			</Text>
		</div>
	);
};

export default Heading;
