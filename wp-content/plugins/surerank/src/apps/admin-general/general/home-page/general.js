import {
	INPUT_VARIABLE_SUGGESTIONS as variableSuggestions,
	DESCRIPTION_LENGTH,
	TITLE_LENGTH,
	MAX_EDITOR_INPUT_LENGTH,
} from '@Global/constants';
import { __ } from '@wordpress/i18n';
import { useRef, useState } from '@wordpress/element';
import { Label, EditorInput, Container, Text } from '@bsf/force-ui';
import {
	editorValueToString,
	stringValueToFormatJSON,
	truncateText,
	cn,
} from '@Functions/utils';
import Preview from '@GlobalComponents/preview';
import replacement from '@Functions/replacement';
import useSettings from '@/global/hooks/use-admin-settings';
import GeneratePageContent from '@/functions/page-content-generator';
import AdminMagicButton from './magic-button';
import AIModal from './ai-modal';
import useAIModal from '@/global/hooks/use-ai-modal';

const HomePageGeneralSettings = () => {
	const { metaSettings, siteSettings, setMetaSetting } = useSettings();
	const { home_page_title: title, home_page_description: description } =
		metaSettings;

	const titleEditor = useRef( null );
	const descriptionEditor = useRef( null );

	const [ editorKey, setEditorKey ] = useState( 0 );

	const handleUpdateMetaSettings = ( key, value ) => {
		if ( metaSettings[ key ] === value ) {
			return;
		}
		setMetaSetting( key, value );
	};

	// AI modal management
	const { aiModal, handleOpenAIModal, handleCloseAIModal, handleUseThis } =
		useAIModal( ( fieldKey, content ) => {
			handleUpdateMetaSettings( fieldKey, content );

			setEditorKey( ( prev ) => prev + 1 );
		} );

	const faviconImageUrl = siteSettings?.site?.favicon
		? siteSettings?.site?.favicon
		: '';
	const titleContent = replacement( title, siteSettings?.site );
	const descriptionContent = replacement( description, siteSettings?.site );
	const titleContentTruncated = truncateText( titleContent, TITLE_LENGTH );
	const descriptionContentTruncated = truncateText(
		descriptionContent,
		DESCRIPTION_LENGTH
	);

	const inputTitleContent = replacement( title, siteSettings?.site );
	const inputDescriptionContent = replacement(
		description,
		siteSettings?.site
	);

	return (
		<Container direction="column" className="w-full gap-6">
			{ /* Search Engine Title input */ }
			<div className="space-y-1.5">
				{ /* Label & Limit */ }
				<div className="flex items-center justify-start gap-1">
					<Label tag="span" size="sm" className="space-x-0.5">
						<span>{ __( 'Search Engine Title', 'surerank' ) }</span>
					</Label>
					<div className="ml-auto inline-flex items-center gap-2">
						<span className="text-xs leading-4 font-normal text-field-helper">
							<span
								className={ cn( {
									'text-text-error':
										inputTitleContent?.length >
										TITLE_LENGTH,
								} ) }
							>
								{ inputTitleContent?.length ?? 0 }
							</span>
							/{ TITLE_LENGTH }
						</span>
						<AdminMagicButton
							onClick={ () =>
								handleOpenAIModal(
									'home_page_title',
									'home_page_title'
								)
							}
							tooltip={ __( 'Generate with AI', 'surerank' ) }
						/>
					</div>
				</div>
				{ /* Input */ }
				<EditorInput
					key={ `home-page-title-${ editorKey }` }
					ref={ titleEditor }
					by="label"
					trigger="@"
					defaultValue={ stringValueToFormatJSON(
						metaSettings.home_page_title !== ''
							? metaSettings.home_page_title
							: metaSettings?.global_default?.home_page_title,
						variableSuggestions,
						'value'
					) }
					options={ variableSuggestions }
					onChange={ ( editorState ) => {
						handleUpdateMetaSettings(
							'home_page_title',
							editorValueToString( editorState.toJSON() ) !== ''
								? editorValueToString( editorState.toJSON() )
								: metaSettings?.global_default?.home_page_title
						);
					} }
					placeholder={ '' }
				/>
				{ /* Hint text */ }
				<Text size={ 14 } weight={ 400 } color="help">
					{ __( 'Type @ to view variable suggestions', 'surerank' ) }
				</Text>
			</div>

			{ /* Search Engine Description input */ }
			<div className="space-y-1.5">
				{ /* Label & Limit */ }
				<div className="flex items-center justify-start gap-1">
					<Label tag="span" size="sm" className="space-x-0.5">
						<span>
							{ __( 'Search Engine Description', 'surerank' ) }
						</span>
					</Label>
					<div className="ml-auto inline-flex items-center gap-2">
						<span className="text-xs leading-4 font-normal text-field-helper">
							<span
								className={ cn( {
									'text-text-error':
										inputDescriptionContent?.length >
										DESCRIPTION_LENGTH,
								} ) }
							>
								{ inputDescriptionContent?.length ?? 0 }
							</span>
							/{ DESCRIPTION_LENGTH }
						</span>
						<AdminMagicButton
							onClick={ () =>
								handleOpenAIModal(
									'home_page_description',
									'home_page_description'
								)
							}
							tooltip={ __( 'Generate with AI', 'surerank' ) }
						/>
					</div>
				</div>
				{ /* Input */ }
				<EditorInput
					key={ `home-page-description-${ editorKey }` }
					ref={ descriptionEditor }
					className="[&+div]:items-start [&+div]:pt-1"
					by="label"
					trigger="@"
					defaultValue={ stringValueToFormatJSON(
						metaSettings.home_page_description,
						variableSuggestions,
						'value'
					) }
					options={ variableSuggestions }
					onChange={ ( editorState ) => {
						handleUpdateMetaSettings(
							'home_page_description',
							editorValueToString( editorState.toJSON() )
						);
					} }
					placeholder={ '' }
					maxLength={ MAX_EDITOR_INPUT_LENGTH }
				/>
				{ /* Hint text */ }
				<Text size={ 14 } weight={ 400 } color="help">
					{ __( 'Type @ to view variable suggestions', 'surerank' ) }
				</Text>
			</div>

			<div className="space-y-2.5 px-0">
				<div className="flex items-center justify-between gap-10">
					<div className="flex items-center justify-start gap-1">
						<Label tag="span" size="sm" className="space-x-0.5">
							<span>
								{ __( 'Search Engine Preview', 'surerank' ) }
							</span>
						</Label>
					</div>
				</div>

				<Preview
					siteTitle={ siteSettings?.site?.site_name }
					title={ titleContentTruncated }
					faviconURL={ faviconImageUrl }
					description={ descriptionContentTruncated }
					permalink={ siteSettings?.site?.site_url ?? '' }
					deviceType={ 'desktop' }
				/>
			</div>

			{ /* AI Modal */ }
			{ aiModal.open && (
				<AIModal
					fieldType={ aiModal.fieldType }
					onClose={ handleCloseAIModal }
					onUseThis={ handleUseThis }
				/>
			) }
		</Container>
	);
};

const PAGE_CONTENT = [
	{
		container: null,
		content: [
			{
				id: 'homepage-general-settings',
				type: 'custom',
				component: <HomePageGeneralSettings />,
			},
		],
	},
];

const GeneralSettings = () => {
	return <GeneratePageContent json={ PAGE_CONTENT } />;
};

export default GeneralSettings;
