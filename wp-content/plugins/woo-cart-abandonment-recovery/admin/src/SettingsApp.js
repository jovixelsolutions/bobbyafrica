import { createRoot } from 'react-dom/client';
import ViewContainer from '@Admin/ViewContainer';
import { reducer, StateProvider, initialState } from '@Store';

import '@Admin/SettingsApp.css';

const container = document.getElementById( 'wcar-admin-app' );
if ( container ) {
	const root = createRoot( container );
	root.render(
		<StateProvider reducer={ reducer } initialState={ initialState }>
			<ViewContainer />
		</StateProvider>
	);
}
