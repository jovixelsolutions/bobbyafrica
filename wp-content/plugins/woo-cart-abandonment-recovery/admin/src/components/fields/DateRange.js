import { Button, DatePicker } from '@bsf/force-ui';
import { CalendarIcon } from '@heroicons/react/24/outline';
import { useState, useRef, useEffect } from 'react';

const DateRange = ( {
	selectedRange,
	setSelectedRange,
	className,
	disabled,
} ) => {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ localRange, setLocalRange ] = useState( selectedRange );
	const datePickerRef = useRef( null );
	const popoverRef = useRef( null );

	// Sync local state with parent
	useEffect( () => {
		setLocalRange( selectedRange );
	}, [ selectedRange ] );

	// Close datepicker when clicking outside
	useEffect( () => {
		const handleClickOutside = ( event ) => {
			if (
				popoverRef.current &&
				! popoverRef.current.contains( event.target ) &&
				datePickerRef.current &&
				! datePickerRef.current.contains( event.target )
			) {
				setIsOpen( false );
			}
		};

		if ( isOpen ) {
			document.addEventListener( 'mousedown', handleClickOutside );
		}

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ isOpen ] );

	// Generate dynamic date ranges
	const getDateRanges = () => {
		const now = new Date();
		const today = new Date( now );

		// Yesterday
		const yesterday = new Date( now );
		yesterday.setDate( yesterday.getDate() - 1 );

		// This Week (Monday to Sunday)
		const thisWeekStart = new Date( now );
		const day = thisWeekStart.getDay();
		const diff = day === 0 ? 6 : day - 1; // Adjust for Monday (0 = Sunday, so Sunday - 6 = Monday)
		thisWeekStart.setDate( now.getDate() - diff );
		const thisWeekEnd = new Date( thisWeekStart );
		thisWeekEnd.setDate( thisWeekStart.getDate() + 6 );
		thisWeekEnd.setHours( 23, 59, 59, 999 );

		// Last Week
		const lastWeekStart = new Date( thisWeekStart );
		lastWeekStart.setDate( lastWeekStart.getDate() - 7 );
		const lastWeekEnd = new Date( lastWeekStart );
		lastWeekEnd.setDate( lastWeekStart.getDate() + 6 );
		lastWeekEnd.setHours( 23, 59, 59, 999 );

		// This Month
		const thisMonthStart = new Date( now.getFullYear(), now.getMonth(), 1 );
		const thisMonthEnd = new Date( now );
		thisMonthEnd.setHours( 23, 59, 59, 999 );

		// Last Month
		const lastMonthStart = new Date(
			now.getFullYear(),
			now.getMonth() - 1,
			1
		);
		const lastMonthEnd = new Date( now.getFullYear(), now.getMonth(), 0 );
		lastMonthEnd.setHours( 23, 59, 59, 999 );

		return [
			{
				label: 'Today',
				range: {
					from: today,
					to: today,
				},
			},
			{
				label: 'Yesterday',
				range: {
					from: yesterday,
					to: yesterday,
				},
			},
			{
				label: 'This Week',
				range: {
					from: thisWeekStart,
					to: thisWeekEnd,
				},
			},
			{
				label: 'Last Week',
				range: {
					from: lastWeekStart,
					to: lastWeekEnd,
				},
			},
			{
				label: 'This Month',
				range: {
					from: thisMonthStart,
					to: thisMonthEnd,
				},
			},
			{
				label: 'Last Month',
				range: {
					from: lastMonthStart,
					to: lastMonthEnd,
				},
			},
		];
	};

	// Add a function to format dates
	const formatDate = ( date ) => {
		return date.toLocaleDateString( 'en-US', {
			month: '2-digit',
			day: '2-digit',
			year: 'numeric',
		} );
	};

	// Add a function to get formatted date range text
	const getDateRangeText = () => {
		if ( ! localRange?.from || ! localRange?.to ) {
			return 'mm/dd/yyyy - mm/dd/yyyy';
		}
		return `${ formatDate( localRange.from ) } - ${ formatDate(
			localRange.to
		) }`;
	};

	const handleApply = ( range ) => {
		setSelectedRange( range );
		setIsOpen( false );
	};

	const handleCancel = () => {
		setIsOpen( false );
	};

	return (
		<div className="flex items-center gap-2">
			<Button
				className={ `whitespace-nowrap text-gray-500 ${ className }` }
				ref={ datePickerRef }
				icon={
					<CalendarIcon
						className="h-6 w-6 text-gray-500"
						aria-label="icon"
						role="img"
					/>
				}
				iconPosition="right"
				size="sm"
				tag="button"
				type="button"
				variant="outline"
				disabled={ disabled }
				onClick={ () => setIsOpen( ( prev ) => ! prev ) }
			>
				{ getDateRangeText() }
			</Button>
			{ isOpen && (
				<div
					ref={ popoverRef }
					className="absolute z-10 rounded-lg shadow-lg top-10 right-0 w-full lg:w-auto overflow-x-auto"
				>
					<DatePicker
						key="range"
						applyButtonText="Apply"
						cancelButtonText="Cancel"
						onApply={ handleApply }
						onCancel={ handleCancel }
						presets={ getDateRanges() }
						selected={ localRange }
						selectionType="range"
						showOutsideDays
						variant="presets"
					/>
				</div>
			) }
		</div>
	);
};

export default DateRange;

