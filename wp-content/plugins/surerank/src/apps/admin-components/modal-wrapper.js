import { cn } from '@/functions/utils';
import { Container } from '@bsf/force-ui';

const ModalWrapper = ( {
	isOpen = true,
	children,
	className = '',
	maxWidth = 'max-w-[464px]',
	...props
} ) => {
	if ( ! isOpen ) {
		return null;
	}

	return (
		<>
			{ /* Modal content */ }
			<Container
				className={ cn(
					'absolute top-[30%] inset-x-0 z-50 mx-auto -translate-y-1/2 w-full',
					maxWidth,
					className
				) }
				justify="center"
				align="center"
				{ ...props }
			>
				{ children }
			</Container>
		</>
	);
};

export default ModalWrapper;
