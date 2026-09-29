import { __, sprintf } from '@wordpress/i18n';
import { createCheck } from '@SeoPopup/components/page-seo-checks/analyzer/content-checks';

// Helper function to check if keyword exists in text as a whole word (case-insensitive)
const keywordExistsInText = ( text, keyword ) => {
	if ( ! text || ! keyword ) {
		return false;
	}

	const escaped = keyword.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	return new RegExp( `\\b${ escaped }\\b`, 'i' ).test( text );
};

/**
 * Normalize text by removing diacritical marks and converting to lowercase.
 * e.g., "phronèsis" becomes "phronesis".
 *
 * @param {string} text - Text to normalize.
 * @return {string} Normalized text.
 */
const normalizeText = ( text ) => {
	if ( ! text ) {
		return '';
	}
	// NFD decomposes characters into base char + combining marks,
	// then we strip all combining diacritical marks.
	return text
		.normalize( 'NFD' )
		.replace(
			/[\u0300-\u036f\u1AB0-\u1AFF\u1DC0-\u1DFF\u20D0-\u20FF\uFE20-\uFE2F]/g,
			''
		)
		.toLowerCase();
};

/**
 * Check if keyword matches text using flexible, accent-insensitive comparison.
 *
 * Handles:
 * - Diacritics/accents (phronèsis matches phronesis)
 * - Compound word spacing (house keeper matches housekeeper)
 * - Phrase variations with inserted words (presentatie geven matches presentatie te geven)
 *
 * @param {string} text    - Text to search in.
 * @param {string} keyword - Keyword to search for.
 * @return {boolean} True if keyword matches text using flexible rules.
 */
const keywordMatchesFlexible = ( text, keyword ) => {
	if ( ! text || ! keyword ) {
		return false;
	}

	const normalizedText = normalizeText( text );
	const normalizedKeyword = normalizeText( keyword );

	// Direct match after diacritics normalization (whole word only).
	const escapedNormalized = normalizedKeyword.replace(
		/[.*+?^${}()|[\]\\]/g,
		'\\$&'
	);
	if (
		new RegExp( `\\b${ escapedNormalized }\\b`, 'iu' ).test(
			normalizedText
		)
	) {
		return true;
	}

	const keywordWords = normalizedKeyword
		.trim()
		.split( /\s+/ )
		.filter( Boolean );

	if ( keywordWords.length > 1 ) {
		// Compound word matching: "house keeper" keyword matches "housekeeper" in text.
		const keywordNoSpaces = keywordWords.join( '' );
		const escapedCompound = keywordNoSpaces.replace(
			/[.*+?^${}()|[\]\\]/g,
			'\\$&'
		);
		if (
			new RegExp( `\\b${ escapedCompound }\\b`, 'iu' ).test(
				normalizedText
			)
		) {
			return true;
		}

		// Sequential word matching with up to 1 optional intervening word.
		// e.g., "presentatie geven" matches "presentatie te geven".
		const escapedWords = keywordWords.map( ( w ) =>
			w.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' )
		);
		const pattern =
			'\\b' + escapedWords.join( '\\s+(?:\\S+\\s+){0,1}' ) + '\\b';
		if ( new RegExp( pattern, 'iu' ).test( normalizedText ) ) {
			return true;
		}
	}

	return false;
};

export const checkKeywordInTitle = ( title, keyword ) => {
	if ( ! keyword ) {
		return createCheck( {
			id: 'keyword_in_title',
			title: __( 'No focus keyword set to analyze title.', 'surerank' ),
			status: 'suggestion',
			type: 'keyword',
		} );
	}

	if ( ! title ) {
		return createCheck( {
			id: 'keyword_in_title',
			title: __( 'No SEO title found to analyze.', 'surerank' ),
			status: 'warning',
			type: 'keyword',
		} );
	}

	if (
		keywordExistsInText( title, keyword ) ||
		keywordMatchesFlexible( title, keyword )
	) {
		return createCheck( {
			id: 'keyword_in_title',
			title: sprintf(
				/* translators: %s: focus keyword */
				__( 'Focus keyword "%s" found in SEO title.', 'surerank' ),
				keyword
			),
			status: 'success',
			type: 'keyword',
		} );
	}

	return createCheck( {
		id: 'keyword_in_title',
		title: sprintf(
			/* translators: %s: focus keyword */
			__( 'Focus keyword "%s" not found in SEO title.', 'surerank' ),
			keyword
		),
		status: 'warning',
		type: 'keyword',
	} );
};

