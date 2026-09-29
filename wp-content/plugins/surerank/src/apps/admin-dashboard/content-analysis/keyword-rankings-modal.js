import {
	Dialog,
	Table,
	Text,
	Skeleton,
	toast,
	Container,
	Pagination,
} from '@bsf/force-ui';
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { SortableColumn } from '@GlobalComponents/sortable-column';
import { AlertTriangle } from 'lucide-react';
import { formatNumber } from '@/functions/utils';
import usePagination from '@Global/hooks/use-pagination';
import { getChangeIcon } from '../utils/get-change-icon';

/**
 * Keyword Rankings Modal Component
 *
 * Displays keyword ranking data for a specific URL in a modal dialog
 *
 * @param {Object}   props           - Component props
 * @param {boolean}  props.open      - Whether modal is open
 * @param {Function} props.setOpen   - Function to set modal open state
 * @param {string}   props.url       - The URL to fetch keyword rankings for
 * @param {string}   props.startDate - Start date for data range
 * @param {string}   props.endDate   - End date for data range
 * @return {JSX.Element} Keyword rankings modal component
 */
const KeywordRankingsModal = ( { open, setOpen, url, startDate, endDate } ) => {
	const [ loading, setLoading ] = useState( true );
	const [ keywordData, setKeywordData ] = useState( [] );
	const [ sortConfig, setSortConfig ] = useState( {
		key: 'impressions',
		direction: 'desc',
	} );
	const [ error, setError ] = useState( null );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const itemsPerPage = 10;

	useEffect( () => {
		if ( ! open || ! url ) {
			return;
		}

		const fetchKeywordRankings = async () => {
			setLoading( true );
			setError( null );

			try {
				const response = await apiFetch( {
					path: addQueryArgs(
						'/surerank/v1/google-search-console/keyword-rankings',
						{
							url,
							startDate,
							endDate,
						}
					),
					method: 'GET',
				} );

				if ( ! response.success ) {
					throw new Error(
						response.message ??
							__( 'Failed to fetch keyword rankings', 'surerank' )
					);
				}

				setKeywordData( response.data || [] );
			} catch ( err ) {
				const errorMessage =
					err.message ||
					__( 'Failed to fetch keyword rankings', 'surerank' );
				setError( errorMessage );
				toast.error( errorMessage );
			} finally {
				setLoading( false );
			}
		};

		fetchKeywordRankings();
	}, [ open, url, startDate, endDate ] );

	const handleSort = ( key ) => {
		let direction = 'asc';
		if ( sortConfig.key === key && sortConfig.direction === 'asc' ) {
			direction = 'desc';
		}
		setSortConfig( { key, direction } );
	};

	const sortedData = [ ...keywordData ].sort( ( a, b ) => {
		const { key, direction } = sortConfig;
		const multiplier = direction === 'asc' ? 1 : -1;

		if ( key === 'position' || key === 'impressions' || key === 'clicks' ) {
			return ( a[ key ] - b[ key ] ) * multiplier;
		}

		if ( key === 'query' ) {
			return a[ key ].localeCompare( b[ key ] ) * multiplier;
		}

		return 0;
	} );

	const totalPages = Math.ceil( sortedData.length / itemsPerPage );
	const pagination = usePagination(
		{
			totalPages,
			currentPage,
			onPageChange: setCurrentPage,
		},
		[ sortConfig ]
	);

	const startIndex = ( currentPage - 1 ) * itemsPerPage;
	const endIndex = startIndex + itemsPerPage;
	const paginatedData = sortedData.slice( startIndex, endIndex );

	const handleSetOpen = ( value ) => {
		setOpen( value );
		if ( ! value ) {
			setKeywordData( [] );
			setError( null );
			setLoading( true );
		}
	};

	return (
		<Dialog
			open={ open ?? false }
			setOpen={ handleSetOpen }
			exitOnEsc
			scrollLock
		>
			<Dialog.Backdrop />
			<Dialog.Panel className="w-[700px] max-w-[700px]">
				<Dialog.Header>
					<div className="flex items-center justify-between">
						<div>
							<Dialog.Title>
								{ __( 'Keyword Rankings', 'surerank' ) }
							</Dialog.Title>
							<Dialog.Description>
								<div className="flex flex-col gap-1">
									<Text size={ 14 } color="secondary">
										{ __(
											'See how this page is ranking in terms of the keywords',
											'surerank'
										) }
									</Text>
								</div>
							</Dialog.Description>
						</div>
						<Dialog.CloseButton />
					</div>
				</Dialog.Header>
				<Dialog.Body className="pb-5">
					{ loading && (
						<Table>
							<Table.Head>
								<Table.HeadCell className="w-[40%]">
									{ __( 'Keywords', 'surerank' ) }
								</Table.HeadCell>
								<Table.HeadCell className="w-[20%]">
									{ __( 'Avg. Position', 'surerank' ) }
								</Table.HeadCell>
								<Table.HeadCell className="w-[20%]">
									{ __( 'Impressions', 'surerank' ) }
								</Table.HeadCell>
								<Table.HeadCell className="w-[20%]">
									{ __( 'Clicks', 'surerank' ) }
								</Table.HeadCell>
							</Table.Head>
							<Table.Body>
								{ Array.from( { length: 10 } ).map(
									( _, index ) => (
										<Table.Row key={ index }>
											<Table.Cell>
												<Skeleton className="h-4 w-full" />
											</Table.Cell>
											<Table.Cell>
												<Skeleton className="h-4 w-16" />
											</Table.Cell>
											<Table.Cell>
												<Skeleton className="h-4 w-16" />
											</Table.Cell>
											<Table.Cell>
												<Skeleton className="h-4 w-16" />
											</Table.Cell>
										</Table.Row>
									)
								) }
							</Table.Body>
						</Table>
					) }

					{ ! loading && error && (
						<Container
							direction="column"
							align="center"
							className="py-12"
						>
							<AlertTriangle className="size-12 text-text-tertiary mb-4" />
							<Text size={ 14 } color="secondary">
								{ error }
							</Text>
						</Container>
					) }

					{ ! loading && ! error && keywordData.length === 0 && (
						<Container
							direction="column"
							align="center"
							className="py-12"
						>
							<Text size={ 14 } color="secondary">
								{ __(
									'No keyword rankings found for this URL.',
									'surerank'
								) }
							</Text>
							<Text size={ 12 } color="tertiary" className="mt-2">
								{ __(
									'This page may not have any search queries in the selected date range.',
									'surerank'
								) }
							</Text>
						</Container>
					) }

					{ ! loading && ! error && keywordData.length > 0 && (
						<Table>
							<Table.Head>
								<SortableColumn
									className="w-[40%]"
									sortKey="query"
									onSort={ handleSort }
									currentSort={ sortConfig }
								>
									{ __( 'Keywords', 'surerank' ) }
								</SortableColumn>
								<SortableColumn
									className="w-[20%]"
									sortKey="position"
									onSort={ handleSort }
									currentSort={ sortConfig }
								>
									{ __( 'Avg. Position', 'surerank' ) }
								</SortableColumn>
								<SortableColumn
									className="w-[20%]"
									sortKey="impressions"
									onSort={ handleSort }
									currentSort={ sortConfig }
								>
									{ __( 'Impressions', 'surerank' ) }
								</SortableColumn>
								<SortableColumn
									className="w-[20%]"
									sortKey="clicks"
									onSort={ handleSort }
									currentSort={ sortConfig }
								>
									{ __( 'Clicks', 'surerank' ) }
								</SortableColumn>
							</Table.Head>
							<Table.Body>
								{ paginatedData.map( ( item, index ) => (
									<Table.Row key={ index }>
										<Table.Cell className="w-[40%]">
											<Text size={ 14 }>
												{ item.query }
											</Text>
										</Table.Cell>
										<Table.Cell className="w-[20%]">
											<div className="flex items-center gap-1">
												<Text size={ 14 }>
													{ item.position.toFixed(
														1
													) }
												</Text>
												{ getChangeIcon(
													item,
													'position'
												) }
											</div>
										</Table.Cell>
										<Table.Cell className="w-[20%]">
											<div className="flex items-center gap-1">
												<Text size={ 14 }>
													{ formatNumber(
														item.impressions
													) }
												</Text>
												{ getChangeIcon(
													item,
													'impressions'
												) }
											</div>
										</Table.Cell>
										<Table.Cell className="w-[20%]">
											<div className="flex items-center gap-1">
												<Text size={ 14 }>
													{ formatNumber(
														item.clicks
													) }
												</Text>
												{ getChangeIcon(
													item,
													'clicks'
												) }
											</div>
										</Table.Cell>
									</Table.Row>
								) ) }
							</Table.Body>
							{ keywordData.length > itemsPerPage && (
								<Table.Footer>
									<KeywordRankingsPagination
										pages={ pagination.pages }
										currentPage={ currentPage }
										totalPages={ totalPages }
										isPreviousDisabled={
											pagination.isPreviousDisabled
										}
										isNextDisabled={
											pagination.isNextDisabled
										}
										handlePageChange={
											pagination.handlePageChange
										}
										goToPreviousPage={
											pagination.goToPreviousPage
										}
										goToNextPage={ pagination.goToNextPage }
									/>
								</Table.Footer>
							) }
						</Table>
					) }
				</Dialog.Body>
			</Dialog.Panel>
		</Dialog>
	);
};

