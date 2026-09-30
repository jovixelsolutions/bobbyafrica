import { Dialog, Button } from '@bsf/force-ui';
import { __ } from '@wordpress/i18n';
import RulesRepeater from '@Components/RuleEngine/RulesRepeater';

const ConditionalRulesModal = ( {
	open,
	onOpenChange,
	value,
	onChange,
	isPro = false,
	title,
} ) => {
	const modalTitle =
		title || __( 'Dynamic Conditions', 'woo-cart-abandonment-recovery' );

	return (
		<Dialog
			open={ open }
			setOpen={ onOpenChange }
			design="simple"
			exitOnEsc
			scrollLock
			className="z-[100001]"
		>
			<Dialog.Backdrop />
			<Dialog.Panel className="gap-0 m-5 sm:m-0 sm:w-3/5 max-w-3xl">
				<Dialog.Header className="p-5 border-0 border-b border-solid border-gray-200">
					<div className="flex items-center justify-between">
						<Dialog.Title className="text-lg font-semibold">
							{ modalTitle }
						</Dialog.Title>
						<Dialog.CloseButton
							className="[&>svg]:size-5"
							type="button"
						/>
					</div>
				</Dialog.Header>
				<Dialog.Body className="px-5 max-h-[60vh] overflow-y-auto">
					<RulesRepeater
						value={ value }
						onChange={ onChange }
						isPro={ isPro }
					/>
				</Dialog.Body>
				<Dialog.Footer className="p-5 border-0 border-t border-solid border-gray-200">
					<Button
						variant="primary"
						size="sm"
						onClick={ () => onOpenChange( false ) }
					>
						{ __( 'Done', 'woo-cart-abandonment-recovery' ) }
					</Button>
				</Dialog.Footer>
			</Dialog.Panel>
		</Dialog>
	);
};

export default ConditionalRulesModal;
