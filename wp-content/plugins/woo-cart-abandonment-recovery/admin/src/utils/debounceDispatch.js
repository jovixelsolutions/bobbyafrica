import { useRef, useCallback } from 'react';
import debounce from 'lodash.debounce';
import { ActionTypes, doApiFetch } from '@Store';

/**
 * Custom hook to dispatch debounced setting updates.
 *
 * @param {Function} dispatch   React context dispatch method.
 * @param {string}   optionName Option key being updated.
 * @param {Function} onSuccess  Success callback function.
 * @param {number}   delay      Debounce delay in ms.
 * @param {boolean}  showToast  Whether to show toast notifications.
 * @return {Function} callback
 */
const useDebounceDispatch = (
	dispatch,
	optionName,
	onSuccess,
	delay = 400,
	showToast = false
) => {
	const debounced = useRef(
		debounce( ( value ) => {
			// Update the state with the new value
			dispatch( {
				type: ActionTypes.UPDATE_SETTINGS_DATA,
				payload: {
					option: optionName,
					value,
				},
			} );
			const formData = new window.FormData();

			// Set the full option name in the form data
			formData.append( 'option_name', optionName );
			if ( Array.isArray( value ) ) {
				value.forEach( ( v ) => formData.append( 'value[]', v ) );
			} else {
				formData.append( 'value', value );
			}
			formData.append( 'action', 'wcar_save_setting' );
			formData.append(
				'security',
				cart_abandonment_admin?.save_setting_nonce
			);

			const ajaxUrl = cart_abandonment_admin?.ajax_url || window.ajaxurl;

			doApiFetch(
				ajaxUrl,
				formData,
				'POST',
				() => {
					if ( onSuccess ) {
						onSuccess();
					}
				},
				undefined,
				true,
				showToast
			);
		}, delay )
	);

	return useCallback(
		( value ) => debounced.current( value ),
		[ debounced ]
	);
};

export default useDebounceDispatch;
