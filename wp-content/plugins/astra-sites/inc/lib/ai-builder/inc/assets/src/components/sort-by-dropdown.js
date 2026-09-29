import { __ } from '@wordpress/i18n';
import { ArrowsUpDownIcon } from '@heroicons/react/24/outline';
import DropdownList from './dropdown-list';
import { classNames } from '../helpers';

export const SORT_OPTIONS = [
	{ id: 'most-used', label: __( 'Default', 'ai-builder' ) },
	{ id: 'newest', label: __( 'Newest', 'ai-builder' ) },
	{ id: 'premium', label: __( 'Premium', 'ai-builder' ) },
];

const SortByDropdown = ( { value, onChange } ) => {
	const selectedOption =
		SORT_OPTIONS.find( ( opt ) => opt.id === value ) ?? SORT_OPTIONS[ 0 ];

	return (
		<DropdownList by="id" value={ selectedOption } onChange={ onChange }>
			<div className="relative">
				<DropdownList.Button className="flex items-center gap-2 h-10 px-3 border border-solid border-border-tertiary rounded-lg bg-white text-sm font-medium text-heading-text cursor-pointer hover:bg-[#F4F7FB]">
					<ArrowsUpDownIcon className="w-4 h-4 text-heading-text shrink-0" />
					<span className="truncate">{ selectedOption.label }</span>
				</DropdownList.Button>
				<DropdownList.Options className="py-1 px-1 w-[130px]">
					{ SORT_OPTIONS.map( ( option ) => (
						<DropdownList.Option
							key={ option.id }
							value={ option }
							className={ ( { selected } ) =>
								classNames(
									'py-2 px-3 rounded cursor-pointer',
									selected
										? 'bg-[#F4F7FB]'
										: 'hover:bg-[#F9FAFB]'
								)
							}
						>
							{ ( { selected } ) => (
								<span
									className={ classNames(
										'block text-sm',
										selected
											? 'font-semibold text-app-heading'
											: 'font-normal text-app-text'
									) }
								>
									{ option.label }
								</span>
							) }
						</DropdownList.Option>
					) ) }
				</DropdownList.Options>
			</div>
		</DropdownList>
	);
};

export default SortByDropdown;
