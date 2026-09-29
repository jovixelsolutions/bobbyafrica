import React from 'react';
import RenderFields from '@Components/RenderFields';
import ConditionsHelper from '@Utils/conditions';

const EmailTemplatesDrawerBody = ( { formState, handleChange, errors } ) => {
	const emailFields = cart_abandonment_admin?.settings_fields?.email_fields;
	const conditions = new ConditionsHelper();

	return (
		<div className="p-1 flex flex-col gap-1 bg-light-background rounded-lg">
			{ emailFields &&
				Object.entries( emailFields )
					?.sort( ( a, b ) => a[ 1 ]?.priority - b[ 1 ]?.priority )
					?.map( ( [ field, data ] ) => {
						const isFieldActive = conditions.isActiveControl(
							data,
							formState
						);

						// Don't render wrapper div if field is not active
						if ( ! isFieldActive ) {
							return null;
						}

						return (
							<div
								key={ field }
								className="px-4 rounded-md bg-white shadow-sm"
							>
								<RenderFields
									data={ data }
									value={
										[ 'time', 'test_email' ].includes(
											data?.type
										)
											? formState
											: formState[ field ] || ''
									}
									isActive={ true }
									handleChange={ handleChange }
									errors={ errors }
									autoSave={ false }
								/>
							</div>
						);
					} ) }
		</div>
	);
};

export default EmailTemplatesDrawerBody;
