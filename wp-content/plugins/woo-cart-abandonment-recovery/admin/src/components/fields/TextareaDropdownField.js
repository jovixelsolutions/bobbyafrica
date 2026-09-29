import { useState, useEffect, useRef } from 'react';
import { useStateValue } from '@Store';
import useDebounceDispatch from '@Utils/debounceDispatch';
import { TextArea, Button, DropdownMenu, SearchBox } from '@bsf/force-ui';
import { EllipsisVerticalIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';

const TextareaDropdownField = ( {
	title,
	description,
	name,
	value,
	handleChange,
	countLimit = null,
	options = [],
	search = false,
	autoSave = true,
	error,
} ) => {
	const [ state, dispatch ] = useStateValue();
	const settingsData = state.settingsData || {};
	const settingsValues = settingsData.values || {};

	// Use provided value when handleChange is provided (form mode)
	// Otherwise use global state value (auto-save mode)
	const currentValue =
		handleChange && ! autoSave
			? value ?? ''
			: settingsValues[ name ] ?? value ?? '';
	const [ textValue, setTextValue ] = useState( currentValue );
	const [ searchText, setSearchText ] = useState( '' );

	const debouncedUpdate = useDebounceDispatch(
		dispatch,
		name,
		undefined,
		1000,
		true
	);
	const inputRef = useRef( null );

	useEffect( () => {
		const newValue =
			handleChange && ! autoSave
				? value ?? ''
				: settingsValues[ name ] ?? value ?? '';
		setTextValue( newValue );
	}, [ settingsValues[ name ], value, handleChange, autoSave ] );

	function handleOnChange( val ) {
		setTextValue( val );

		// If handleChange is provided and autoSave is false, use form mode
		if ( handleChange && ! autoSave ) {
			handleChange( name, String( val ) );
			return;
		}

		// Otherwise use auto-save mode
		if ( autoSave ) {
			debouncedUpdate( String( val ) );
		}
	}

	const handleDropdownClick = ( optionValue ) => {
		const input = inputRef.current;

		if ( ! input ) {
			return;
		}

		const start = input.selectionStart;
		const end = input.selectionEnd;

		const newValue =
			textValue.substring( 0, start ) +
			optionValue +
			textValue.substring( end );
		setTextValue( newValue );

		// If handleChange is provided and autoSave is false, use form mode
		if ( handleChange && ! autoSave ) {
			handleChange( name, newValue );
			return;
		}

		// Otherwise use auto-save mode
		if ( autoSave ) {
			debouncedUpdate( newValue );
		}
	};

	const handleSearch = ( val ) => {
		setSearchText( val );
	};

	// Filter options based on search text
	const filteredOptions = searchText
		? options.filter( ( option ) =>
			option.value?.toLowerCase().includes( searchText.toLowerCase() )
		)
		: options;

	return (
		<FieldWrapper title={ title } description={ description } type="block">
			<div className="flex gap-2">
				<div className="grow">
					<TextArea
						className={ `w-full h-40 focus:[&>input]:ring-focus ${
							countLimit !== null &&
							textValue.length > countLimit &&
							'border-red-500 focus:border-red-500 hover:border-red-500'
						}` }
						type="text"
						size="md"
						name={ name }
						ref={ inputRef }
						value={ textValue }
						onChange={ handleOnChange }
					/>
					{ countLimit !== null && (
						<div
							className={ `flex ${
								textValue.length > countLimit
									? 'justify-between'
									: 'justify-end'
							} mt-1` }
						>
							{ textValue.length > countLimit && (
								<span className="text-red-500 text-sm">
									{ __(
										'Character limit exceeded',
										'woo-cart-abandonment-recovery'
									) }
								</span>
							) }
							<span
								className={ `text-sm ${
									textValue.length > countLimit
										? 'text-red-500'
										: textValue.length > countLimit * 0.9
											? 'text-yellow-500'
											: 'text-gray-500'
								}` }
							>
								{ textValue.length } / { countLimit }
							</span>
						</div>
					) }

					{ error && (
						<p className="text-red-500 text-sm mt-1">{ error }</p>
					) }
				</div>
				{ options.length > 0 && (
					<DropdownMenu placement="bottom-end">
						<DropdownMenu.Trigger>
							<span className="sr-only">
								{ __(
									'Open Dropdown',
									'woo-cart-abandonment-recovery'
								) }
							</span>
							<Button
								size="md"
								type="button"
								variant="outline"
								icon={
									<EllipsisVerticalIcon className="h-6 w-6 text-gray-500" />
								}
								iconPosition="left"
							/>
						</DropdownMenu.Trigger>
						<DropdownMenu.ContentWrapper className="z-50 max-h-72 overflow-y-auto">
							<DropdownMenu.Content className="w-72">
								{ search && (
									<SearchBox size="sm">
										<SearchBox.Input
											ref={ {
												current: '[Circular]',
											} }
											className="mb-2 box-border"
											onChange={ handleSearch }
										/>
									</SearchBox>
								) }
								<DropdownMenu.List>
									{ filteredOptions.length > 0 ? (
										filteredOptions.map(
											( option, index ) => (
												<DropdownMenu.Item
													key={ index }
													onClick={ () =>
														handleDropdownClick(
															option.value
														)
													}
												>
													{ option.text }
												</DropdownMenu.Item>
											)
										)
									) : (
										<div className="text-center">
											No options available
										</div>
									) }
								</DropdownMenu.List>
							</DropdownMenu.Content>
						</DropdownMenu.ContentWrapper>
					</DropdownMenu>
				) }
			</div>
		</FieldWrapper>
	);
};

export default TextareaDropdownField;