const KeywordRankingsPagination = ( {
	pages,
	currentPage,
	totalPages,
	isPreviousDisabled,
	isNextDisabled,
	handlePageChange,
	goToPreviousPage,
	goToNextPage,
} ) => {
	return (
		<Container align="center" justify="between">
			<div aria-label="Pagination status" aria-current="page">
				{ sprintf(
					// translators: %1$s is the current page number, %2$s is the total number of pages
					__( 'Page %1$s out of %2$s', 'surerank' ),
					currentPage,
					totalPages
				) }
			</div>
			<div>
				<Pagination size="sm">
					<Pagination.Content>
						<Pagination.Previous
							className="disabled:opacity-50 disabled:cursor-not-allowed"
							disabled={ isPreviousDisabled }
							tag="button"
							onClick={ goToPreviousPage }
							aria-label={ __( 'Previous page', 'surerank' ) }
						/>
						{ pages.map( ( page, index ) => {
							if ( page === '...' ) {
								return (
									<Pagination.Ellipsis
										key={ `ellipsis-${ index }` }
									/>
								);
							}
							return (
								<Pagination.Item
									key={ page }
									isActive={ page === currentPage }
									onClick={ () => handlePageChange( page ) }
									aria-label={ sprintf(
										// translators: %s is the page number
										__( 'Page %s', 'surerank' ),
										page
									) }
									tag="button"
								>
									{ page }
								</Pagination.Item>
							);
						} ) }
						<Pagination.Next
							className="disabled:opacity-50 disabled:cursor-not-allowed"
							disabled={ isNextDisabled }
							tag="button"
							onClick={ goToNextPage }
							aria-label={ __( 'Next page', 'surerank' ) }
						/>
					</Pagination.Content>
				</Pagination>
			</div>
		</Container>
	);
};

export default KeywordRankingsModal;
