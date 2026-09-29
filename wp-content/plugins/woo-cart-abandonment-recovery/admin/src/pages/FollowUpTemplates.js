import { useLocation } from 'react-router-dom';
import { Title, Tabs } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';

import SectionWrapper from '@Components/common/SectionWrapper';
import EmailTemplates from '@Components/followUpTemplates/emailTemplates/EmailTemplates';
import SmsTemplates from '@Components/followUpTemplates/smsTemplates/SmsTemplates';
import WhatsappTemplates from '@Components/followUpTemplates/whatsappTemplates/WhatsappTemplates';

const FollowUpTemplates = () => {
	const urlParams = new URLSearchParams( useLocation().search );
	const currentTab = urlParams.get( 'tab' ) || 'email';
	return (
		<>
			<div className="p-4 md:p-8">
				<SectionWrapper>
					<div className="flex flex-col gap-4">
						<Title
							size="sm"
							tag="h1"
							title={ __(
								'Follow Up Templates',
								'woo-cart-abandonment-recovery'
							) }
							className="[&_h2]:text-gray-900"
						/>
						<Tabs activeItem={ currentTab }>
							<Tabs.Panel slug="email">
								<EmailTemplates />
							</Tabs.Panel>
							<Tabs.Panel slug="sms">
								<SmsTemplates />
							</Tabs.Panel>
							<Tabs.Panel slug="whatsapp">
								<WhatsappTemplates />
							</Tabs.Panel>
						</Tabs>
					</div>
				</SectionWrapper>
			</div>
		</>
	);
};

export default FollowUpTemplates;

