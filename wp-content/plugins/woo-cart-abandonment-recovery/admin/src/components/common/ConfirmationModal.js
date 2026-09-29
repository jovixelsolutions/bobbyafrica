import { Dialog, Button, Title } from '@bsf/force-ui';
import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { __ } from '@wordpress/i18n';

const ConfirmationModal = ( {
	isOpen,
	onClose,
	onConfirm,
	title = __( 'Confirm Delete', 'woo-cart-abandonment-recovery' ),
	message = __(
		'Are you sure you want to delete this item? This action cannot be undone.',
		'woo-cart-abandonment-recovery'
	),
	confirmText = __( 'Delete', 'woo-cart-abandonment-recovery' ),
	cancelText = __( 'Cancel', 'woo-cart-abandonment-recovery' ),
	isLoading = false,
} ) => {
	const handleConfirm = () => {
		onConfirm();
	};

	return (
		<Dialog
			open={ isOpen }
			setOpen={ onClose }
			size="sm"
			className="max-w-md"
		>
			<Dialog.Backdrop />
			<Dialog.Panel>
				<div className="p-6">
					<div className="flex items-center gap-4 mb-4">
						<div className="flex-shrink-0">
							<ExclamationTriangleIcon className="h-8 w-8 text-red-500" />
						</div>
						<Title
							size="sm"
							tag="h3"
							title={ title }
							className="[&_h2]:text-gray-900"
						/>
					</div>

					<div className="mb-6">
						<p className="text-gray-600 text-sm font-normal">
							{ message }
						</p>
					</div>

					<div className="flex gap-3 justify-end">
						<Button
							variant="primary"
							onClick={ handleConfirm }
							disabled={ isLoading }
						>
							{ confirmText }
						</Button>
						<Button
							variant="outline"
							className="bg-primary-25 text-primary-600 outline-primary-300 hover:bg-primary-25 hover:outline-primary-300"
							onClick={ onClose }
							disabled={ isLoading }
						>
							{ cancelText }
						</Button>
					</div>
				</div>
			</Dialog.Panel>
		</Dialog>
	);
};

export default ConfirmationModal;
