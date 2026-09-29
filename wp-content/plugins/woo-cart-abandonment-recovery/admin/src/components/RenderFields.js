import React from 'react';
import ToggleField from '@Components/fields/ToggleField';
import TextField from '@Components/fields/TextField';
import TextAreaField from '@Components/fields/TextAreaField';
import NumberField from '@Components/fields/NumberField';
import SelectField from '@Components/fields/SelectField';
import MultiSelectField from '@Components/fields/MultiSelectField';
import DeleteCouponsField from '@Components/fields/DeleteCouponsField';
import WebhookURLField from '@Components/fields/WebhookURLField';
import TimeField from '@Components/fields/TimeField';
import ProductSearchField from '@Components/fields/ProductSearchField';
import RichTextField from '@Components/fields/RichTextField';
import TestEmail from '@Components/fields/TestEmail';
import SubjectField from '@Components/fields/SubjectField';
import RulesRepeater from '@Components/RuleEngine/RulesRepeater';
import EmailField from '@Components/fields/EmailField';
import UiSwitch from '@Components/fields/UiSwitch';
import TestMessage from '@Components/fields/TestMessage';
import TextareaDropdownField from '@Components/fields/TextareaDropdownField';
import PasswordField from '@Components/fields/PasswordField';
import DynamicSelectField from '@Components/fields/DynamicSelectField';
import SelectSearchField from '@Components/fields/SelectSearchField';
import RollbackField from '@Components/fields/RollbackField';

const RenderFields = ( {
	data,
	value,
	isActive = true,
	handleChange,
	errors,
	autoSave = true,
	disableStyle = false,
} ) => {
	if ( ! isActive ) {
		return null;
	}

	switch ( data.type ) {
		case 'toggle':
			return (
				<ToggleField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					disabled={ data?.disabled }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'text':
			return (
				<TextField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					options={ data?.options || [] }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'subject_field':
			return (
				<SubjectField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					options={ data?.options || [] }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'textarea':
			return (
				<TextAreaField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'richtext':
			return (
				<RichTextField
					id={ data?.id || data?.name }
					title={ data.label }
					name={ data.name }
					value={ value }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'number':
			return (
				<NumberField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					min={ data.min }
					after={ data.after }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
					{ ...( data.className && { className: data.className } ) }
				/>
			);
		case 'select':
			return (
				<SelectField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					optionsArray={ data.options }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'dynamic-select':
			return (
				<DynamicSelectField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					placeholder={ data.placeholder }
					action={ data.action }
					actionNonce={ data.action_nonce }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'select-search':
			return (
				<SelectSearchField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					placeholder={ data.placeholder }
					action={ data.action }
					actionNonce={ data.action_nonce }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'multi-select':
			return (
				<MultiSelectField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					optionsArray={ data.options }
					placeholder={ data.placeholder }
					proUpgradeMessage={ data?.pro_upgrade_message }
					isPro={ data?.is_pro }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'webhook-url':
			return (
				<WebhookURLField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'time':
			return (
				<TimeField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					unitOptions={ data.unit_options }
					fields={ data.fields }
					min={ data.min }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'button':
			return (
				<DeleteCouponsField
					title={ data.label }
					description={ data.desc }
				/>
			);
		case 'product-search':
			return (
				<ProductSearchField
					id={ data.id }
					title={ data.label }
					description={ data.desc || data.description }
					name={ data.name }
					value={ value }
					placeholder={ data.placeholder }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'rule_engine':
			return (
				<RulesRepeater
					value={ value }
					onChange={ ( rules ) =>
						handleChange && handleChange( data.name, rules )
					}
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
				/>
			);
		case 'test_email':
			return (
				<TestEmail
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					{ ...( handleChange && { handleChange } ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'email':
			return (
				<EmailField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					options={ data?.options || [] }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'ui_switch':
			return (
				<UiSwitch
					title={ data.label }
					description={ data.desc }
					name={ data.name }
				/>
			);
		case 'test_message':
			return (
				<TestMessage
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					action={ data.action }
					actionNonce={ data.action_nonce }
					messageType={ data.message_type }
				/>
			);
		case 'textarea_dropdown':
			return (
				<TextareaDropdownField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					options={ data?.options || [] }
					search={ data?.search || false }
					countLimit={ data?.count_limit || null }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'password':
			return (
				<PasswordField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					isPro={ data?.is_pro }
					proUpgradeMessage={ data?.pro_upgrade_message }
					{ ...( handleChange && { handleChange } ) }
					{ ...( errors &&
						errors[ data.name ] && {
						error: errors[ data.name ],
					} ) }
					{ ...( autoSave !== undefined && { autoSave } ) }
					{ ...( disableStyle !== undefined && { disableStyle } ) }
				/>
			);
		case 'rollback':
			return (
				<RollbackField
					title={ data.label }
					description={ data.desc }
					name={ data.name }
					value={ value }
					options={ data?.options || [] }
				/>
			);
		default:
			return null;
	}
};

export default RenderFields;
