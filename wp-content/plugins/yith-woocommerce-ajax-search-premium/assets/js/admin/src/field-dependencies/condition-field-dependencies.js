'use strict';
import FieldDependencies from './field-dependecies';

export default class ConditionFieldDependencies extends FieldDependencies {
	constructor( toggle, containerClass, parentClassField, depsName = 'ywcas-conditions-deps' ) {
		super( containerClass, parentClassField );
		this.toggleElement = toggle;
		this.depsName = depsName;
	}
	_getFields() {
		return this.toggleElement.find( '[data-'+this.depsName+']' );
	}
	getDeps( field ) {
		return field.data( this.depsName );
	}
	getTargetDep( dep ) {
		return this.toggleElement.find( '.' + dep.id ).filter('input,select');
	}
}
