#!/usr/bin/env node

const { execSync } = require( 'child_process' );
const fs = require( 'fs' );
const path = require( 'path' );

const POT_FILE = 'languages/woo-cart-abandonment-recovery.pot';

const languages = [
	{ code: 'nl', locale: 'nl_NL' },
	{ code: 'fr', locale: 'fr_FR' },
	{ code: 'de', locale: 'de_DE' },
	{ code: 'es', locale: 'es_ES' },
	{ code: 'it', locale: 'it_IT' },
	{ code: 'pt', locale: 'pt_PT' },
	{ code: 'pl', locale: 'pl_PL' },
];

function runCommand( command ) {
	try {
		console.log( `\n🔄 Running: ${ command }\n` );
		execSync( command, { stdio: 'inherit' } );
	} catch ( err ) {
		console.error( `❌ Failed: ${ command }` );
	}
}

function ensurePoFile( locale ) {
	const poPath = `languages/woo-cart-abandonment-recovery-${ locale }.po`;
	if ( ! fs.existsSync( poPath ) ) {
		console.log( `📄 ${ poPath } not found — creating from ${ POT_FILE }` );
		runCommand(
			`msginit --locale=${ locale } --input=${ POT_FILE } --output-file=${ poPath } --no-translator`
		);
	}
	return poPath;
}

languages.forEach( ( { code, locale } ) => {
	const poFile = ensurePoFile( locale );

	const poContent = fs.readFileSync( poFile, 'utf8' );
	const entries = poContent.split( '\n\n' );

	// Find untranslated entries
	const untranslated = entries.filter( ( e ) =>
		/msgstr\s+""\s*$/m.test( e )
	);

	if ( untranslated.length === 0 ) {
		console.log( `✅ ${ poFile } — no missing translations.` );
		return;
	}

	console.log(
		`📄 ${ poFile } — found ${ untranslated.length } missing translations.`
	);

	const header = entries[ 0 ]; // Keep header
	const tempFile = path.join( 'languages', `temp-${ code }.po` );

	// Create temporary file with only missing translations
	fs.writeFileSync(
		tempFile,
		[ header, ...untranslated ].join( '\n\n' ),
		'utf8'
	);

	// Translate using gpt-po (no unsupported flags)
	runCommand(
		`API_TIMEOUT=120000 gpt-po translate --po ${ tempFile } --lang ${ code } --verbose`
	);

	// Merge back into original
	const translatedTemp = fs.readFileSync( tempFile, 'utf8' );
	const translatedEntries = translatedTemp.split( '\n\n' );

	let updatedPo = poContent;
	untranslated.forEach( ( originalEntry, idx ) => {
		updatedPo = updatedPo.replace(
			originalEntry,
			translatedEntries[ idx + 1 ]
		);
	} );

	fs.writeFileSync( poFile, updatedPo, 'utf8' );

	fs.unlinkSync( tempFile );
	console.log( `✅ Updated ${ poFile } with new translations.` );
} );
