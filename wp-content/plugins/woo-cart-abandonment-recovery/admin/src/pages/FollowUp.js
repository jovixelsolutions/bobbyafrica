/* eslint-disable no-mixed-spaces-and-tabs, indent, @wordpress/i18n-no-variables, @wordpress/i18n-no-collapsible-whitespace, @wordpress/i18n-translator-comments */
import { useState, useEffect } from 'react';
import {
	Title,
	Table,
	Pagination,
	Button,
	Select,
	Input,
	toast,
} from '@bsf/force-ui';
import {
	EyeIcon,
	UserMinusIcon,
	TrashIcon,
	MagnifyingGlassIcon,
	XMarkIcon,
	ExclamationTriangleIcon,
	BookmarkSlashIcon,
	NoSymbolIcon,
	ShieldCheckIcon,
} from '@heroicons/react/24/outline';
import { __, sprintf } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';

import { doApiFetch, useStateValue, ActionTypes } from '@Store';

import SectionWrapper from '@Components/common/SectionWrapper';
import OrderStatusBadge from '@Components/common/OrderStatusBadge';
import DateRange from '@Components/fields/DateRange';
import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import { EmptyBlock } from '@Components/common/empty-blocks';
import ExportToExcel from '@Components/common/ExportToExcel';
import AppTooltip from '@Components/common/AppTooltip';
import ConfirmationModal from '@Components/common/ConfirmationModal';
import { generatePaginationPages } from '@Admin/utils/helper';
import { useProAccess } from '@Components/pro/useProAccess';

