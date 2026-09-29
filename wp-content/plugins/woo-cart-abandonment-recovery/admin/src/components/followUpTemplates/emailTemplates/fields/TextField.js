import { Input, Button, DropdownMenu } from '@bsf/force-ui';
import { EllipsisVerticalIcon } from '@heroicons/react/24/outline';

import FieldWrapper from '@Components/common/FieldWrapper';

const TextField = ( {
	title,
	description,
	name,
	value,
	handleChange,
	optionsArray = [],
} ) => {
	const handleOnChange = ( val ) => {
		handleChange( name, val );
	};

	const handleDropdownClick = ( optionValue ) => {
		const textValue = value + optionValue;
		handleOnChange( textValue );
	};

	return (
		<FieldWrapper
			title={ title }
			description={ description }
			type="block"
			size="small"
		>
			<div className="flex gap-2">
				<div className="grow">
					<Input
						className="w-full focus:[&>input]:ring-focus"
						type="text"
						size="md"
						name={ name }
						value={ value }
						onChange={ handleOnChange }
					/>
				</div>
				{ optionsArray.length > 0 && (
					<DropdownMenu placement="bottom-end">
						<DropdownMenu.Trigger>
							<span className="sr-only">Open Dropdown</span>
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
						<DropdownMenu.ContentWrapper className="z-50">
							<DropdownMenu.Content>
								<DropdownMenu.List>
									{ optionsArray.map( ( option, index ) => (
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
									) ) }
								</DropdownMenu.List>
							</DropdownMenu.Content>
						</DropdownMenu.ContentWrapper>
					</DropdownMenu>
				) }
			</div>
		</FieldWrapper>
	);
};

export default TextField;
