'use strict';
export default class FieldDependencies {
    constructor(containerClass, parentClassField) {
        this.containerClass = containerClass;
        this.parentClass = parentClassField;
        this.target_deps = [];
        this.target_deps_id = [];
    }

    init() {
        this.fields = this._getFields();
        this._initFields();
        this.handleFieldsChange();


    }

    _getFields() {
        return jQuery(this.containerClass).find('[data-ywcas-boost-deps]');
    }

    _initFields() {
        let self = this;
        this.fields.each(function () {
            let t = jQuery(this);
            self.handleField(t);
        });
        jQuery(document).trigger('ywcas-init-fields', [self]);
    }

    getTargetDep(dep) {
        return jQuery(document).find('#' + dep.id);
    }

    getDeps(field) {
        return field.data('ywcas-boost-deps');
    }

    handleField(field) {
        let self = this,
            parent = field.closest(self.parentClass),
            deps = this.getDeps(field),
            show = true;
        jQuery.each(deps, function (i, dep) {
            let target_dep = self.getTargetDep(dep),
                compare =
                    typeof dep.compare === 'undefined' ? '==' : dep.compare,
                property =
                    typeof dep.property === 'undefined' ? false : dep.property,
                current_value;
            // it's a radio button.
            if (target_dep.hasClass('yith-plugin-fw-radio')) {
                current_value = target_dep
                    .find('input[type="radio"]')
                    .filter(':checked')
                    .val();
            } else if (
                target_dep.hasClass('yith-plugin-fw-select') ||
                target_dep.hasClass('yith-post-search') ||
                target_dep.hasClass('wc-enhanced-select')
            ) {
                current_value = target_dep.val();
            } else if (
                target_dep.hasClass('yith-plugin-fw-onoff-container')
            ) {
                current_value = target_dep
                    .find('input[type="checkbox"]')
                    .is(':checked')
                    ? 'yes'
                    : 'no';
            } else {
                current_value = target_dep.is(':checked') ? 'yes' : 'no';
            }
            if (self.target_deps_id.indexOf(dep.id) < 0) {

                self.target_deps.push(target_dep);
                self.target_deps_id.push(dep.id);
            }

            if (show) {
                if (property) {
                    if (property === 'length') {
                        switch (compare) {
                            case '==':
                            case '===':
                                show = current_value.length == dep.value;
                                break;
                            case '>':
                                show = current_value.length > dep.value;
                                break;
                            case '<':
                                show = current_value.length < dep.value;
                                break;
                            case '>=':
                                show = current_value.length >= dep.value;
                                break;
                            case '<=':
                                show = current_value.length <= dep.value;
                                break;
                        }
                    }
                } else {
                    let value = dep.value.split(',');
                    switch (compare) {
                        case '==':
                        case '===':
                            show = value.indexOf(current_value) >= 0;
                            break;
                        case '!=':
                        case '!==':
                            show = value.indexOf(current_value) < 0;
                            break;
                    }
                }
            }
        });

        if (show) {
            parent.show();
        } else {
            parent.hide();
        }
    }

    handleFieldsChange() {
        let self = this;

        jQuery.each(self.target_deps, function (i, field) {

            field.on('change', function () {
                self._initFields();
            });
        });
    }
}
