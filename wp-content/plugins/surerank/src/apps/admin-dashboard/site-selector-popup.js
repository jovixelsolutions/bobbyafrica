import { useState } from '@wordpress/element';
import ModalWrapper from '@AdminComponents/modal-wrapper';
import { ConnectMode, SelectMode } from './site-selector-popup-content';

const SiteSelectorPopup = () => {
	const [ forceSelectMode, setForceSelectMode ] = useState( false );
	const mode = forceSelectMode ? 'select' : 'connect';

	if ( mode === 'connect' ) {
		return (
			<ModalWrapper
				maxWidth="max-w-[550px]"
				isOpen={ true }
				className="z-[1]"
			>
				<ConnectMode setForceSelectMode={ setForceSelectMode } />
			</ModalWrapper>
		);
	}

	return (
		<ModalWrapper
			maxWidth="max-w-[480px]"
			isOpen={ true }
			className="z-[1]"
		>
			<SelectMode
				forceSelectMode={ forceSelectMode }
				setForceSelectMode={ setForceSelectMode }
			/>
		</ModalWrapper>
	);
};

export default SiteSelectorPopup;
