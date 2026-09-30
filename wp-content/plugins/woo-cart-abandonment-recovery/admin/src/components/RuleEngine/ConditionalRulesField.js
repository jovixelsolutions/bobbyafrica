import { useState } from 'react';
import { Button } from '@bsf/force-ui';
import { PencilSquareIcon } from '@heroicons/react/24/outline';
import { __, sprintf, _n } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';
import { useProAccess } from '@Components/pro/useProAccess';
import ConditionalRulesModal from '@Components/RuleEngine/ConditionalRulesModal';
import { parseRulesValue } from '@Utils/helper';

const isFieldFilled = ( v ) => {
	if ( v === undefined || v === null ) {
		return false;
	}
	if ( Array.isArray( v ) ) {
		return v.length > 0;
	}
	return String( v ).trim() !== '';
};

// Count only fully-filled rules (condition + operator + value) so the summary
// matches the drawer's validateForm() contract — otherwise a half-filled rule
// would inflate the count and surprise the user with a validation toast on Save.
const countFilledRules = ( groups ) => {
	let groupsWithRules = 0;
	let totalRules = 0;
	groups.forEach( ( g ) => {
		const filled = ( g?.rules || [] ).filter(
			( r ) =>
				isFieldFilled( r.condition ) &&
				isFieldFilled( r.operator ) &&
				isFieldFilled( r.value )
		);
		if ( filled.length > 0 ) {
			groupsWithRules += 1;
			totalRules += filled.length;
		}
	} );
	return { groupsWithRules, totalRules };
};

const ConditionalRulesField = ( {
	data,
	value,
	handleChange,
	isPro = false,
	proUpgradeMessage = '',
} ) => {
	const [ modalOpen, setModalOpen ] = useState( false );
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();

	const parsedRules = parseRulesValue( value );
	const { groupsWithRules, totalRules } = countFilledRules( parsedRules );

	let summary = __(
		'No conditions configured.',
		'woo-cart-abandonment-recovery'
	);
	if ( totalRules > 0 ) {
		const conditionsLabel = sprintf(
			/* translators: %d: number of conditions */
			_n(
				'%d condition',
				'%d conditions',
				totalRules,
				'woo-cart-abandonment-recovery'
			),
			totalRules
		);
		const groupsLabel = sprintf(
			/* translators: %d: number of groups */
			_n(
				'%d group',
				'%d groups',
				groupsWithRules,
				'woo-cart-abandonment-recovery'
			),
			groupsWithRules
		);
		summary = sprintf(
			/* translators: 1: conditions count phrase, 2: groups count phrase */
			__(
				'%1$s across %2$s',
				'woo-cart-abandonment-recovery'
			),
			conditionsLabel,
			groupsLabel
		);
	}

	const title =
		data?.label ||
		__( 'Conditions', 'woo-cart-abandonment-recovery' );

	const fieldIsPro = !! data?.is_pro || isPro;
	const fieldProMessage = data?.pro_upgrade_message || proUpgradeMessage;

	return (
		<>
			<FieldWrapper
				title={ title }
				description={ summary }
				name={ data?.name }
				isPro={ fieldIsPro }
				proUpgradeMessage={ fieldProMessage }
			>
				<Button
					variant="outline"
					size="sm"
					icon={
						<PencilSquareIcon aria-hidden="true" />
					}
					iconPosition="left"
					onClick={ () => setModalOpen( true ) }
					disabled={ fieldIsPro && isFeatureBlocked }
					type="button"
					className="whitespace-nowrap h-fit"
				>
					{ __(
						'Edit Conditions',
						'woo-cart-abandonment-recovery'
					) }
				</Button>
			</FieldWrapper>
			<ConditionalRulesModal
				open={ modalOpen }
				onOpenChange={ setModalOpen }
				value={ value }
				onChange={ ( rules ) =>
					handleChange && handleChange( data.name, rules )
				}
				isPro={ fieldIsPro }
				title={ __(
					'Dynamic Conditions',
					'woo-cart-abandonment-recovery'
				) }
			/>
		</>
	);
};

export default ConditionalRulesField;
