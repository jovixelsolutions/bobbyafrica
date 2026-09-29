import { ArrowUp, ArrowDown } from 'lucide-react';

export const getChangeIcon = ( item, key ) => {
	const value = item?.changes?.[ key ];

	if ( typeof value !== 'number' ) {
		return null;
	}
	if ( value > 0 ) {
		if ( key === 'position' ) {
			return (
				<ArrowDown className="size-3.5 text-support-error shrink-0" />
			);
		}
		return <ArrowUp className="size-3.5 text-support-success shrink-0" />;
	}
	if ( value < 0 ) {
		if ( key === 'position' ) {
			return (
				<ArrowUp className="size-3.5 text-support-success shrink-0" />
			);
		}
		return <ArrowDown className="size-3.5 text-support-error shrink-0" />;
	}
	return null;
};
