import React, { useState, useEffect } from 'react';
import parse from 'html-react-parser';
import { Tooltip } from '@WizardFields';
import classnames from 'classnames';

function Checkbox( props ) {
	const {
		name,
		id,
		label,
		value,
		desc,
		backComp = false,
		tooltip,
		onClick,
		notice,
		isDisabled = false,
	} = props;

	const [ inputvalue, setInputvalue ] = useState( value );

	useEffect( () => {
		setInputvalue( value );
	}, [ value ] );

	const checkedvalue = backComp ? 'enable' : 'yes';
	const uncheckedvalue = backComp ? 'disable' : 'no';

	function handleCheckboxClick( e ) {
		let current_value = 'no';

		if ( e.target.checked ) {
			// Check is there any notice added in the checkbox.
			if ( notice && ! show_notice( notice ) ) {
				return;
			}

			setInputvalue( checkedvalue );
			current_value = checkedvalue;
		} else {
			setInputvalue( uncheckedvalue );
			current_value = uncheckedvalue;
		}

		// Trigger change
		const changeEvent = new CustomEvent( 'wcf:checkbox:change', {
			bubbles: true,
			detail: { e, name, value: current_value },
		} );

		document.dispatchEvent( changeEvent );
	}
	function onChangeHandle( e ) {
		if ( onClick ) {
			onClick( e );
		}
	}

	// Function to show desired alert box to get the confirmation from the user input.
	function show_notice() {
		switch ( notice.type ) {
			case 'alert':
				alert( notice.message );
				return true;
			case 'confirm':
				const is_confirm = confirm( notice.message );
				return is_confirm ? true : false;
			case 'prompt':
				const is_prompt = prompt( notice.message );
				return is_prompt === notice.check.toUpperCase() ? true : false;
			default:
				return false;
		}
	}

	return (
		<div className="wcf-checkbox-field text-left">
			<div className="">
				<div className="flex items-center gap-2">
					<input
						type="hidden"
						name={ name }
						defaultValue={ uncheckedvalue }
					/>
					<input
						type="checkbox"
						className={ classnames(
							props.class,
							'!h-5 !w-5 !rounded !border-gray-300 !text-primary-600 focus:!ring-primary-600 !shadow-none before:!content-none !outline-none !m-0'
						) }
						name={ name }
						value={ inputvalue }
						id={ id ? id : name }
						checked={ checkedvalue === inputvalue ? 'checked' : '' }
						onClick={ handleCheckboxClick }
						onChange={ onChangeHandle }
						disabled={ isDisabled }
					/>
					{ label && (
						<div className="text-sm font-medium text-left">
							<label htmlFor={ id ? id : name }>
								{ label }
								{ tooltip && <Tooltip text={ tooltip } /> }
							</label>
						</div>
					) }
				</div>
			</div>

			{ desc && (
				<div className="wcf-field__desc text-[13px] font-normal text-gray-500 mt-2">
					{ parse( desc ) }
				</div>
			) }
		</div>
	);
}

export default Checkbox;
