window.hfePromoNotice = {
	storageKey: 'hfe_promo_notice_dismissed',
	oneMonthMs: 2592000000, // 30 days in milliseconds.

	init: function () {
		if ( this.shouldShow() ) {
			this.show();
		}
	},

	shouldShow: function () {
		try {
			var dismissed = localStorage.getItem( this.storageKey );
			if ( ! dismissed ) {
				return true;
			}
			var dismissedTime = parseInt( dismissed );
			var now = Date.now();
			return now - dismissedTime >= this.oneMonthMs;
		} catch ( e ) {
			return true;
		}
	},

	show: function () {
		var notice = document.getElementById( 'hfe-promo-notice' );
		if ( notice ) {
			notice.classList.add( 'show' );
		}
	},

	dismiss: function () {
		var notice = document.getElementById( 'hfe-promo-notice' );
		if ( notice ) {
			notice.classList.add( 'hide' );
			setTimeout( function () {
				notice.remove();
			}, 400 );
		}
		try {
			localStorage.setItem( this.storageKey, Date.now().toString() );
		} catch ( e ) {
			console.log( 'Could not save dismissal state' );
		}
	},
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', function () {
		hfePromoNotice.init();
	} );
} else {
	hfePromoNotice.init();
}
