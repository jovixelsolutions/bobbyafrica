import { useState, useEffect } from 'react';
import { Table, Input, Button, Switch, toast } from '@bsf/force-ui';
import { __, sprintf } from '@wordpress/i18n';
import {
	TrashIcon,
	MagnifyingGlassIcon,
	PlusIcon,
	XMarkIcon,
	PencilIcon,
	DocumentDuplicateIcon,
	ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import { doApiFetch } from '@Store';
import { useProAccess } from '@Components/pro/useProAccess';

import SkeletonLoader from '@Components/common/skeletons/SkeletonLoader';
import EmailTemplatesDrawer from '@Components/followUpTemplates/emailTemplates/EmailTemplatesDrawer';
import { EmptyBlock } from '@Components/common/empty-blocks';
import AppTooltip from '@Components/common/AppTooltip';
import ConfirmationModal from '@Components/common/ConfirmationModal';
import FeatureBadge from '@Components/common/FeatureBadge';
import TemplatesNav from '../TemplatesNav';

const EmailTemplates = () => {
	const [ selected, setSelected ] = useState( [] );
	const [ searchText, setSearchText ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ open, setOpen ] = useState( false );
	const [ template, setTemplate ] = useState( null );
	const [ templates, setTemplates ] = useState( [] );
	const [ deleteModal, setDeleteModal ] = useState( {
		isOpen: false,
		type: null,
		id: null,
	} );
	const [ isDeleting, setIsDeleting ] = useState( false );
	const [ dummyMetrics, setDummyMetrics ] = useState( {} );
	const { shouldBlockProFeatures } = useProAccess();
	const isFeatureBlocked = shouldBlockProFeatures();

	useEffect( () => {
		fetchEmailTemplates();
	}, [] );

	useEffect( () => {
		if ( ! isFeatureBlocked ) {
			return;
		}

		setDummyMetrics( ( prev ) => {
			const next = { ...prev };
			let changed = false;

			templates.forEach( ( t ) => {
				const id = String( t.id );
				if ( ! next[ id ] ) {
					next[ id ] = {
						sent: Math.floor( Math.random() * 100 ),
						open: Math.floor( Math.random() * 100 ),
						click: Math.floor( Math.random() * 100 ),
						conversion: Math.floor( Math.random() * 100 ),
						unsub: Math.floor( Math.random() * 100 ),
					};
					changed = true;
				}
			} );

			return changed ? next : prev;
		} );
	}, [ isFeatureBlocked, templates ] );

	const fetchEmailTemplates = async () => {
		try {
			await doApiFetch(
				'/wcar/api/admin/followup-emails',
				{},
				'POST',
				( response ) => {
					setTemplates( response );
					setIsLoading( false );
				},
				( error ) => {
					console.error( 'Error fetching email templates:', error );
					setIsLoading( false );
				}
			);
		} catch ( error ) {
			console.error( 'Error in fetchEmailTemplates:', error );
			setIsLoading( false );
		}
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

	const handleSearch = ( value ) => {
		setSearchText( value );
	};

	const filteredData = templates.filter( ( item ) =>
		item.template_name.toLowerCase().includes( searchText.toLowerCase() )
	);

	// Handle delete trigger
	const handleDeleteTrigger = () => {
		if ( ! selected.length ) {
			return;
		}
		setDeleteModal( { isOpen: true, type: 'bulk', id: null } );
	};

	// Handle bulk delete
	const handleBulkDelete = () => {
		setIsDeleting( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url || window.ajaxurl;
		const nonce = cart_abandonment_admin?.delete_email_template_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_delete_email_template' );
		selected.forEach( ( id ) => formData.append( 'ids[]', id ) );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					// Ensure both sides are numbers for comparison
					const deletedIds = ( response.data?.ids || selected ).map(
						Number
					);
					setTemplates( ( prev ) =>
						prev.filter(
							( tpl ) => ! deletedIds.includes( Number( tpl.id ) )
						)
					);
					toast.success(
						__(
							'Template(s) deleted',
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
				setSelected( [] );
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			( error ) => {
				toast.error(
					__( 'Delete failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error?.message || '',
					}
				);
				setSelected( [] );
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			true
		);
	};

	// Handle single delete
	const handleSingleDelete = () => {
		setIsDeleting( true );
		const ajaxUrl = cart_abandonment_admin?.ajax_url || window.ajaxurl;
		const nonce = cart_abandonment_admin?.delete_email_template_nonce;

		const formData = new window.FormData();
		formData.append( 'action', 'wcar_delete_email_template' );
		formData.append( 'ids[]', deleteModal.id );
		formData.append( 'security', nonce );

		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					setTemplates( ( prev ) =>
						prev.filter(
							( tpl ) =>
								Number( tpl.id ) !== Number( deleteModal.id )
						)
					);
					toast.success(
						__(
							'Template deleted',
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
						description: error?.message || '',
					}
				);
				setIsDeleting( false );
				setDeleteModal( { isOpen: false, type: null, id: null } );
			},
			true
		);
	};

	const handleConfirmDelete = () => {
		if ( deleteModal.type === 'bulk' ) {
			handleBulkDelete();
		} else {
			handleSingleDelete();
		}
	};

	const handleCancelSelect = () => {
		setSelected( [] );
	};

	const handleEdit = ( record ) => {
		setTemplate( record );
		setOpen( true );
	};

	const handleCreateNew = () => {
		setTemplate( null );
		setOpen( true );
	};

	const handleToggleStatus = ( id, val ) => {
		/* eslint-disable */
		setTemplates( ( prev ) =>
			prev.map( ( p ) =>
				p.id === id
					? {
							...p,
							is_activated: val === true ? 'on' : '',
					  }
					: p
			)
		);
		/* eslint-enable */
		const formData = new window.FormData();
		formData.append( 'action', 'wcar_update_email_template_status' );
		formData.append(
			'security',
			cart_abandonment_admin?.update_email_template_status_nonce
		);
		formData.append( 'id', id );
		// Send 1 for enabled, 0 for disabled
		formData.append( 'state', val === true ? 1 : 0 );

		doApiFetch(
			cart_abandonment_admin?.ajax_url || window.ajaxurl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					setTemplates( ( prev ) =>
						prev.map(
							( tplat ) =>
								/* eslint-disable */
								tplat.id === id
									? {
											...tplat,
											is_activated:
												response.data.is_active ===
													'1' ||
												response.data.is_active === 1
													? 'on'
													: '',
									  }
									: tplat
							/* eslint-enable */
						)
					);
					toast.success( 'Success!', {
						description: __(
							'Template status updated successfully',
							'woo-cart-abandonment-recovery'
						),
					} );
				} else {
					toast.error( 'Error!', {
						description:
							response.data?.message ||
							__(
								'Failed to update status',
								'woo-cart-abandonment-recovery'
							),
					} );
				}
			},
			( error ) => {
				toast.error( 'Error!', {
					description:
						error?.message ||
						__(
							'Failed to update status',
							'woo-cart-abandonment-recovery'
						),
				} );
			},
			true // useUrl: true for regular AJAX
		);
	};

	const handleDuplicate = ( item ) => {
		const formData = new window.FormData();
		formData.append( 'action', 'wcar_clone_email_template' );
		formData.append(
			'security',
			cart_abandonment_admin?.clone_email_template_nonce
		);
		formData.append( 'id', item.id );

		doApiFetch(
			cart_abandonment_admin?.ajax_url || window.ajaxurl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					setTemplates( ( prev ) => [
						...prev,
						response.data.template,
					] );
					toast.success(
						__(
							'Template cloned successfully',
							'woo-cart-abandonment-recovery'
						)
					);
				} else {
					toast.error(
						__( 'Clone failed', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error(
					__( 'Clone failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error?.message || '',
					}
				);
			},
			true
		);
	};

	const handleDrawerSave = ( newOrUpdatedTemplate ) => {
		// Add or update the template in state
		const isCreate = ! templates.some(
			( t ) => t.id === newOrUpdatedTemplate.id
		);

		setTemplates( ( prev ) => {
			if ( isCreate ) {
				return [ ...prev, newOrUpdatedTemplate ];
			}
			return prev.map( ( t ) =>
				t.id === newOrUpdatedTemplate.id ? newOrUpdatedTemplate : t
			);
		} );

		if ( isCreate ) {
			setOpen( false );
			setTemplate( null );
		}
	};

	const handleRestoreDefaultEmails = () => {
		const formData = new window.FormData();
		formData.append( 'action', 'wcar_restore_email_templates' );
		formData.append(
			'security',
			cart_abandonment_admin?.restore_email_templates_nonce
		);

		doApiFetch(
			cart_abandonment_admin?.ajax_url || window.ajaxurl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					setTemplates( response.data.templates );
					toast.success(
						__(
							'Default templates restored',
							'woo-cart-abandonment-recovery'
						)
					);
				} else {
					toast.error(
						__( 'Restore failed', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
			},
			( error ) => {
				toast.error(
					__( 'Restore failed', 'woo-cart-abandonment-recovery' ),
					{
						description: error?.message || '',
					}
				);
			},
			true
		);
	};

	const truncateText = ( text, maxLength = 60 ) => {
		return text.length > maxLength
			? text.slice( 0, maxLength ) + '...'
			: text;
	};

	const formatDuration = ( value, unit ) => {
		const units = {
			MINUTE: 'Minutes',
			HOUR: 'Hours',
			DAY: 'Days',
		};
		unit = units[ unit ];
		const formattedUnit =
			parseInt( value ) === 1
				? unit.slice( 0, -1 ) // remove 's' for singular: "days" -> "day"
				: unit;

		return `${ value } ${ formattedUnit }`;
	};

	// Introduced a variable if in future or any user wants the subject column.
	const shouldHideTableColumn = true;

	return (
		<>
			<div className="flex flex-col md:flex-row gap-4 md:gap-0 justify-between relative">
				<div className="flex flex-row flex-wrap gap-4 items-center">
					<TemplatesNav currentTab="email" />
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
								{ selected.length }{ ' ' }
								{ __(
									'Selected',
									'woo-cart-abandonment-recovery'
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
								{ __(
									'Delete',
									'woo-cart-abandonment-recovery'
								) }
							</Button>
						</div>
					) }
				</div>
				<div className="flex flex-col md:flex-row gap-4">
					<Input
						placeholder={ __(
							'Search…',
							'woo-cart-abandonment-recovery'
						) }
						prefix={
							<MagnifyingGlassIcon className="h-6 w-6 text-gray-500" />
						}
						size="md"
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
					<Button
						iconPosition="left"
						size="sm"
						tag="button"
						type="button"
						variant="outline"
						disabled={ isLoading }
						onClick={ handleRestoreDefaultEmails }
					>
						{ __(
							'Restore Default Templates',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
					<Button
						className=""
						icon={ <PlusIcon aria-label="icon" role="img" /> }
						iconPosition="left"
						size="sm"
						tag="button"
						type="button"
						variant="primary"
						disabled={ isLoading }
						onClick={ handleCreateNew }
					>
						{ __(
							'Create New Template',
							'woo-cart-abandonment-recovery'
						) }
					</Button>
				</div>
			</div>
			{ isLoading ? (
				<div className="flex flex-col gap-4">
					<SkeletonLoader height="40px" />
					{ [ ...Array( 5 ) ].map( ( _, index ) => (
						<SkeletonLoader key={ index } height="50px" />
					) ) }
				</div>
			) : templates.length === 0 ? (
				<EmptyBlock
					className="my-2"
					icon={
						<ExclamationTriangleIcon className="h-12 w-12 text-yellow-500" />
					}
					title={ __(
						'No Templates available',
						'woo-cart-abandonment-recovery'
					) }
					description={ __(
						'Follow Up Email Templates data will appear here after templates are created',
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
								'Template Name',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell
							className={ shouldHideTableColumn ? 'hidden' : '' }
						>
							{ __(
								'Email Subject',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							{ __(
								'Trigger After',
								'woo-cart-abandonment-recovery'
							) }
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Sent',
									'woo-cart-abandonment-recovery'
								) }
								{ isFeatureBlocked && (
									<FeatureBadge
										feature={ __(
											'PRO',
											'woo-cart-abandonment-recovery'
										) }
									/>
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Open Rate',
									'woo-cart-abandonment-recovery'
								) }
								{ isFeatureBlocked && (
									<FeatureBadge
										feature={ __(
											'PRO',
											'woo-cart-abandonment-recovery'
										) }
									/>
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Click Rate',
									'woo-cart-abandonment-recovery'
								) }
								{ isFeatureBlocked && (
									<FeatureBadge
										feature={ __(
											'PRO',
											'woo-cart-abandonment-recovery'
										) }
									/>
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Conversion Rate',
									'woo-cart-abandonment-recovery'
								) }
								{ isFeatureBlocked && (
									<FeatureBadge
										feature={ __(
											'PRO',
											'woo-cart-abandonment-recovery'
										) }
									/>
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							<div className="flex items-center justify-center gap-1.5">
								{ __(
									'Unsubscribed',
									'woo-cart-abandonment-recovery'
								) }
								{ isFeatureBlocked && (
									<FeatureBadge
										feature={ __(
											'PRO',
											'woo-cart-abandonment-recovery'
										) }
									/>
								) }
							</div>
						</Table.HeadCell>
						<Table.HeadCell>
							{ __( 'Status', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
						<Table.HeadCell className="text-right">
							{ __( 'Actions', 'woo-cart-abandonment-recovery' ) }
						</Table.HeadCell>
					</Table.Head>
					<Table.Body>
						{ filteredData.length > 0 ? (
							filteredData.map( ( item ) => (
								<Table.Row
									key={ item.id }
									value={ item }
									selected={ selected.includes( item.id ) }
									onChangeSelection={ handleCheckboxChange }
								>
									<Table.Cell>
										<AppTooltip
											content={ item.template_name }
											arrow
											placement="top"
										>
											<span
												className="cursor-pointer hover:text-flamingo-400 focus-visible:text-flamingo-400"
												onClick={ () =>
													handleEdit( item )
												}
												onKeyDown={ ( e ) => {
													if (
														e.key === 'Enter' ||
														e.key === ' '
													) {
														handleEdit( item );
													}
												} }
												role="button"
												tabIndex={ 0 }
											>
												{ truncateText(
													item.template_name,
													30
												) }
											</span>
										</AppTooltip>
									</Table.Cell>
									<Table.Cell
										className={
											shouldHideTableColumn
												? 'hidden'
												: ''
										}
									>
										<AppTooltip
											content={ item.email_subject }
											arrow
											placement="top"
										>
											<span>
												{ truncateText(
													item.email_subject
												) }
											</span>
										</AppTooltip>
									</Table.Cell>
									<Table.Cell>
										{ formatDuration(
											item.email_frequency,
											item.email_frequency_unit
										) }
									</Table.Cell>

									{ /* eslint-disable indent, no-mixed-spaces-and-tabs, no-trailing-spaces */ }
									<Table.Cell
										className={ `${
											isFeatureBlocked
												? 'blur-sm select-none'
												: ''
										} text-center` }
									>
										{ isFeatureBlocked
											? dummyMetrics[ item.id ]?.sent
											: item?.sent || '-' }
									</Table.Cell>
									<Table.Cell
										className={ `${
											isFeatureBlocked
												? 'blur-sm select-none'
												: ''
										} text-center` }
									>
										{ isFeatureBlocked
											? `${
													dummyMetrics[ item.id ]
														?.open
											  }%`
											: item?.open_rate
											? `${ item.open_rate }%`
											: '-' }
									</Table.Cell>
									<Table.Cell
										className={ `${
											isFeatureBlocked
												? 'blur-sm select-none'
												: ''
										} text-center` }
									>
										{ isFeatureBlocked
											? `${
													dummyMetrics[ item.id ]
														?.click
											  }%`
											: item?.click_rate
											? `${ item.click_rate }%`
											: '-' }
									</Table.Cell>
									<Table.Cell
										className={ `${
											isFeatureBlocked
												? 'blur-sm select-none'
												: ''
										} text-center` }
									>
										{ isFeatureBlocked
											? `${
													dummyMetrics[ item.id ]
														?.conversion
											  }%`
											: item?.conversion_rate
											? `${ item.conversion_rate }%`
											: '-' }
									</Table.Cell>
									<Table.Cell
										className={ `${
											isFeatureBlocked
												? 'blur-sm select-none'
												: ''
										} text-center` }
									>
										{ isFeatureBlocked
											? `${
													dummyMetrics[ item.id ]
														?.unsub
											  }%`
											: item?.unsubscribe_rate
											? `${ item.unsubscribe_rate }%`
											: '-' }
									</Table.Cell>
									{ /* eslint-enable indent, no-mixed-spaces-and-tabs, no-trailing-spaces */ }
									<Table.Cell>
										<Switch
											aria-label={ `${
												item.template_name
											} - ${
												'on' === item?.is_activated
													? 'Enabled'
													: 'Disabled'
											}` }
											aria-checked={
												'on' === item?.is_activated
											}
											value={
												'on' === item?.is_activated
											}
											name={
												'wcf_activate_email_template'
											}
											onChange={ ( val ) =>
												handleToggleStatus(
													item.id,
													val
												)
											}
											size="md"
											className="border-none moderncart-toggle-field"
											role="switch"
										/>
									</Table.Cell>
									<Table.Cell>
										<div className="flex items-center justify-end gap-2">
											<AppTooltip
												content={ __(
													'Edit',
													'woo-cart-abandonment-recovery'
												) }
												arrow
												placement="top"
											>
												<Button
													variant="ghost"
													icon={
														<PencilIcon className="h-6 w-6" />
													}
													size="xs"
													className="text-gray-500 hover:text-flamingo-400"
													aria-label={ __(
														'Edit',
														'woo-cart-abandonment-recovery'
													) }
													onClick={ () =>
														handleEdit( item )
													}
												/>
											</AppTooltip>
											<AppTooltip
												content={ __(
													'Duplicate',
													'woo-cart-abandonment-recovery'
												) }
												arrow
												placement="top"
											>
												<Button
													variant="ghost"
													icon={
														<DocumentDuplicateIcon className="h-6 w-6" />
													}
													size="xs"
													className="text-gray-500 hover:text-flamingo-400"
													aria-label={ __(
														'Duplicate',
														'woo-cart-abandonment-recovery'
													) }
													onClick={ () =>
														handleDuplicate( item )
													}
												/>
											</AppTooltip>
											<AppTooltip
												content={ __(
													'Delete',
													'woo-cart-abandonment-recovery'
												) }
												arrow
												placement="top"
											>
												<Button
													variant="ghost"
													icon={
														<TrashIcon className="h-6 w-6" />
													}
													size="xs"
													className="text-gray-500 hover:text-red-600"
													aria-label={ __(
														'Delete',
														'woo-cart-abandonment-recovery'
													) }
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
							) )
						) : (
							<tr>
								<td
									colSpan="9"
									className="p-4 w-full text-center"
								>
									{ __(
										'No matching data available',
										'woo-cart-abandonment-recovery'
									) }
								</td>
							</tr>
						) }
					</Table.Body>
				</Table>
			) }

			<EmailTemplatesDrawer
				open={ open }
				setOpen={ setOpen }
				template={ template }
				onSave={ handleDrawerSave }
			/>
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
					/* eslint-disable no-mixed-spaces-and-tabs, indent, @wordpress/i18n-no-variables, @wordpress/i18n-no-collapsible-whitespace, @wordpress/i18n-translator-comments */
					deleteModal.type === 'bulk'
						? __(
								'Delete Selected Templates',
								'woo-cart-abandonment-recovery'
						  )
						: __(
								'Delete Template',
								'woo-cart-abandonment-recovery'
						  )
				}
				message={
					deleteModal.type === 'bulk'
						? sprintf(
								/* translators: %s: number of selected templates */
								__(
									'Are you sure you want to delete %s selected template(s)? This action cannot be undone.',
									'woo-cart-abandonment-recovery'
								),
								selected.length
						  )
						: __(
								'Are you sure you want to delete this template? This action cannot be undone.',
								'woo-cart-abandonment-recovery'
						  )
				}
				isLoading={ isDeleting }
			/>
		</>
	);
};

export default EmailTemplates;
