import React from 'react';
import { __ } from '@wordpress/i18n';
import useWhatsNewRSS from 'whats-new-rss';

import AppTooltip from '@Components/common/AppTooltip';

function WhatsNew() {
	const rssUrlData = cart_abandonment_admin?.whats_new_rss_feed;
	const preparedRssUrls = rssUrlData?.url || rssUrlData?.[ 0 ]?.url; // If the Pro version is not available then only display the free version's RSS without the tabs.

	useWhatsNewRSS( {
		uniqueKey: 'cart-abandonment-recovery',
		rssFeedURL: preparedRssUrls,
		selector: '#wcar-whats-new-content',
		triggerButton: {
			icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 stroke-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-offset-2 cursor-pointer" role="button" tabIndex="0"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" /></svg>',
		},
		flyout: {
			title: __( "What's New?", 'woo-cart-abandonment-recovery' ),
			/**
			 * @param {Date} date
			 */
			formatDate: ( date ) => {
				const dayOfWeek = date.toLocaleDateString( 'en-US', {
					weekday: 'long',
				} );
				const month = date.toLocaleDateString( 'en-US', {
					month: 'long',
				} );
				const day = date.getDate();
				const year = date.getFullYear();

				// Format the date string
				const formattedDate = `${ dayOfWeek } ${ month } ${ day }, ${ year }`;

				return formattedDate;
			},
		},
	} );

	return (
		<AppTooltip
			content={ __( "What's New?", 'woo-cart-abandonment-recovery' ) }
			position="bottom"
			className="z-50"
		>
			<div id="wcar-whats-new-content"></div>
		</AppTooltip>
	);
}

export default WhatsNew;
