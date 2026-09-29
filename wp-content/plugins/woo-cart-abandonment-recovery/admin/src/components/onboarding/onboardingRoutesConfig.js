import AddOns from './screens/AddOns';
import FollowUpChannels from './screens/FollowUpChannels';
import OnboardingComplete from './screens/OnboardingComplete';
import RecoverySettings from './screens/RecoverySettings';
import UserDetails from './screens/UserDetails';
import Welcome from './screens/Welcome';

const ONBOARDING_ROUTES_CONFIG = [
	{
		url: 'welcome',
		index: true,
		component: <Welcome />,
	},
	{
		url: 'recovery-settings',
		component: <RecoverySettings />,
	},
	{
		url: 'follow-up-channels',
		component: <FollowUpChannels />,
	},
	{
		url: 'report-email',
		component: <UserDetails />,
	},
	{
		url: 'addons',
		component: <AddOns />,
	},
	{
		url: 'finish',
		component: <OnboardingComplete />,
	},
];
export default ONBOARDING_ROUTES_CONFIG;
