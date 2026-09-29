import { BrowserRouter } from 'react-router-dom';

import Routes from '@Admin/Routes';

const ViewContainer = () => {
	return (
		<BrowserRouter>
			<Routes />
		</BrowserRouter>
	);
};

export default ViewContainer;

