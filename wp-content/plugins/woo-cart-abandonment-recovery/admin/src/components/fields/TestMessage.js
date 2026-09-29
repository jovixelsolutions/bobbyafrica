import { useEffect, useState } from 'react';
import { Input, Button, Loader, toast, Select } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import FieldWrapper from '@Components/common/FieldWrapper';
import { doApiFetch } from '@Store';

const TestMessage = ( {
	title,
	description,
	value,
	action,
	actionNonce,
	messageType,
} ) => {
	const [ phone, setPhone ] = useState( '' );
	const [ code, setCode ] = useState( '' );
	const [ isSending, setIsSending ] = useState( false );
	const countryPhoneCodes = [
		{ text: 'AD (+376)', value: '+376' },
		{ text: 'AE (+971)', value: '+971' },
		{ text: 'AF (+93)', value: '+93' },
		{ text: 'AG (+1-268)', value: '+1268' },
		{ text: 'AI (+1-264)', value: '+1264' },
		{ text: 'AL (+355)', value: '+355' },
		{ text: 'AM (+374)', value: '+374' },
		{ text: 'AO (+244)', value: '+244' },
		{ text: 'AQ (+672)', value: '+672' },
		{ text: 'AR (+54)', value: '+54' },
		{ text: 'AS (+1-684)', value: '+1684' },
		{ text: 'AT (+43)', value: '+43' },
		{ text: 'AU (+61)', value: '+61' },
		{ text: 'AW (+297)', value: '+297' },
		{ text: 'AX (+358)', value: '+358' },
		{ text: 'AZ (+994)', value: '+994' },
		{ text: 'BA (+387)', value: '+387' },
		{ text: 'BB (+1-246)', value: '+1246' },
		{ text: 'BD (+880)', value: '+880' },
		{ text: 'BE (+32)', value: '+32' },
		{ text: 'BF (+226)', value: '+226' },
		{ text: 'BG (+359)', value: '+359' },
		{ text: 'BH (+973)', value: '+973' },
		{ text: 'BI (+257)', value: '+257' },
		{ text: 'BJ (+229)', value: '+229' },
		{ text: 'BL (+590)', value: '+590' },
		{ text: 'BM (+1-441)', value: '+1441' },
		{ text: 'BN (+673)', value: '+673' },
		{ text: 'BO (+591)', value: '+591' },
		{ text: 'BQ (+599)', value: '+599' },
		{ text: 'BR (+55)', value: '+55' },
		{ text: 'BS (+1-242)', value: '+1242' },
		{ text: 'BT (+975)', value: '+975' },
		{ text: 'BV (+47)', value: '+47' },
		{ text: 'BW (+267)', value: '+267' },
		{ text: 'BY (+375)', value: '+375' },
		{ text: 'BZ (+501)', value: '+501' },
		{ text: 'CA (+1)', value: '+1' },
		{ text: 'CC (+61)', value: '+61' },
		{ text: 'CD (+243)', value: '+243' },
		{ text: 'CF (+236)', value: '+236' },
		{ text: 'CG (+242)', value: '+242' },
		{ text: 'CH (+41)', value: '+41' },
		{ text: 'CI (+225)', value: '+225' },
		{ text: 'CK (+682)', value: '+682' },
		{ text: 'CL (+56)', value: '+56' },
		{ text: 'CM (+237)', value: '+237' },
		{ text: 'CN (+86)', value: '+86' },
		{ text: 'CO (+57)', value: '+57' },
		{ text: 'CR (+506)', value: '+506' },
		{ text: 'CU (+53)', value: '+53' },
		{ text: 'CV (+238)', value: '+238' },
		{ text: 'CW (+599)', value: '+599' },
		{ text: 'CX (+61)', value: '+61' },
		{ text: 'CY (+357)', value: '+357' },
		{ text: 'CZ (+420)', value: '+420' },
		{ text: 'DE (+49)', value: '+49' },
		{ text: 'DJ (+253)', value: '+253' },
		{ text: 'DK (+45)', value: '+45' },
		{ text: 'DM (+1-767)', value: '+1767' },
		{ text: 'DO (+1-809)', value: '+1809' },
		{ text: 'DZ (+213)', value: '+213' },
		{ text: 'EC (+593)', value: '+593' },
		{ text: 'EE (+372)', value: '+372' },
		{ text: 'EG (+20)', value: '+20' },
		{ text: 'EH (+212)', value: '+212' },
		{ text: 'ER (+291)', value: '+291' },
		{ text: 'ES (+34)', value: '+34' },
		{ text: 'ET (+251)', value: '+251' },
		{ text: 'FI (+358)', value: '+358' },
		{ text: 'FJ (+679)', value: '+679' },
		{ text: 'FK (+500)', value: '+500' },
		{ text: 'FM (+691)', value: '+691' },
		{ text: 'FO (+298)', value: '+298' },
		{ text: 'FR (+33)', value: '+33' },
		{ text: 'GA (+241)', value: '+241' },
		{ text: 'GB (+44)', value: '+44' },
		{ text: 'GD (+1-473)', value: '+1473' },
		{ text: 'GE (+995)', value: '+995' },
		{ text: 'GF (+594)', value: '+594' },
		{ text: 'GG (+44)', value: '+44' },
		{ text: 'GH (+233)', value: '+233' },
		{ text: 'GI (+350)', value: '+350' },
		{ text: 'GL (+299)', value: '+299' },
		{ text: 'GM (+220)', value: '+220' },
		{ text: 'GN (+224)', value: '+224' },
		{ text: 'GP (+590)', value: '+590' },
		{ text: 'GQ (+240)', value: '+240' },
		{ text: 'GR (+30)', value: '+30' },
		{ text: 'GT (+502)', value: '+502' },
		{ text: 'GU (+1-671)', value: '+1671' },
		{ text: 'GW (+245)', value: '+245' },
		{ text: 'GY (+592)', value: '+592' },
		{ text: 'HK (+852)', value: '+852' },
		{ text: 'HM (+672)', value: '+672' },
		{ text: 'HN (+504)', value: '+504' },
		{ text: 'HR (+385)', value: '+385' },
		{ text: 'HT (+509)', value: '+509' },
		{ text: 'HU (+36)', value: '+36' },
		{ text: 'ID (+62)', value: '+62' },
		{ text: 'IE (+353)', value: '+353' },
		{ text: 'IL (+972)', value: '+972' },
		{ text: 'IM (+44)', value: '+44' },
		{ text: 'IN (+91)', value: '+91' },
		{ text: 'IO (+246)', value: '+246' },
		{ text: 'IQ (+964)', value: '+964' },
		{ text: 'IR (+98)', value: '+98' },
		{ text: 'IS (+354)', value: '+354' },
		{ text: 'IT (+39)', value: '+39' },
		{ text: 'JE (+44)', value: '+44' },
		{ text: 'JM (+1-876)', value: '+1876' },
		{ text: 'JO (+962)', value: '+962' },
		{ text: 'JP (+81)', value: '+81' },
		{ text: 'KE (+254)', value: '+254' },
		{ text: 'KG (+996)', value: '+996' },
		{ text: 'KH (+855)', value: '+855' },
		{ text: 'KI (+686)', value: '+686' },
		{ text: 'KM (+269)', value: '+269' },
		{ text: 'KN (+1-869)', value: '+1869' },
		{ text: 'KP (+850)', value: '+850' },
		{ text: 'KR (+82)', value: '+82' },
		{ text: 'KW (+965)', value: '+965' },
		{ text: 'KY (+1-345)', value: '+1345' },
		{ text: 'KZ (+7)', value: '+7' },
		{ text: 'LA (+856)', value: '+856' },
		{ text: 'LB (+961)', value: '+961' },
		{ text: 'LC (+1-758)', value: '+1758' },
		{ text: 'LI (+423)', value: '+423' },
		{ text: 'LK (+94)', value: '+94' },
		{ text: 'LR (+231)', value: '+231' },
		{ text: 'LS (+266)', value: '+266' },
		{ text: 'LT (+370)', value: '+370' },
		{ text: 'LU (+352)', value: '+352' },
		{ text: 'LV (+371)', value: '+371' },
		{ text: 'LY (+218)', value: '+218' },
		{ text: 'MA (+212)', value: '+212' },
		{ text: 'MC (+377)', value: '+377' },
		{ text: 'MD (+373)', value: '+373' },
		{ text: 'ME (+382)', value: '+382' },
		{ text: 'MF (+590)', value: '+590' },
		{ text: 'MG (+261)', value: '+261' },
		{ text: 'MH (+692)', value: '+692' },
		{ text: 'MK (+389)', value: '+389' },
		{ text: 'ML (+223)', value: '+223' },
		{ text: 'MM (+95)', value: '+95' },
		{ text: 'MN (+976)', value: '+976' },
		{ text: 'MO (+853)', value: '+853' },
		{ text: 'MP (+1-670)', value: '+1670' },
		{ text: 'MQ (+596)', value: '+596' },
		{ text: 'MR (+222)', value: '+222' },
		{ text: 'MS (+1-664)', value: '+1664' },
		{ text: 'MT (+356)', value: '+356' },
		{ text: 'MU (+230)', value: '+230' },
		{ text: 'MV (+960)', value: '+960' },
		{ text: 'MW (+265)', value: '+265' },
		{ text: 'MX (+52)', value: '+52' },
		{ text: 'MY (+60)', value: '+60' },
		{ text: 'MZ (+258)', value: '+258' },
		{ text: 'NA (+264)', value: '+264' },
		{ text: 'NC (+687)', value: '+687' },
		{ text: 'NE (+227)', value: '+227' },
		{ text: 'NF (+672)', value: '+672' },
		{ text: 'NG (+234)', value: '+234' },
		{ text: 'NI (+505)', value: '+505' },
		{ text: 'NL (+31)', value: '+31' },
		{ text: 'NO (+47)', value: '+47' },
		{ text: 'NP (+977)', value: '+977' },
		{ text: 'NR (+674)', value: '+674' },
		{ text: 'NU (+683)', value: '+683' },
		{ text: 'NZ (+64)', value: '+64' },
		{ text: 'OM (+968)', value: '+968' },
		{ text: 'PA (+507)', value: '+507' },
		{ text: 'PE (+51)', value: '+51' },
		{ text: 'PF (+689)', value: '+689' },
		{ text: 'PG (+675)', value: '+675' },
		{ text: 'PH (+63)', value: '+63' },
		{ text: 'PK (+92)', value: '+92' },
		{ text: 'PL (+48)', value: '+48' },
		{ text: 'PM (+508)', value: '+508' },
		{ text: 'PN (+870)', value: '+870' },
		{ text: 'PR (+1-787)', value: '+1787' },
		{ text: 'PS (+970)', value: '+970' },
		{ text: 'PT (+351)', value: '+351' },
		{ text: 'PW (+680)', value: '+680' },
		{ text: 'PY (+595)', value: '+595' },
		{ text: 'QA (+974)', value: '+974' },
		{ text: 'RE (+262)', value: '+262' },
		{ text: 'RO (+40)', value: '+40' },
		{ text: 'RS (+381)', value: '+381' },
		{ text: 'RU (+7)', value: '+7' },
		{ text: 'RW (+250)', value: '+250' },
		{ text: 'SA (+966)', value: '+966' },
		{ text: 'SB (+677)', value: '+677' },
		{ text: 'SC (+248)', value: '+248' },
		{ text: 'SD (+249)', value: '+249' },
		{ text: 'SE (+46)', value: '+46' },
		{ text: 'SG (+65)', value: '+65' },
		{ text: 'SH (+290)', value: '+290' },
		{ text: 'SI (+386)', value: '+386' },
		{ text: 'SJ (+47)', value: '+47' },
		{ text: 'SK (+421)', value: '+421' },
		{ text: 'SL (+232)', value: '+232' },
		{ text: 'SM (+378)', value: '+378' },
		{ text: 'SN (+221)', value: '+221' },
		{ text: 'SO (+252)', value: '+252' },
		{ text: 'SR (+597)', value: '+597' },
		{ text: 'SS (+211)', value: '+211' },
		{ text: 'ST (+239)', value: '+239' },
		{ text: 'SV (+503)', value: '+503' },
		{ text: 'SX (+1-721)', value: '+1721' },
		{ text: 'SY (+963)', value: '+963' },
		{ text: 'SZ (+268)', value: '+268' },
		{ text: 'TC (+1-649)', value: '+1649' },
		{ text: 'TD (+235)', value: '+235' },
		{ text: 'TF (+262)', value: '+262' },
		{ text: 'TG (+228)', value: '+228' },
		{ text: 'TH (+66)', value: '+66' },
		{ text: 'TJ (+992)', value: '+992' },
		{ text: 'TK (+690)', value: '+690' },
		{ text: 'TL (+670)', value: '+670' },
		{ text: 'TM (+993)', value: '+993' },
		{ text: 'TN (+216)', value: '+216' },
		{ text: 'TO (+676)', value: '+676' },
		{ text: 'TR (+90)', value: '+90' },
		{ text: 'TT (+1-868)', value: '+1868' },
		{ text: 'TV (+688)', value: '+688' },
		{ text: 'TZ (+255)', value: '+255' },
		{ text: 'UA (+380)', value: '+380' },
		{ text: 'UG (+256)', value: '+256' },
		{ text: 'UM (+1)', value: '+1' },
		{ text: 'US (+1)', value: '+1' },
		{ text: 'UY (+598)', value: '+598' },
		{ text: 'UZ (+998)', value: '+998' },
		{ text: 'VA (+379)', value: '+379' },
		{ text: 'VC (+1-784)', value: '+1784' },
		{ text: 'VE (+58)', value: '+58' },
		{ text: 'VG (+1-284)', value: '+1284' },
		{ text: 'VI (+1-340)', value: '+1340' },
		{ text: 'VN (+84)', value: '+84' },
		{ text: 'VU (+678)', value: '+678' },
		{ text: 'WF (+681)', value: '+681' },
		{ text: 'WS (+685)', value: '+685' },
		{ text: 'YE (+967)', value: '+967' },
		{ text: 'YT (+262)', value: '+262' },
		{ text: 'ZA (+27)', value: '+27' },
		{ text: 'ZM (+260)', value: '+260' },
		{ text: 'ZW (+263)', value: '+263' },
	];
	useEffect( () => {
		const initialCode = countryPhoneCodes.find(
			( item ) => item.value === '+1'
		);
		setCode( initialCode );
	}, [] );

	const validateFields = () => {
		if ( ! code ) {
			toast.error(
				__(
					'Please select a country code',
					'woo-cart-abandonment-recovery'
				)
			);
			return false;
		}
		if ( ! phone ) {
			toast.error(
				__(
					'Please enter a phone number',
					'woo-cart-abandonment-recovery'
				)
			);
			return false;
		}
		const phoneNumber = code?.value + phone;
		if ( ! /^\+[1-9]\d{1,14}$/.test( phoneNumber ) ) {
			toast.error(
				__(
					'Please enter a valid phone number',
					'woo-cart-abandonment-recovery'
				)
			);
			return false;
		}

		if ( messageType === 'sms' && value && value.sms_body === '' ) {
			toast.error(
				__( 'Please enter SMS body', 'woo-cart-abandonment-recovery' )
			);
			return false;
		}

		if (
			messageType === 'whatsapp' &&
			value &&
			value?.whatsapp_template === ''
		) {
			toast.error(
				__(
					'Please select a WhatsApp template',
					'woo-cart-abandonment-recovery'
				)
			);
			return false;
		}
		return true;
	};

	const handleClick = () => {
		if ( ! validateFields() ) {
			return;
		}
		const ajaxUrl = cart_abandonment_admin?.ajax_url;
		const nonce = cart_abandonment_admin?.[ actionNonce ];
		const phoneNumber = code?.value + phone;

		const formData = new FormData();
		formData.append( 'action', action );
		formData.append( 'phone_number', phoneNumber );
		formData.append( 'security', nonce );

		if ( messageType === 'sms' && value ) {
			formData.append( 'sms_body', value.sms_body );
		} else if ( messageType === 'sms' && ! value ) {
			formData.append(
				'sms_body',
				'Test SMS! Hi {{customer.firstname}}! You left {{cart.product.names}} in your cart.'
			);
		}

		if ( messageType === 'whatsapp' && value ) {
			formData.append( 'whatsapp_template', value?.whatsapp_template );
			formData.append(
				'whatsapp_template_header_variables',
				value?.whatsapp_template_header_variables
			);
			formData.append(
				'wcf_whatsapp_template_body_variables',
				value?.whatsapp_template_body_variables
			);
		}

		setIsSending( true );
		doApiFetch(
			ajaxUrl,
			formData,
			'POST',
			( response ) => {
				if ( response.success ) {
					toast.success(
						__( 'Success', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				} else {
					toast.error(
						__( 'Error', 'woo-cart-abandonment-recovery' ),
						{
							description: response.data?.message || '',
						}
					);
				}
				setIsSending( false );
			},
			( error ) => {
				toast.error( __( 'Error', 'woo-cart-abandonment-recovery' ), {
					description: error.data?.message || '',
				} );
				setIsSending( false );
			},
			true
		);
	};

	return (
		<FieldWrapper title={ title } description={ description } type="block">
			<div className="flex gap-2 items-center">
				<div className="min-w-32">
					<Select
						onChange={ ( val ) => setCode( val ) }
						value={ code?.text }
						size="md"
						combobox={ true }
						searchPlaceholder="Search..."
					>
						<Select.Button
							placeholder={ __(
								'Country Code',
								'woo-cart-abandonment-recovery'
							) }
						/>
						<Select.Options>
							{ countryPhoneCodes.map( ( option ) => (
								<Select.Option
									key={ option.text }
									value={ option }
								>
									{ option.text }
								</Select.Option>
							) ) }
						</Select.Options>
					</Select>
				</div>
				<div className="flex-1">
					<Input
						className="w-full focus:[&>input]:ring-focus"
						type="text"
						size="md"
						value={ phone }
						onChange={ setPhone }
						placeholder={ __(
							'Enter Phone no.',
							'woo-cart-abandonment-recovery'
						) }
					/>
				</div>

				<Button
					className="w-fit bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300"
					size="md"
					tag="button"
					variant="outline"
					icon={
						isSending && (
							<Loader
								className="text-primary-600"
								size="md"
								variant="primary"
							/>
						)
					}
					iconPosition="left"
					onClick={ handleClick }
					disabled={ isSending }
				>
					{ __( 'Send Message', 'woo-cart-abandonment-recovery' ) }
				</Button>
			</div>
		</FieldWrapper>
	);
};

export default TestMessage;