const FollowUp = () => {
	const [ selected, setSelected ] = useState( [] );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ itemsPerPage, setItemsPerPage ] = useState( 20 );
	const [ searchText, setSearchText ] = useState( '' );
	const [ selectedOption, setSelectedOption ] = useState( {
		id: 'all',
		title: __( 'All', 'woo-cart-abandonment-recovery' ),
	} );
	const [ selectedRange, setSelectedRange ] = useState( {
		from: null,
		to: null,
	} );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ data, setData ] = useState( [] );
	const [ state, dispatch ] = useStateValue();
	const storeFollowUpData = state?.followUpData || null;
	const navigate = useNavigate();
	const [ deleteModal, setDeleteModal ] = useState( {
		isOpen: false,
		type: null,
		id: null,
	} );
	const [ isDeleting, setIsDeleting ] = useState( false );
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();
	const notAvailableString = __(
		'Not Available',
		'woo-cart-abandonment-recovery'
	);

	// Load data
	useEffect( () => {
		if ( storeFollowUpData ) {
			setData( storeFollowUpData );
			setIsLoading( false );
		} else {
			const fetchRecords = async () => {
				setIsLoading( true );
				await doApiFetch(
					'/wcar/api/admin/follow-up/',
					{},
					'POST',
					( response ) => {
						setData( response.items || [] );
						dispatch( {
							type: 'FETCH_FOLLOWUP_DATA_SUCCESS',
							followUpData: response.items || [],
						} );
						setIsLoading( false );
					},
					() => {
						setIsLoading( false );
					}
				);
			};
			fetchRecords();
		}
	}, [] );

	// Handler for date range changes
	const handleDateRangeChange = ( range ) => {
		setSelectedRange( range );
		setCurrentPage( 1 ); // Reset to first page when date range changes
	};

	const handleCheckboxChange = ( checked, value ) => {
		if ( checked ) {
			setSelected( [ ...selected, value.id ] );
		} else {
			setSelected( selected.filter( ( item ) => item !== value.id ) );
		}
	};

	const toggleSelectAll = ( checked ) => {
		if ( checked ) {
			setSelected( filteredData.map( ( item ) => item.id ) );
		} else {
			setSelected( [] );
		}
	};

	const filterOptions = [
		{
			id: 'all',
			title: __( 'All', 'woo-cart-abandonment-recovery' ),
		},
		{
			id: 'abandoned_orders',
			title: __( 'Abandoned Orders', 'woo-cart-abandonment-recovery' ),
		},
		{
			id: 'recovered_orders',
			title: __( 'Recovered Orders', 'woo-cart-abandonment-recovery' ),
		},
		{
			id: 'lost_orders',
			title: __( 'Lost Orders', 'woo-cart-abandonment-recovery' ),
		},
		{
			id: 'blacklisted',
			title: __( 'Blacklisted', 'woo-cart-abandonment-recovery' ),
		},
	];

	// Filter data based on search text and selected option
	const filteredData = data.filter( ( item ) => {
		// Search filter - check username and email
		const searchMatch =
			searchText === '' ||
			item.userName.toLowerCase().includes( searchText.toLowerCase() ) ||
			item.email.toLowerCase().includes( searchText.toLowerCase() );

		// Status filter
		let statusMatch = true;
		if ( selectedOption?.id !== 'all' ) {
			switch ( selectedOption.id ) {
				case 'abandoned_orders':
					statusMatch = item.orderStatus === 'Abandoned';
					break;
				case 'recovered_orders':
					statusMatch = item.orderStatus === 'Successful';
					break;
				case 'lost_orders':
					statusMatch = item.orderStatus === 'Failed';
					break;
				case 'blacklisted':
					statusMatch = item.orderStatus === 'Blacklisted';
					break;
				default:
					statusMatch = true;
			}
		}

		// Date range filter
		let dateMatch = true;
		if ( selectedRange.from || selectedRange.to ) {
			const itemDate = new Date( item.dateTime );

			if ( selectedRange.from ) {
				const fromDate = new Date( selectedRange.from );
				fromDate.setHours( 0, 0, 0, 0 );
				dateMatch = dateMatch && itemDate >= fromDate;
			}

			if ( selectedRange.to ) {
				const toDate = new Date( selectedRange.to );
				toDate.setHours( 23, 59, 59, 999 );
				dateMatch = dateMatch && itemDate <= toDate;
			}
		}

		return searchMatch && statusMatch && dateMatch;
	} );

	// Calculate pagination
	const indexOfLastItem = currentPage * itemsPerPage;
	const indexOfFirstItem = indexOfLastItem - itemsPerPage;
	const currentItems = filteredData.slice(
		indexOfFirstItem,
		indexOfLastItem
	);
	const totalPages = Math.ceil( filteredData.length / itemsPerPage );

	const filtersApplied =
		searchText !== '' ||
		selectedOption?.id !== 'all' ||
		selectedRange.from ||
		selectedRange.to;

	const handleClearFilters = () => {
		setSelectedOption( {
			id: 'all',
			title: __( 'All', 'woo-cart-abandonment-recovery' ),
		} );
		setSearchText( '' );
		setSelectedRange( {
			from: undefined,
			to: undefined,
		} );
		setCurrentPage( 1 );
	};

	const handlePageChange = ( page ) => {
		setCurrentPage( page );
	};

	const handleItemsPerPageChange = ( value ) => {
		setItemsPerPage( Number( value ) );
		setCurrentPage( 1 ); // Reset to first page when changing items per page
	};

	// Handle search
	const handleSelectedOptionChange = ( value ) => {
		setSelectedOption( value );
		setCurrentPage( 1 ); // Reset to first page when filter changes
	};

	const handleSearch = ( value ) => {
		setSearchText( value );
		setCurrentPage( 1 ); // Reset to first page when search changes
	};

	// Handle delete trigger
	const handleDeleteTrigger = () => {
		if ( selected.length === 0 ) {
			return;
		}
		setDeleteModal( { isOpen: true, type: 'bulk', id: null } );
	};

	// Handle bulk delete
	const handleBulkDelete = () => {
		setIsDeleting( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.delete_follow_up_reports_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_delete_follow_up_reports' );
		selected.forEach( ( id ) => formData.append( 'ids[]', id ) );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					const updated = data.filter(
						( record ) => ! selected.includes( record.id )
					);
					setData( updated );
					dispatch( {
						type: 'FETCH_FOLLOWUP_DATA_SUCCESS',
						followUpData: updated,
					} );
					setSelected( [] );
					toast.success(
						__(
							'Record(s) deleted',
							'woo-cart-abandonment-recovery'
						)
					);
				} else {
					toast.error(
						__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			( error ) => {
				toast.error(
					__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error.data?.message || '',
					}
				);
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			true,
			false
		);
	};

	// Handle single delete
	const handleSingleDelete = () => {
		setIsDeleting( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.delete_follow_up_reports_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_delete_follow_up_reports' );
		formData.append( 'ids[]', deleteModal.id );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					const updated = data.filter(
						( record ) => record.id !== deleteModal.id
					);
					setData( updated );
					dispatch( {
						type: 'FETCH_FOLLOWUP_DATA_SUCCESS',
						followUpData: updated,
					} );
					toast.success(
						__( 'Record deleted', 'woo-cart-abandonment-recovery' )
					);
				} else {
					toast.error(
						__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			( error ) => {
				toast.error(
					__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error.data?.message || '',
					}
				);
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			true,
			false
		);
	};

	const handleConfirmDelete = () => {
		if ( deleteModal.type === 'bulk' ) {
			handleBulkDelete();
		} else {
			handleSingleDelete();
		}
	};

	const handleUnsubscribe = ( id ) => {
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce =
			cart_abandonment_admin?.unsubscribe_follow_up_reports_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_unsubscribe_follow_up_reports' );
		formData.append( 'ids[]', id );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					const updated = data.map( ( record ) => {
						if ( record.id === id ) {
							record.unsubscribed = 1;
						}
						return record;
					} );
					setData( updated );
					dispatch( {
						type: 'FETCH_FOLLOWUP_DATA_SUCCESS',
						followUpData: updated,
					} );
					toast.success(
						__(
							'Unsubscribed Successfully',
							'woo-cart-abandonment-recovery'
						)
					);
				} else {
					toast.error(
						__(
							'Unsubscribe failed',
							'woo-cart-abandonment-recovery'
						),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error(
					__( 'Unsubscribe failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error.data?.message || '',
					}
				);
			},
			true,
			false
		);
	};

	const handleBlacklist = ( id, action ) => {
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce =
			'blacklist' === action
				? cart_abandonment_admin?.blacklist_cart_nonce
				: cart_abandonment_admin?.whitelist_cart_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_pro_' + action + '_cart' );
		formData.append( 'id', id );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					const newStatus =
						'blacklist' === action ? 'Blacklisted' : 'Abandoned';
					const updated = data.map( ( record ) => {
						if ( record.id === id ) {
							return { ...record, orderStatus: newStatus };
						}
						return record;
					} );
					setData( updated );
					dispatch( {
						type: 'FETCH_FOLLOWUP_DATA_SUCCESS',
						followUpData: updated,
					} );
					// Sync the blacklist setting in the store so the Settings
					// page reflects the latest list without a page reload.
					if ( response.data?.blacklist_list !== undefined ) {
						dispatch( {
							type: ActionTypes.UPDATE_SETTINGS_DATA,
							payload: {
								option: 'wcf_ca_email_blacklist_list',
								value: response.data.blacklist_list,
							},
						} );
					}
					toast.success(
						'blacklist' === action
							? __(
									'Cart blacklisted successfully',
									'woo-cart-abandonment-recovery'
							  )
							: __(
									'Cart whitelisted successfully',
									'woo-cart-abandonment-recovery'
							  )
					);
				} else {
					toast.error(
						'blacklist' === action
							? __(
									'Blacklist failed',
									'woo-cart-abandonment-recovery'
							  )
							: __(
									'Whitelist failed',
									'woo-cart-abandonment-recovery'
							  ),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error(
					'blacklist' === action
						? __(
								'Blacklist failed',
								'woo-cart-abandonment-recovery'
						  )
						: __(
								'Whitelist failed',
								'woo-cart-abandonment-recovery'
						  ),
					{
						description: error.data?.message || '',
					}
				);
			},
			true,
			false
		);
	};

	// Handle detailed screen navigation
	const handleDetailedScreen = ( id ) => {
		navigate( {
			search: `?page=woo-cart-abandonment-recovery&path=detailed-report&id=${ id }`,
		} );
	};

	const handleCancelSelect = () => {
		setSelected( [] );
	};

	return (
		<div className="p-4 md:p-8">
			<SectionWrapper className="flex flex-col gap-4">
				<div className="flex flex-col xl:flex-row justify-between relative">
					<div className="flex gap-4 items-center">
						<Title
							size="sm"
							tag="h1"
							title={ __(
								'Follow Up Reports',
								'woo-cart-abandonment-recovery'
							) }
							className="[&_h2]:text-gray-900"
						/>
						{ ! isLoading && selected.length > 0 && (
							<div className="flex gap-4 items-center border-0 border-l border-solid border-gray-200">
								<Button
									variant="ghost"
									icon={
										<XMarkIcon className="h-6 w-6 text-gray-500" />
									}
									size="xs"
									className="text-icon-secondary hover:text-icon-primary"
									onClick={ handleCancelSelect }
								/>
								<span className="text-sm font-normal text-gray-500">
									{ sprintf(
										__(
											'%d Selected',
											'woo-cart-abandonment-recovery'
										),
										selected.length
									) }
								</span>
								<Button
									className="py-2 px-4 bg-red-50 text-red-600 outline-red-600 hover:bg-red-50 hover:outline-red-600"
									size="sm"
									tag="button"
									type="button"
									variant="outline"
									icon={ <TrashIcon className="h-4 w-4" /> }
									iconPosition="left"
									onClick={ handleDeleteTrigger }
								>
									Delete
								</Button>
							</div>
						) }
					</div>
					<div className="flex flex-col lg:flex-row gap-4">
						{ filtersApplied && (
							<Button
								variant="link"
								size="xs"
								icon={ <XMarkIcon className="h-4 w-4" /> }
								onClick={ handleClearFilters }
								className="text-red-500 no-underline whitespace-nowrap focus:ring-0 [box-shadow:none] focus:[box-shadow:none] hover:no-underline hover:text-red-500"
								aria-label={ __(
									'Clear Filters',
									'woo-cart-abandonment-recovery'
								) }
							>
								{ __(
									'Clear Filters',
									'woo-cart-abandonment-recovery'
								) }
							</Button>
						) }

						<Input
							placeholder="Search..."
							prefix={
								<MagnifyingGlassIcon className="h-6 w-6 text-gray-500" />
							}
							size="sm"
							type="text"
							aria-label={ __(
								'Search',
								'woo-cart-abandonment-recovery'
							) }
							value={ searchText }
							onChange={ handleSearch }
							className="w-full lg:w-52"
							disabled={ isLoading }
						/>
						<Select
							onChange={ handleSelectedOptionChange }
							size="sm"
							value={ selectedOption.title }
							disabled={ isLoading }
						>
							<Select.Button className="w-full lg:w-52 text-gray-500" />
							<Select.Options>
								{ filterOptions.map( ( option ) => (
									<Select.Option
										key={ option.id }
										value={ option }
									>
										{ option.title }
									</Select.Option>
								) ) }
							</Select.Options>
						</Select>
						<DateRange
							selectedRange={ selectedRange }
							setSelectedRange={ handleDateRangeChange }
							disabled={ isLoading }
						/>
						<ExportToExcel
							data={ data }
							filename="followUpReport"
						/>
					</div>
				</div>
				{ isLoading ? (
					<div className="flex flex-col gap-4">
						<SkeletonLoader height="40px" />
						{ [ ...Array( 5 ) ].map( ( _, index ) => (
							<SkeletonLoader key={ index } height="50px" />
						) ) }
					</div>
				) : data.length === 0 ? (
					<EmptyBlock
						className="my-2"
						icon={
							<ExclamationTriangleIcon className="h-12 w-12 text-yellow-500" />
						}
						title={ __(
							'No Follow-Up Reports Yet',
							'woo-cart-abandonment-recovery'
						) }
						description={ __(
							'Reports will show up here once customers start abandoning their carts.',
							'woo-cart-abandonment-recovery'
						) }
					/>
				) : (
					<Table
						checkboxSelection={ true }
						className="whitespace-nowrap sm:whitespace-normal"
					>
						<Table.Head
							selected={ selected.length > 0 }
							onChangeSelection={ toggleSelectAll }
							indeterminate={
								selected.length > 0 &&
								selected.length < filteredData.length
							}
						>
							<Table.HeadCell>
								{ __(
									'User Name',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
							<Table.HeadCell>
								{ __(
									'Email To',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
							<Table.HeadCell>
								{ __(
									'Cart Total',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
							<Table.HeadCell>
								{ __(
									'Order Status',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
							<Table.HeadCell>
								{ __(
									'Date & Time',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
							<Table.HeadCell className="text-right">
								{ __(
									'Actions',
									'woo-cart-abandonment-recovery'
								) }
							</Table.HeadCell>
						</Table.Head>
						{ currentItems.length > 0 ? (
							<Table.Body>
								{ currentItems.map( ( item, index ) => (
									<Table.Row
										key={ index }
										value={ item }
										selected={ selected.includes(
											item.id
										) }
										onChangeSelection={
											handleCheckboxChange
										}
									>
										<Table.Cell>
											<span
												className="cursor-pointer hover:text-flamingo-400 focus-visible:text-flamingo-400"
												onClick={ () =>
													handleDetailedScreen(
														item.id
													)
												}
												onKeyDown={ ( e ) => {
													if (
														e.key === 'Enter' ||
														e.key === ' '
													) {
														handleDetailedScreen(
															item.id
														);
													}
												} }
												role="button"
												tabIndex={ 0 }
											>
												{ item.userName }
											</span>
										</Table.Cell>
										<Table.Cell>
											{ item?.email ||
												notAvailableString }
										</Table.Cell>
										<Table.Cell>
											{ item?.cartTotal ||
												notAvailableString }
										</Table.Cell>
										<Table.Cell>
											<OrderStatusBadge
												status={
													item?.orderStatus ||
													notAvailableString
												}
											/>
										</Table.Cell>
										<Table.Cell>
											{ item?.dateTime ||
												notAvailableString }
										</Table.Cell>
										<Table.Cell>
											<div className="flex items-center justify-end gap-2">
												<AppTooltip
													content="View"
													position="top"
												>
													<Button
														variant="ghost"
														icon={
															<EyeIcon className="h-6 w-6" />
														}
														size="xs"
														className="text-gray-500 hover:text-flamingo-400"
														aria-label="View"
														onClick={ () =>
															handleDetailedScreen(
																item.id
															)
														}
													/>
												</AppTooltip>
												{ 0 === item.unsubscribed ? (
													<AppTooltip
														content={ __(
															'Unsubscribe',
															'woo-cart-abandonment-recovery'
														) }
														position="top"
													>
														<Button
															variant="ghost"
															icon={
																<UserMinusIcon className="h-6 w-6" />
															}
															size="xs"
															className="text-gray-500 hover:text-flamingo-400"
															aria-label={ __(
																'Unsubscribe',
																'woo-cart-abandonment-recovery'
															) }
															onClick={ () =>
																handleUnsubscribe(
																	item.id
																)
															}
														/>
													</AppTooltip>
												) : (
													<AppTooltip
														content={ __(
															'Unsubscribed',
															'woo-cart-abandonment-recovery'
														) }
														position="top"
													>
														<Button
															variant="ghost"
															icon={
																<BookmarkSlashIcon
																	className="h-6 w-6"
																	aria-label={ __(
																		'Unsubscribed',
																		'woo-cart-abandonment-recovery'
																	) }
																	disabled={
																		true
																	}
																/>
															}
															size="xs"
															className="text-gray-500 cursor-not-allowed opacity-50"
															aria-label={ __(
																'Unsubscribed',
																'woo-cart-abandonment-recovery'
															) }
														/>
													</AppTooltip>
												) }
												{ ! isFeatureBlocked && (
													<AppTooltip
														content={ __(
															'Blacklisted' ===
																item?.orderStatus
																? 'Whitelist'
																: 'Blacklist',
															'woo-cart-abandonment-recovery'
														) }
														position="top"
													>
														<Button
															variant="ghost"
															icon={
																'Blacklisted' ===
																item?.orderStatus ? (
																	<ShieldCheckIcon className="h-6 w-6" />
																) : (
																	<NoSymbolIcon className="h-6 w-6" />
																)
															}
															size="xs"
															className="text-gray-500 hover:text-flamingo-400"
															aria-label={ __(
																'Blacklisted' ===
																	item?.orderStatus
																	? 'Whitelist'
																	: 'Blacklist',
																'woo-cart-abandonment-recovery'
															) }
															onClick={ () =>
																handleBlacklist(
																	item.id,
																	'Blacklisted' ===
																		item?.orderStatus
																		? 'whitelist'
																		: 'blacklist'
																)
															}
														/>
													</AppTooltip>
												) }
												<AppTooltip
													content="Delete"
													position="top"
												>
													<Button
														variant="ghost"
														icon={
															<TrashIcon className="h-6 w-6" />
														}
														size="xs"
														className="text-gray-500 hover:text-red-600"
														aria-label="Delete"
														onClick={ () =>
															setDeleteModal( {
																isOpen: true,
																type: 'single',
																id: item.id,
															} )
														}
													/>
												</AppTooltip>
											</div>
										</Table.Cell>
									</Table.Row>
								) ) }
							</Table.Body>
						) : (
							<tr>
								<td
									colSpan="7"
									className="p-4 w-full text-center"
								>
									{ __(
										'No matching data available',
										'woo-cart-abandonment-recovery'
									) }
								</td>
							</tr>
						) }
						<Table.Footer>
							<div className="flex items-center justify-between w-full">
								<div className="flex items-center gap-2">
									<span className="text-sm font-normal leading-5 text-text-secondary whitespace-nowrap">
										{ __(
											'Items per page:',
											'woo-cart-abandonment-recovery'
										) }
									</span>
									<Select
										onChange={ handleItemsPerPageChange }
										size="md"
										value={ itemsPerPage.toString() }
									>
										<Select.Button />
										<Select.Options>
											<Select.Option value="5">
												5
											</Select.Option>
											<Select.Option value="10">
												10
											</Select.Option>
											<Select.Option value="20">
												20
											</Select.Option>
											<Select.Option value="50">
												50
											</Select.Option>
										</Select.Options>
									</Select>
								</div>
								<Pagination className="w-fit">
									<Pagination.Content>
										<Pagination.Previous
											onClick={ () => {
												if ( currentPage > 1 ) {
													handlePageChange(
														currentPage - 1
													);
												}
											} }
											disabled={ currentPage === 1 }
										/>
										{ generatePaginationPages(
											totalPages,
											currentPage
										).map( ( page, index ) => {
											if (
												page === 'ellipsis' ||
												page === 'ellipsis-end'
											) {
												return (
													<Pagination.Ellipsis
														key={ `ellipsis-${ index }` }
													/>
												);
											}

											return (
												<Pagination.Item
													key={ page }
													isActive={
														currentPage === page
													}
													onClick={ () =>
														handlePageChange( page )
													}
													className={ `${
														currentPage === page
															? 'bg-flamingo-50 text-flamingo-400'
															: ''
													}` }
												>
													{ page }
												</Pagination.Item>
											);
										} ) }
										<Pagination.Next
											onClick={ () => {
												if (
													currentPage < totalPages
												) {
													handlePageChange(
														currentPage + 1
													);
												}
											} }
											disabled={
												currentPage === totalPages
											}
										/>
									</Pagination.Content>
								</Pagination>
							</div>
						</Table.Footer>
					</Table>
				) }
				<ConfirmationModal
					isOpen={ deleteModal.isOpen }
					onClose={ () =>
						setDeleteModal( {
							isOpen: false,
							type: null,
							id: null,
						} )
					}
					onConfirm={ handleConfirmDelete }
					title={
						deleteModal.type === 'bulk'
							? __(
									'Delete Selected Records',
									'woo-cart-abandonment-recovery'
							  )
							: __(
									'Delete Record',
									'woo-cart-abandonment-recovery'
							  )
					}
					message={
						deleteModal.type === 'bulk'
							? sprintf(
									/* translators: %s: number of selected records */
									__(
										'Are you sure you want to delete %s selected record(s)? This action cannot be undone.',
										'woo-cart-abandonment-recovery'
									),
									selected.length
							  )
							: __(
									'Are you sure you want to delete this record? This action cannot be undone.',
									'woo-cart-abandonment-recovery'
							  )
					}
					isLoading={ isDeleting }
				/>
			</SectionWrapper>
		</div>
	);
};

export default FollowUp;
