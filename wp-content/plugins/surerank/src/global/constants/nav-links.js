import {
	Type,
	House,
	Paperclip,
	Bot,
	Network,
	Globe,
	Share2,
	ArrowLeftRight,
	Grid,
	Settings,
	FileText,
	ArrowUpDown,
	ExternalLink,
	CheckCheck,
	Waypoints,
	Mail,
	Image,
	Users,
	Ellipsis,
	File,
} from 'lucide-react';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

// Page contents
import { PAGE_CONTENT as GENERAL_PAGE_CONTENT } from '@AdminGeneral/social/general/general';
import { PAGE_CONTENT as FACEBOOK_PAGE_CONTENT } from '@AdminGeneral/social/facebook/facebook';
import { PAGE_CONTENT as TWITTER_PAGE_CONTENT } from '@AdminGeneral/social/twitter/twitter';
import { PAGE_CONTENT as SITEMAPS_PAGE_CONTENT } from '@AdminGeneral/advanced/sitemaps/sitemaps';
import { PAGE_CONTENT as IMAGE_SEO_PAGE_CONTENT } from '@AdminGeneral/advanced/image-seo/image-seo';
import { PAGE_CONTENT as ARCHIVE_PAGES_PAGE_CONTENT } from '@/apps/admin-general/advanced/archive-pages/archive-pages';
import { PAGE_CONTENT as ADVANCED_PAGE_CONTENT } from '@AdminGeneral/general/home-page/advanced';
import { PAGE_CONTENT as SOCIAL_ACCOUNTS_PAGE_CONTENT } from '@AdminGeneral/social/account/account';
import { PAGE_CONTENT as TITLE_AND_DESCRIPTION_PAGE_CONTENT } from '@AdminGeneral/general/title-and-description/title-and-description';
import { PAGE_CONTENT as ROBOTS_TXT_PAGE_CONTENT } from '@AdminGeneral/advanced/tools/robots-txt-editor/robots-txt-editor';
import { PAGE_CONTENT as EMAIL_REPORTS_PAGE_CONTENT } from '@AdminGeneral/advanced/email-reports/email-reports';
import {
	ENABLE_GOOGLE_CONSOLE,
	ENABLE_SCHEMAS,
	ENABLE_MIGRATION,
} from '@Global/constants';

/**
 * Static navigation configuration - used for router setup and other non-component contexts
 * @return {Array} Navigation configuration
 */
