import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { XMarkIcon } from '@heroicons/react/24/outline';

function HeaderSaleNotice() {
	const [ isVisible, setIsVisible ] = useState( true ); // State to control visibility
	const [ timeLeft, setTimeLeft ] = useState( {
		days: 0,
		hours: 0,
		minutes: 0,
		seconds: 0,
	} );

	// Set custom sale start and end dates
	// You can change these to any specific dates/times as needed
	const SALE_START_DATE = '2025-11-17T00:00:00';
	const SALE_END_DATE = '2025-12-04T23:59:59';

	// Get countdown end time from localStorage or set a new one based on sale end date
	const getEndTime = () => {
		const storedEndTime = localStorage.getItem( 'wcarSaleEndTime' );
		if ( storedEndTime ) {
			return parseInt( storedEndTime, 10 );
		}
		const newEndTime = new Date( SALE_END_DATE ).getTime();
		localStorage.setItem( 'wcarSaleEndTime', newEndTime.toString() );
		return newEndTime;
	};

	// Check if the sale is currently active
	const isSaleActive = () => {
		const now = new Date().getTime();
		const saleStart = new Date( SALE_START_DATE ).getTime();
		const saleEnd = new Date( SALE_END_DATE ).getTime();
		return now >= saleStart && now < saleEnd;
	};

	const formatNumber = ( num ) => String( num ).padStart( 2, '0' );

	const upgradeToProUrl =
		'https://cartflows.com/cart-abandonment/?utm_source=wcar-dashboard&utm_medium=free-wcar&utm_campaign=bfcm';

	// Function to handle dismissal of the notice
	const handleDismiss = () => {
		localStorage.removeItem( 'wcarSaleEndTime' );
		localStorage.setItem( 'wcarSaleBannerDisable', true );
		setIsVisible( false );
	};

	// Countdown timer effect
	useEffect( () => {
		// Don't start the timer if the sale isn't active
		if ( ! isSaleActive() ) {
			// If sale hasn't started, show full countdown
			// If sale has ended, show 00:00:00:00
			const now = new Date().getTime();
			const saleStart = new Date( SALE_START_DATE ).getTime();

			if ( now < saleStart ) {
				// Sale hasn't started yet, show time until start
				const distance = saleStart - now;
				const days = Math.floor( distance / ( 1000 * 60 * 60 * 24 ) );
				const hours = Math.floor(
					( distance % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 )
				);
				const minutes = Math.floor(
					( distance % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 )
				);
				const seconds = Math.floor(
					( distance % ( 1000 * 60 ) ) / 1000
				);
				setTimeLeft( { days, hours, minutes, seconds } );
			} else {
				// Sale has ended
				localStorage.removeItem( 'wcarSaleEndTime' );
				localStorage.setItem( 'wcarSaleBannerDisable', true );
				setIsVisible( false );
			}
			return;
		}

		const countdownEndTime = getEndTime();

		const timer = setInterval( () => {
			const now = new Date().getTime();
			const distance = countdownEndTime - now;

			// If the countdown is finished, clear the interval and remove from localStorage
			if ( distance < 0 ) {
				clearInterval( timer );
				setTimeLeft( { days: 0, hours: 0, minutes: 0, seconds: 0 } );
				localStorage.removeItem( 'wcarSaleEndTime' );
				return;
			}

			// Calculate time components
			const days = Math.floor( distance / ( 1000 * 60 * 60 * 24 ) );
			const hours = Math.floor(
				( distance % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 )
			);
			const minutes = Math.floor(
				( distance % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 )
			);
			const seconds = Math.floor( ( distance % ( 1000 * 60 ) ) / 1000 );

			setTimeLeft( { days, hours, minutes, seconds } );
		}, 1000 );

		// Clean up the interval on component unmount
		return () => clearInterval( timer );
	}, [] );

	// Don't render the component if it has been dismissed or if the option is set to not show
	// Also don't render if the sale hasn't started yet
	const disableBanner = localStorage.getItem( 'wcarSaleBannerDisable' );
	if ( ! isVisible || disableBanner ) {
		return null;
	}

	return (
		<div className="bg-gradient-to-r from-primary-600 via-flamingo-400 to-primary-600 px-8 py-3 z-10 relative">
			{ /* Dismiss button in the top right corner */ }

			<button
				className="absolute top-2 right-3 text-white hover:text-gray-200 bg-transparent border-none p-0 cursor-pointer focus:outline-none"
				onClick={ handleDismiss }
				aria-label={ __( 'Dismiss', 'woo-cart-abandonment-recovery' ) }
			>
				<XMarkIcon className="h-4 w-4 stroke-[2]" />
			</button>

			<div className="flex flex-col sm:flex-row items-center justify-between max-w-7xl mx-auto flex-wrap">
				<div className="flex items-center gap-6">
					<div className="flex flex-col items-center">
						<div className="text-lg font-bold text-white leading-4">
							{ formatNumber( timeLeft.days ) }
						</div>
						<div className="text-sm text-white font-medium">
							{ __( 'Days', 'woo-cart-abandonment-recovery' ) }
						</div>
					</div>
					<div className="flex flex-col items-center">
						<div className="text-lg font-bold text-white leading-4">
							{ formatNumber( timeLeft.hours ) }
						</div>
						<div className="text-sm text-white font-medium">
							{ __( 'Hours', 'woo-cart-abandonment-recovery' ) }
						</div>
					</div>
					<div className="flex flex-col items-center">
						<div className="text-lg font-bold text-white leading-4">
							{ formatNumber( timeLeft.minutes ) }
						</div>
						<div className="text-sm text-white font-medium">
							{ __( 'Minutes', 'woo-cart-abandonment-recovery' ) }
						</div>
					</div>
					<div className="flex flex-col items-center">
						<div className="text-lg font-bold text-white leading-4">
							{ formatNumber( timeLeft.seconds ) }
						</div>
						<div className="text-sm text-white font-medium">
							{ __( 'Seconds', 'woo-cart-abandonment-recovery' ) }
						</div>
					</div>
				</div>

				<div className="flex-1 text-center px-8">
					<h2 className="text-base font-bold text-white">
						{ __(
							'Black Friday Deal is Live: Get Cart Abandonment Recovery Pro for up to 40% OFF →',
							'woo-cart-abandonment-recovery'
						) }
					</h2>
				</div>

				<a
					href={ upgradeToProUrl }
					target="_blank"
					rel="noopener noreferrer"
					className="bg-white hover:bg-[#fef1ec] text-primary-600 font-semibold no-underline px-8 py-3 rounded-lg transition-colors whitespace-nowrap inline-block"
				>
					{ __( 'Claim Offer', 'woo-cart-abandonment-recovery' ) }
				</a>
			</div>
		</div>
	);
}

export default HeaderSaleNotice;
