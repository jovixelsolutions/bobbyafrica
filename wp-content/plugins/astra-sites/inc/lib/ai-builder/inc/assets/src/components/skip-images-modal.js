import { memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Modal from './modal';
import { ExclamationTriangleColorfulIcon } from '../ui/icons';
import LoadingSpinner from './loading-spinner';
import Button from './button';

const SkipImagesModal = ( {
	open,
	setOpen,
	onConfirmSkip,
	loadingNextStep,
} ) => {
	const handleNo = () => {
		setOpen( false );
	};

	const handleYes = () => {
		if ( typeof onConfirmSkip !== 'function' ) {
			return;
		}
		onConfirmSkip();
	};

	return (
		<Modal
			open={ open }
			setOpen={ setOpen }
			width={ 480 }
			height="280"
			overflowHidden={ false }
			className={ 'px-8 pt-8 pb-8 font-sans' }
		>
			<div>
				<div className="flex items-center gap-3">
					<ExclamationTriangleColorfulIcon className="w-8 h-8 text-accent-st" />
					<div className="font-bold text-2xl leading-8 text-zip-app-heading">
						{ __( 'Skip Images?', 'ai-builder' ) }
					</div>
				</div>

				<div className="mt-5">
					<div className="text-zip-body-text text-base font-normal leading-6">
						{ __(
							'We will choose placeholder images ourselves!',
							'ai-builder'
						) }
					</div>
					<div className="flex items-center gap-4 sm:gap-6 justify-center mt-8 flex-col xs:flex-row">
						<Button
							variant="white"
							size="medium"
							className="min-w-[206px] text-sm font-semibold leading-5 w-full xs:w-auto"
							onClick={ handleNo }
							disabled={ loadingNextStep }
						>
							{ __( "No, I'll Select", 'ai-builder' ) }
						</Button>
						<Button
							variant="primary"
							size="medium"
							className="min-w-[206px] text-sm font-semibold leading-5 px-5 w-full xs:w-auto"
							onClick={ handleYes }
							disabled={ loadingNextStep }
						>
							{ loadingNextStep ? (
								<span className="inset-0 flex items-center justify-center">
									<LoadingSpinner />
								</span>
							) : (
								__( 'Yes, Skip', 'ai-builder' )
							) }
						</Button>
					</div>
				</div>
			</div>
		</Modal>
	);
};

export default memo( SkipImagesModal );
