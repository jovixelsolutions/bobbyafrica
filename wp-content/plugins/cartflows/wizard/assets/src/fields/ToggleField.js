import React, { useState, useEffect } from 'react';
import { Tooltip } from '@WizardFields';
import parse from 'html-react-parser';
import classnames from 'classnames';

function ToggleField( { onChange, ...props } ) {
	const {
		name,
		id,
		label,
		value,
		backComp = false,
		onClick,
		isDisabled = false,
		tooltip,
		desc,
	} = props;

	const [ inputvalue, setInputvalue ] = useState( value );

	useEffect( () => {
		setInputvalue( value );
	}, [ value ] );

	const checkedvalue = backComp ? 'enable' : 'yes';
	const uncheckedvalue = backComp ? 'disable' : 'no';

	function handleCheckboxClick( e ) {
		let current_value = 'no';

		if ( uncheckedvalue === inputvalue ) {
			setInputvalue( checkedvalue );
			current_value = checkedvalue;
		} else {
			setInputvalue( uncheckedvalue );
			current_value = uncheckedvalue;
		}

		// Trigger change
		const changeEvent = new CustomEvent( 'wcf:toggle:change', {
			bubbles: true,
			detail: { e, name, value: current_value },
		} );

		document.dispatchEvent( changeEvent );

		// Notify parent component about the change
		if ( onChange ) {
			onChange( { name, value: current_value } );
		}
	}
	function onChangeHandle() {
		if ( onClick ) {
			onClick();
		}
	}

	return (
		<div className={ 'wcf-field wcf-toggle-field' }>
			<div className={ 'wcf-field__data gap-2' }>
				<div className="wcf-field__data--content-left">
					{ label && (
						<label className="flex items-center gap-0.5">
							{ label }
							{ tooltip && <Tooltip text={ tooltip } /> }
						</label>
					) }
					{ desc && (
						<div className={ `wcf-field__desc text-gray-400` }>
							{ parse( desc ) }
						</div>
					) }
				</div>
				<div className="wcf-field__data--content-right">
					<div className="flex justify-center">
						<button
							type="button"
							// name={ name }
							// value={ inputvalue }
							id={ id ? id : name }
							className={ classnames(
								props.class,
								'bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2',
								checkedvalue === inputvalue
									? 'bg-primary-600'
									: 'bg-gray-200',
								isDisabled ? '!bg-primary-300' : ''
							) }
							disabled={ isDisabled }
							role="switch"
							onClick={ handleCheckboxClick }
							onChange={ onChangeHandle }
						>
							<input
								type="hidden"
								className={ props.class }
								name={ name }
								value={ inputvalue }
							/>
							<span
								aria-hidden="true"
								className={ classnames(
									checkedvalue === inputvalue
										? 'translate-x-5'
										: 'translate-x-0',
									'translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
								) }
							></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	);
}

export default ToggleField;
