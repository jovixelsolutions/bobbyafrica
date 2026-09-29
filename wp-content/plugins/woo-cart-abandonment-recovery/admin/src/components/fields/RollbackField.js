import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Select, Button } from '@bsf/force-ui';

import FieldWrapper from '@Components/common/FieldWrapper';
import ConfirmationModal from '@Components/common/ConfirmationModal';

const RollbackField = ( {
	title,
	description,
	name,
	options = [],
	disableStyle,
} ) => {
	const [ previousVersion, setPreviousVersion ] = useState( options[ 0 ] );
	const [ isPopupOpen, setIsPopupOpen ] = useState( false );
	const [ pluginName, setPluginName ] = useState( '' );

	const rollbackButtonClickHandler = () => {
		setPluginName( 'Cart Abandonment Recovery' );
		setIsPopupOpen( true );
	};

	const onConfirm = () => {
		const rollbackUrl = cart_abandonment_admin.rollback_url.replace(
			'VERSION',
			previousVersion.name
		);
		setIsPopupOpen( false );
		window.location.href = rollbackUrl;
	};

	return (
		<>
			<FieldWrapper
				title={ title }
				description={ description }
				type="inline"
				disableStyle={ disableStyle }
			>
				<div className="flex gap-8 items-center">
					<Select
						onChange={ ( val ) => {
							setPreviousVersion( val );
						} }
						size="md"
						name={ name }
						value={ previousVersion.name }
					>
						<Select.Button
							placeholder={ __(
								'Select an option',
								'woo-cart-abandonment-recovery'
							) }
						/>
						<Select.Options>
							{ options.map( ( option ) => (
								<Select.Option
									key={ option.id }
									value={ option }
								>
									{ option.name }
								</Select.Option>
							) ) }
						</Select.Options>
					</Select>
					<Button
						className=""
						iconPosition="left"
						size="sm"
						tag="button"
						type="button"
						variant="primary"
						disabled={ isPopupOpen }
						loading={ isPopupOpen }
						onClick={ rollbackButtonClickHandler }
					>
						{ __( 'Rollback', 'woo-cart-abandonment-recovery' ) }
					</Button>
				</div>
			</FieldWrapper>
			<ConfirmationModal
				isOpen={ isPopupOpen }
				onClose={ () => setIsPopupOpen( false ) }
				onConfirm={ onConfirm }
				title={ __(
					'Rollback to Previous Version',
					'woo-cart-abandonment-recovery'
				) }
				message={ sprintf(
					// translators: %1$s is the selected product of CartFlows, %2$s is the selected version of CartFlows.
					__(
						'Are you sure you want to rollback to %1$s v%2$s?',
						'woo-cart-abandonment-recovery'
					),
					pluginName,
					previousVersion.name
				) }
				confirmText={ __( 'Confirm', 'woo-cart-abandonment-recovery' ) }
			/>
		</>
	);
};

export default RollbackField;

