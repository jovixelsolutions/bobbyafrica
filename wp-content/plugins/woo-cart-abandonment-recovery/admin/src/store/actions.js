import apiFetch from '@wordpress/api-fetch';
import { toast } from '@bsf/force-ui';

/**
 * Common API fetch function to handle API requests
 *
 * @param {string}   path      - API endpoint path
 * @param {Object}   data      - Data to send in the request body
 * @param {string}   method    - HTTP method (default: 'POST')
 * @param {Function} onSuccess - Success callback function
 * @param {Function} onError   - Error callback function
 * @param {boolean}  useUrl    - Whether to use URL for the request
 * @param {boolean}  showToast - Whether to show a toast notification
 * @return {Promise} API fetch promise
 */
export async function doApiFetch(
	path,
	data = {},
	method = 'POST',
	onSuccess = () => {},
	onError = () => {},
	useUrl = false,
	showToast = false
) {
	try {
		// Prepare request options.
		const fetchOptions = {
			method,
		};

		if ( useUrl ) {
			// For regular AJAX calls
			fetchOptions.url = path;
			fetchOptions.body = data;
		} else {
			// For WordPress API calls
			fetchOptions.path = path;
			if ( data && ( method === 'POST' || method === 'PUT' ) ) {
				fetchOptions.data = data;
			}
		}

		const apiResponse = await apiFetch( fetchOptions ).then(
			( response ) => {
				if ( onSuccess ) {
					onSuccess( response );
				}
				if ( showToast ) {
					toast.success( 'Success!', {
						description: `Setting is saved successfully`,
					} );
				}
			}
		);

		return apiResponse;
	} catch ( error ) {
		console.error( 'API Error:', error );
		if ( showToast ) {
			toast.error( 'Error!', {
				description: 'Something wrong! ' + error,
			} );
		}
		if ( onError ) {
			onError( error );
		}
		throw error;
	}
}
