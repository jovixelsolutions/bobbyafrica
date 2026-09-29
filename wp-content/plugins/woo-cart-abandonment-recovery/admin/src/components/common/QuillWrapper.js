import { useEffect } from 'react';
import ReactQuill from 'react-quill';

const QuillWrapper = ( props ) => {
	useEffect( () => {
		// Temporarily suppress console warnings for DOMNodeInserted deprecation
		const originalWarn = console.warn;
		const suppressedMessages = [ 'DOMNodeInserted', 'mutation event' ];

		console.warn = function ( ...args ) {
			const message = args.join( ' ' );
			const shouldSuppress = suppressedMessages.some( ( msg ) =>
				message.includes( msg )
			);

			if ( ! shouldSuppress ) {
				originalWarn.apply( console, args );
			}
		};

		return () => {
			// Restore original console.warn when component unmounts
			console.warn = originalWarn;
		};
	}, [] );

	return <ReactQuill { ...props } />;
};

export default QuillWrapper;
