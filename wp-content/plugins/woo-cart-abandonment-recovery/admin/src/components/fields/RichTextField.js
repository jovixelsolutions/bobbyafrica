import { useEffect, useState } from 'react';
import { Label, Button, Tabs } from '@bsf/force-ui';
import { ArrowUpTrayIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

const RichTextField = ( {
	title,
	name,
	id,
	value,
	handleChange,
	size = 'normal',
} ) => {
	const [ activeTab, setActiveTab ] = useState( 'visual' );
	useEffect( () => {
		let isActive = true;
		if ( isActive ) {
			jQuery( document ).on(
				'tinymce-editor-setup',

				function ( event, editor ) {
					editor.settings.toolbar1 =
						'formatselect,wcar_fields,bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,spellchecker,fullscreen';
					editor.on( 'change', function () {
						handleChange( name, editor.getContent() );
					} );
					editor.on( 'keyup', function () {
						handleChange( name, editor.getContent() );
					} );
					editor.settings.forced_root_block = false;

					if ( 'email_body' === name ) {
						editor.addButton( 'wcar_fields', {
							type: 'menubutton',
							text: 'WCAR Fields',
							icon: '',
							classes: 'listbox',
							menu: [
								{
									text: 'Abandoned Product Details Table',
									value: '{{cart.product.table}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Abandoned Product Names',
									value: '{{cart.product.names}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Admin Firstname',
									value: '{{admin.firstname}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Admin Company',
									value: '{{admin.company}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Cart Checkout URL',
									value: '{{cart.checkout_url}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Coupon Code',
									value: '{{cart.coupon_code}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Customer First Name',
									value: '{{customer.firstname}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Customer Last Name',
									value: '{{customer.lastname}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Customer Full Name',
									value: '{{customer.fullname}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Cart Abandonment Date',
									value: '{{cart.abandoned_date}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Site URL',
									value: '{{site.url}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
								{
									text: 'Unsubscribe Link',
									value: '{{cart.unsubscribe}}',
									onclick() {
										editor.insertContent( this.value() );
									},
								},
							].sort( function ( a, b ) {
								return a.text.localeCompare( b.text );
							} ),
						} );
					}
				}
			);

			wp.editor.initialize( id, {
				tinymce: true,
				quicktags: true,
				mediaButtons: false,
			} );
		}

		return () => {
			isActive = false;
			if ( tinymce.activeEditor ) {
				tinymce.activeEditor.destroy();
			}
		};
	}, [] );

	useEffect( () => {
		const editor = tinymce.get( id );
		if ( editor && editor.getContent() !== value ) {
			editor.setContent( value || '' );
		}
	}, [ value, id ] );

	const handleTabChange = ( tab ) => {
		setActiveTab( tab );
		const editor = tinymce.get( id );
		if ( editor ) {
			const container = editor.getContainer();
			const wrapper = container.closest( `#wp-${ id }-wrap` );
			if ( wrapper ) {
				const switchClass =
					tab === 'visual' ? 'switch-tmce' : 'switch-html';
				const modeButton = wrapper.querySelector( `.${ switchClass }` );
				if ( modeButton ) {
					modeButton.click();
				}
			}
		}
	};

	function updateValue( e ) {
		handleChange( name, e.target.value );
	}

	const handleAddMedia = () => {
		if ( typeof wp !== 'undefined' && wp.media ) {
			const mediaUploader = wp.media( {
				title: 'Select Media',
				button: {
					text: 'Insert Media',
				},
				multiple: false,
			} );

			mediaUploader.on( 'select', () => {
				const attachment = mediaUploader
					.state()
					.get( 'selection' )
					.first()
					.toJSON();
				const editor = tinymce.get( id );

				if ( editor ) {
					if (
						attachment.type === 'image' ||
						attachment.subtype === 'gif'
					) {
						editor.insertContent(
							`<img src="${ attachment.url }" alt="${
								attachment.alt || attachment.title || ''
							}" />`
						);
					} else if ( attachment.type === 'video' ) {
						editor.insertContent(
							`<video controls><source src="${ attachment.url }" type="${ attachment.mime }">Your browser does not support the video tag.</video>`
						);
					} else {
						const linkText =
							attachment.title ||
							attachment.filename ||
							'Click to view file';
						editor.insertContent(
							`<a href="${ attachment.url }" target="_blank">${ linkText }</a>`
						);
					}
				}
			} );

			mediaUploader.open();
		} else {
			console.warn( 'wp.media is not available.' );
		}
	};

	const fieldId = `field-${ Math.random().toString( 36 ).substr( 2, 9 ) }`;

	return (
		<section
			className="flex flex-col py-5 justify-between gap-2 lg:gap-5 border-0 border-b border-solid border-gray-200 last:border-b-0"
			aria-labelledby={ title ? `${ fieldId }-title` : undefined }
		>
			<div className="flex justify-between items-center">
				<Label
					className="font-semibold mb-1"
					htmlFor={ fieldId }
					size={ `${ size === 'normal' ? 'md' : 'sm' }` }
					id={ `${ fieldId }-title` }
					as="h3"
				>
					{ title.replace( /\b\w/g, ( char ) =>
						char.toLocaleUpperCase()
					) }
				</Label>
				<div className="flex items-center gap-2 w-fit">
					<Tabs.Group
						variant="rounded"
						size="sm"
						className="p-0 bg-white"
						onChange={ ( { value: val } ) => {
							handleTabChange( val.slug );
						} }
					>
						<Tabs.Tab
							className={ `text-sm font-medium hover:text-primary-600 ${
								activeTab === 'visual'
									? 'text-primary-600 bg-primary-25 outline outline-1 outline-primary-300 focus:outline-1'
									: ''
							}` }
							key={ 'visual' }
							text={ __( 'Visual', 'sureforms-pro' ) }
							slug={ 'visual' }
						/>
						<Tabs.Tab
							className={ `text-sm font-medium hover:text-primary-600 ${
								activeTab === 'html'
									? 'text-primary-600 bg-primary-25 outline outline-1 outline-primary-300 focus:outline-1'
									: ''
							}` }
							key={ 'html' }
							text={ __( 'HTML', 'sureforms-pro' ) }
							slug={ 'html' }
						/>
					</Tabs.Group>
					<Button
						className="px-4 bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300 whitespace-nowrap"
						size="sm"
						tag="button"
						icon={ <ArrowUpTrayIcon className="h-4 w-4" /> }
						iconPosition="left"
						variant="outline"
						onClick={ handleAddMedia }
					>
						Add Media
					</Button>
				</div>
			</div>

			<textarea
				name={ name }
				id={ id }
				value={ value }
				rows="17"
				onChange={ updateValue }
				className="w-full focus:border-flamingo-400 focus:shadow-sm focus:shadow-flamingo-400 focus:outline-0"
			></textarea>
		</section>
	);
};

export default RichTextField;