export const checkKeywordInDescription = ( description, keyword ) => {
	if ( ! keyword ) {
		return createCheck( {
			id: 'keyword_in_description',
			title: __(
				'No focus keyword set to analyze meta description.',
				'surerank'
			),
			status: 'suggestion',
			type: 'keyword',
		} );
	}

	if ( ! description ) {
		return createCheck( {
			id: 'keyword_in_description',
			title: __( 'No meta description found to analyze.', 'surerank' ),
			status: 'warning',
			type: 'keyword',
		} );
	}

	if (
		keywordExistsInText( description, keyword ) ||
		keywordMatchesFlexible( description, keyword )
	) {
		return createCheck( {
			id: 'keyword_in_description',
			title: sprintf(
				/* translators: %s: focus keyword */
				__(
					'Focus keyword "%s" found in meta description.',
					'surerank'
				),
				keyword
			),
			status: 'success',
			type: 'keyword',
		} );
	}

	return createCheck( {
		id: 'keyword_in_description',
		title: sprintf(
			/* translators: %s: focus keyword */
			__(
				'Focus keyword "%s" not found in meta description.',
				'surerank'
			),
			keyword
		),
		status: 'warning',
		type: 'keyword',
	} );
};

export const checkKeywordInUrl = ( url, keyword ) => {
	if ( ! keyword ) {
		return createCheck( {
			id: 'keyword_in_url',
			title: __( 'No focus keyword set to analyze URL.', 'surerank' ),
			status: 'suggestion',
			type: 'keyword',
		} );
	}

	if ( ! url ) {
		return createCheck( {
			id: 'keyword_in_url',
			title: __( 'No URL found to analyze.', 'surerank' ),
			status: 'warning',
			type: 'keyword',
		} );
	}

	// Convert keyword to URL-friendly format (lowercase, spaces to hyphens)
	const urlFriendlyKeyword = keyword.toLowerCase().replace( /\s+/g, '-' );
	const urlLower = url.toLowerCase();

	if (
		urlLower.includes( urlFriendlyKeyword ) ||
		keywordExistsInText( url, keyword ) ||
		keywordMatchesFlexible( url, keyword ) ||
		keywordMatchesFlexible( url.replace( /-/g, ' ' ), keyword )
	) {
		return createCheck( {
			id: 'keyword_in_url',
			title: sprintf(
				/* translators: %s: focus keyword */
				__( 'Focus keyword "%s" found in URL.', 'surerank' ),
				keyword
			),
			status: 'success',
			type: 'keyword',
		} );
	}

	return createCheck( {
		id: 'keyword_in_url',
		title: sprintf(
			/* translators: %s: focus keyword */
			__( 'Focus keyword "%s" not found in URL.', 'surerank' ),
			keyword
		),
		status: 'warning',
		type: 'keyword',
	} );
};

export const checkKeywordInContent = ( content, keyword ) => {
	if ( ! keyword ) {
		return createCheck( {
			id: 'keyword_in_content',
			title: __( 'No focus keyword set to analyze content.', 'surerank' ),
			status: 'suggestion',
			type: 'keyword',
		} );
	}

	if ( ! content ) {
		return createCheck( {
			id: 'keyword_in_content',
			title: __( 'No content found to analyze.', 'surerank' ),
			status: 'warning',
			type: 'keyword',
		} );
	}

	// Clean content of HTML tags for better analysis
	const cleanContent = content
		.replace( /<[^>]*>/g, ' ' )
		.replace( /\s+/g, ' ' )
		.trim();

	if (
		keywordExistsInText( cleanContent, keyword ) ||
		keywordMatchesFlexible( cleanContent, keyword )
	) {
		return createCheck( {
			id: 'keyword_in_content',
			title: sprintf(
				/* translators: %s: focus keyword */
				__( 'Focus keyword "%s" found in content.', 'surerank' ),
				keyword
			),
			status: 'success',
			type: 'keyword',
		} );
	}

	return createCheck( {
		id: 'keyword_in_content',
		title: sprintf(
			/* translators: %s: focus keyword */
			__( 'Focus keyword "%s" not found in content.', 'surerank' ),
			keyword
		),
		status: 'warning',
		type: 'keyword',
	} );
};