export const getNavLinks = () => {
	const links = [
		{
			section: __( 'Dashboard', 'surerank' ),
			sectionId: 'dashboard',
			links: [
				{
					label: __( 'Dashboard', 'surerank' ),
					path: '/dashboard',
					icon: House,
				},
				{
					label: __( 'Site SEO Audit', 'surerank' ),
					path: '/site-seo-analysis',
					icon: House,
				},
			],
		},
		{
			section: __( 'General', 'surerank' ),
			sectionId: 'general',
			links: [
				{
					// This is the root path for the settings page
					path: '/general',
					label: __( 'Meta Templates', 'surerank' ),
					icon: Type,
					pageContent: TITLE_AND_DESCRIPTION_PAGE_CONTENT,
					migratable: true,
				},
				{
					label: __( 'Social', 'surerank' ),
					path: '/general/social',
					icon: Share2,
					migratable: true,
					submenu: [
						{
							path: '/general/social',
							label: __( 'Default Social Image', 'surerank' ),
							pageContent: GENERAL_PAGE_CONTENT,
						},
						{
							path: '/general/social/facebook',
							label: __( 'Facebook', 'surerank' ),
							pageContent: FACEBOOK_PAGE_CONTENT,
						},
						{
							path: '/general/social/x',
							label: __( 'X', 'surerank' ),
							pageContent: TWITTER_PAGE_CONTENT,
						},
						{
							path: '/general/social/accounts',
							label: __( 'Other Accounts', 'surerank' ),
							pageContent: SOCIAL_ACCOUNTS_PAGE_CONTENT,
						},
					],
				},
				...( window?.surerank_globals?.home_page_static !== 'page'
					? [
							{
								path: HOME_PAGE_PATH,
								label: __( 'Home Page', 'surerank' ),
								icon: House,
								migratable: true,
								submenu: [
									{
										path: HOME_PAGE_PATH,
										label: __( 'General', 'surerank' ),
									},
									{
										path: '/general/homepage/social',
										label: __( 'Social', 'surerank' ),
									},
									{
										path: '/general/homepage/advanced',
										label: __( 'Advanced', 'surerank' ),
										pageContent: ADVANCED_PAGE_CONTENT,
									},
								],
							},
					  ]
					: [] ),
				{
					path: '/general/archive_pages',
					label: __( 'Archive Pages', 'surerank' ),
					icon: Paperclip,
					migratable: true,
					pageContent: ARCHIVE_PAGES_PAGE_CONTENT,
				},
				{
					path: '/general/sitemaps',
					label: __( 'Sitemaps', 'surerank' ),
					icon: Network,
					pageContent: SITEMAPS_PAGE_CONTENT,
					migratable: true,
				},
				{
					path: '/general/site-information',
					label: __( 'Site Information', 'surerank' ),
					icon: File,
					migratable: true,
				},
			],
		},
		{
			section: __( 'Advanced', 'surerank' ),
			sectionId: 'advanced',
			links: [
				{
					path: '/advanced/robot_instructions',
					label: __( 'Robot Instructions', 'surerank' ),
					icon: Bot,
					migratable: true,
					submenu: [
						{
							path: '/advanced/robot_instructions/indexing',
							label: __( 'No Index', 'surerank' ),
						},
						{
							path: '/advanced/robot_instructions/following',
							label: __( 'No Follow', 'surerank' ),
						},
						{
							path: '/advanced/robot_instructions/archiving',
							label: __( 'No Archive', 'surerank' ),
						},
					],
				},
				...( ENABLE_SCHEMAS
					? [
							{
								path: '/advanced/schema',
								label: __( 'Schema', 'surerank' ),
								icon: Globe,
								migratable: false,
							},
					  ]
					: [] ),
				{
					path: '/advanced/robots-txt-editor',
					label: __( 'Robots.txt Editor', 'surerank' ),
					icon: FileText,
					pageContent: ROBOTS_TXT_PAGE_CONTENT,
					migratable: false,
				},
				{
					path: '/advanced/breadcrumbs',
					label: __( 'Breadcrumbs', 'surerank' ),
					icon: Ellipsis,
					migratable: false,
				},
				{
					path: '/advanced/image-seo',
					label: __( 'Image SEO', 'surerank' ),
					icon: Image,
					pageContent: IMAGE_SEO_PAGE_CONTENT,
					migratable: true,
				},
				{
					path: '/advanced/image-generation',
					label: __( 'Image Generation', 'surerank' ),
					icon: Image,
				},
				{
					path: '/advanced/email-reports',
					label: __( 'Email Summary', 'surerank' ),
					icon: Mail,
					pageContent: EMAIL_REPORTS_PAGE_CONTENT,
					migratable: false,
				},
				{
					path: '/advanced/instant-indexing',
					label: __( 'Instant Indexing', 'surerank' ),
					icon: CheckCheck,
					submenu: [
						{
							path: '/advanced/instant-indexing/settings',
							label: __( 'Settings', 'surerank' ),
						},
						{
							path: '/advanced/instant-indexing/logs',
							label: __( 'Logs', 'surerank' ),
						},
					],
				},
				{
					path: '/advanced/google-indexing',
					label: __( 'Google Indexing', 'surerank' ),
					icon: Waypoints,
					submenu: [
						{
							path: '/advanced/google-indexing/settings',
							label: __( 'Settings', 'surerank' ),
						},
						{
							path: '/advanced/google-indexing/logs',
							label: __( 'Logs', 'surerank' ),
						},
					],
				},
			],
		},
		...( ENABLE_GOOGLE_CONSOLE
			? [
					{
						section: __( 'Search Console', 'surerank' ),
						sectionId: 'search-console',
						links: [
							{
								path: '/search-console',
								label: __( 'Search Console', 'surerank' ),
								icon: Network,
								pageContent: [],
								migratable: false,
							},
							{
								label: __( 'Content Analysis', 'surerank' ),
								path: '/content-performance',
								icon: House,
								migratable: false,
							},
						],
					},
			  ]
			: [] ),
		{
			section: __( 'Link Manager', 'surerank' ),
			sectionId: 'link-manager',
			links: [
				{
					path: '/link-manager/redirection-manager',
					label: __( 'Redirections', 'surerank' ),
					icon: ExternalLink,
					migratable: false,
				},
				{
					path: '/link-manager/link-manager/dashboard',
					label: __( 'Link Manager', 'surerank' ),
					icon: Network,
					migratable: false,
					submenu: [
						{
							path: '/link-manager/link-manager/dashboard',
							label: __( 'Dashboard', 'surerank' ),
						},
						{
							path: '/link-manager/link-manager/settings',
							label: __( 'Settings', 'surerank' ),
						},
					],
				},
				{
					path: '/link-manager/link-suggestion',
					label: __( 'Link Suggestions', 'surerank' ),
					icon: Paperclip,
					migratable: false,
				},
			],
		},
		{
			section: __( 'Tools', 'surerank' ),
			sectionId: 'tools',
			links: [
				{
					path: '/tools/manage-features',
					label: __( 'Manage Features', 'surerank' ),
					icon: Grid,
					migratable: false,
				},
				{
					path: '/tools/import-export',
					label: __( 'Import/Export', 'surerank' ),
					icon: ArrowUpDown,
					migratable: false,
				},
				...( ENABLE_MIGRATION
					? [
							{
								path: '/tools/migrate',
								label: __( 'Migrate to SureRank', 'surerank' ),
								icon: ArrowLeftRight,
								migratable: false,
							},
					  ]
					: [] ),
				{
					path: '/tools/miscellaneous',
					label: __( 'Miscellaneous', 'surerank' ),
					icon: Settings,
					migratable: false,
				},
				{
					path: '/tools/role-manager',
					label: __( 'Role Manager', 'surerank' ),
					icon: Users,
					migratable: false,
				},
			],
		},
	];

	const filteredLinks = applyFilters( 'surerank-pro.nav-links', links );

	const sectionMap = new Map();

	filteredLinks.forEach( ( section ) => {
		if ( section.links ) {
			const linkMap = new Map();

			section.links.forEach( ( link ) => {
				linkMap.set( link.path, link );
			} );

			section.links = Array.from( linkMap.values() );
		}

		sectionMap.set( section.sectionId, section );
	} );

	return Array.from( sectionMap.values() );
};

export const HOME_PAGE_PATH = '/general/homepage';
export const GENERAL_SITEMAPS_PATH = '/general/sitemaps';
