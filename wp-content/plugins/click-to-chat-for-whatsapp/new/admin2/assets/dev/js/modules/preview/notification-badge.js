/**
 * Notification badge for the live preview — mirrors the frontend block in
 * new/inc/chat/class-ht-ctc-chat.php (ht_ctc_notification > ht_ctc_badge). The
 * frontend renders it hidden and reveals it via JS; the preview shows it at the
 * default offset and toggles visibility with a container CSS class.
 */
import { escapeAttr, escapeHTML } from '../core/Utils.js';

/**
 * Badge markup, or '' when the badge is disabled.
 *
 * @param {{ get: (group: string, key: string) => *}} values FormValues instance
 * @returns {string}
 */
export const notificationBadgeHtml = ( values ) => {
	const enabled = values.get( 'ht_ctc_othersettings', 'notification_badge' ) || '';
	if ( enabled === '' ) { return ''; }

	const count = values.get( 'ht_ctc_othersettings', 'notification_count' ) || '1';
	const bgColor = values.get( 'ht_ctc_othersettings', 'notification_bg_color' ) || '#ff4c4c';
	const textColor = values.get( 'ht_ctc_othersettings', 'notification_text_color' ) || '#ffffff';
	const borderColor = values.get( 'ht_ctc_othersettings', 'notification_border_color' ) || '';
	const border = ( borderColor !== '' ) ? `border:2px solid ${borderColor};` : '';

	const badgeCss = [
		'position: absolute;',
		'top: -11px;',
		'right: -11px;',
		'font-size:12px;',
		'font-weight:600;',
		'height:22px;',
		'width:22px;',
		'box-sizing:border-box;',
		'border-radius:50%;',
		border,
		`background:${bgColor};`,
		`color:${textColor};`,
		'display:flex;',
		'justify-content:center;',
		'align-items:center;',
	].join( ' ' );

	const notificationStyle = 'padding:0px; margin:0px; position:relative; float:right; ' +
		'z-index:9999999;';

	return `<span class="ht_ctc_notification" style="${notificationStyle}">
		<span class="ht_ctc_badge" style="${escapeAttr( badgeCss )}">
			${escapeHTML( String( count ) )}
		</span>
	</span>`;
};

/**
 * Override the badge top/right from the style chip's data-nb_top / data-nb_right,
 * mirroring display_notifications() in new/inc/assets/js/dev/app.dev.js. Styles
 * without a .ctc_nb chip keep the default -11px offset baked into the markup.
 *
 * @param {HTMLElement} stage The rendered widget stage.
 */
export const applyBadgeOffset = ( stage ) => {
	const chip = stage.querySelector( '.ctc_nb' );
	const badge = stage.querySelector( '.ht_ctc_badge' );
	if ( ! chip || ! badge ) { return; }

	const top = chip.getAttribute( 'data-nb_top' );
	const right = chip.getAttribute( 'data-nb_right' );
	if ( top !== null ) { badge.style.top = top; }
	if ( right !== null ) { badge.style.right = right; }
};
