function ConditionsHelper() {
	const self = this;

	this.compare = function ( leftValue, rightValue, operator ) {
		switch ( operator ) {
			case '==':
				return leftValue == rightValue; // eslint-disable-line eqeqeq
			case '!=':
				return leftValue != rightValue; // eslint-disable-line eqeqeq
			case '!==':
				return leftValue !== rightValue;
			case 'in':
				return -1 !== rightValue.indexOf( leftValue );
			case '!in':
				return -1 === rightValue.indexOf( leftValue );
			case 'contains':
				return -1 !== leftValue.indexOf( rightValue );
			case '!contains':
				return -1 === leftValue.indexOf( rightValue );
			case '<':
				return leftValue < rightValue;
			case '<=':
				return leftValue <= rightValue;
			case '>':
				return leftValue > rightValue;
			case '>=':
				return leftValue >= rightValue;
			default:
				return leftValue === rightValue;
		}
	};

	this.check = function ( conditions, options ) {
		const isOrCondition = 'or' === conditions.relation;
		let conditionsResult = ! isOrCondition;

		conditions.fields.forEach( function ( field ) {
			let comparisonResult;

			if ( field.fields ) {
				comparisonResult = self.check( field, options );
			} else {
				comparisonResult = self.compare(
					options[ field.name ],
					field.value,
					field.operator
				);
			}

			if ( isOrCondition ) {
				if ( comparisonResult ) {
					conditionsResult = true;
				}
				return ! comparisonResult;
			}

			if ( ! comparisonResult ) {
				conditionsResult = false;
				return false;
			}
			return '';
		} );

		return conditionsResult;
	};

	this.isActiveControl = function ( control, options ) {
		const conditions = control?.conditions ? control.conditions : false;

		if ( conditions && ! self.check( conditions, options ) ) {
			return false;
		}

		return true;
	};
}

export default ConditionsHelper;
