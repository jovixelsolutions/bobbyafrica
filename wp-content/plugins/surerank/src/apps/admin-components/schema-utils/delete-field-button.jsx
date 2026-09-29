import { __ } from '@wordpress/i18n';
import { Button } from '@bsf/force-ui';
import { Trash2 } from 'lucide-react';

/**
 * Renders a delete button for schema fields
 *
 * @param {Object}   props           - Component props
 * @param {Function} props.onDelete  - Callback function when delete is clicked
 * @param {string}   props.className - Optional additional CSS classes
 * @return {JSX.Element} The DeleteFieldButton component
 */
export const DeleteFieldButton = ( { onDelete, className = '' } ) => {
	return (
		<Button
			variant="ghost"
			size="xs"
			onClick={ onDelete }
			className={ `text-text-tertiary hover:text-status-error ${ className }` }
			title={ __( 'Delete Field', 'surerank' ) }
			icon={ <Trash2 className="size-4" /> }
		>
			{ __( 'Delete', 'surerank' ) }
		</Button>
	);
};
