import { useEffect, useState } from 'react';
import { Button } from '@bsf/force-ui';
import { XMarkIcon } from '@heroicons/react/24/outline';

import { useProAccess } from './useProAccess';

const ProUpgradeHeaderBanner = () => {
	const [ isVisible, setIsVisible ] = useState( false );
	const [ isHiding, setIsHiding ] = useState( false );
	const { canAccessProFeatures, upgradeActionButton, getActionButtonText } =
		useProAccess();
	const DISMISS_KEY = 'wcarUpgradeHeaderBannerDismissedAt';
	const ONE_WEEK_MS = 7 * 24 * 60 * 60 * 1000; // 1 week in milliseconds

	useEffect( () => {
		const dismissedAt = localStorage.getItem( DISMISS_KEY );

		if ( dismissedAt ) {
			const timeSinceDismiss = Date.now() - parseInt( dismissedAt, 10 );
			if ( timeSinceDismiss < ONE_WEEK_MS ) {
				setIsVisible( false ); // Don't show if less than 1 week
				return;
			}
		}

		setIsVisible( true ); // Show banner
	}, [] );

	const handleDismiss = () => {
		setIsHiding( true );
		// Wait for the height transition to finish before unmounting
		setTimeout( () => {
			setIsVisible( false );
		}, 300 );
		localStorage.setItem( DISMISS_KEY, Date.now().toString() );
	};

	const handleBannerClick = () => {
		upgradeActionButton();
	};

	if ( ! isVisible ) {
		return null;
	}

	return (
		! canAccessProFeatures() && (
			<div
				className={ `overflow-hidden transition-all duration-300 ${
					isHiding ? 'max-h-0' : 'max-h-10'
				}` }
			>
				<div className="p-2 bg-[#FDE6D7] flex gap-2 items-center z-10 relative">
					<p className="m-0 text-[13px] flex-1 text-center">
						<span className="font-semibold">
							Recover More Sales Effortlessly!
						</span>{ ' ' }
						Unlock advanced tools that boost conversions and bring
						back more customers.{ ' ' }
						<span
							className="text-flamingo-400 font-semibold underline cursor-pointer"
							onKeyDown={ ( e ) => {
								if ( e.key === 'Enter' ) {
									handleBannerClick();
								}
							} }
							onClick={ handleBannerClick }
							role="button"
							tabIndex="0"
						>
							{ getActionButtonText() }
						</span>
					</p>
					<Button
						className=""
						icon={ <XMarkIcon class="h-6 w-6 text-text-primary" /> }
						iconPosition="left"
						size="xs"
						tag="button"
						type="button"
						variant="ghost"
						onClick={ handleDismiss }
					/>
				</div>
			</div>
		)
	);
};

export default ProUpgradeHeaderBanner;
