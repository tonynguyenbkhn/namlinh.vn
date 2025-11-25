import FieldDependencies from "../field-dependencies/field-dependecies";
import ConditionFieldDependencies from "../field-dependencies/condition-field-dependencies";

export default class YWCAS_Admin_Boost_Rules {

    constructor() {
        jQuery(document).on('click', '.ywcas_add_new_boost, .page-title-action', this.addNewBoostRule.bind(this));
        jQuery(document).on('click', '.ywcas-add-new-boost-condition a', this.addNewCondition.bind(this));
        jQuery(document.body).on('ywcas-new-boost-condition', this.initConditionDeps.bind(this));
        jQuery(document).on('change', 'select.ywcas-condition-for', this.handleValidOptions.bind(this));
        jQuery(document).on('select2:open', this.addCustomCssSelect2.bind(this));
        jQuery(document).on('click', '.ywcas-delete-condition, .ywcas-delete-condition i', this.removeCondition.bind(this));
        jQuery(document).on('click', '.ywcas-boost-action button', this.saveRule.bind(this));
        jQuery(document).on('click', '.yith-plugin-fw__action-button--edit-action, .yith-plugin-fw__action-button--edit-action a, .yith-plugin-fw__action-button--edit-action a i', this.editRule.bind(this));
        jQuery(document).on('change', '.type-ywcas_boost input[type="number"], .type-ywcas_boost input[type="checkbox"]', this.editInLineRule.bind(this));
        this.defaultModalConfig = {
            allowWpMenu: false,
            allowClosingWithEsc: false,
            footer: false,
            width: 735
        }
        this.modal = null;
    }

    addNewBoostRule(event) {
        event.preventDefault();
        const title = ywcas_boost_rule_params.newRuleTitle;

        this.loadBoostMetaBox(0, title);

    }

    __getTemplate(templateID, data) {
        const template = wp.template(
            templateID
        );

        return template(data);
    }

    _initFields() {
        jQuery(document).trigger('yith_fields_init');
        jQuery(document.body).trigger('yith-plugin-fw-init-radio');
        jQuery(document).find('.yith-plugin-fw-onoff-container input').trigger('change');

        new FieldDependencies('#ywcas-boost-rule-panel', '.ywcas-boost-rule-row').init();
        this.initConditionDeps(null);
    }

    addNewCondition(event) {
        event.preventDefault();
        const conditionList = jQuery(document).find('.ywcas-boost-conditions-list'),
            numElements = conditionList.find('.ywcas-boost-condition-wrapper').size(),
            newCondition = this.__getTemplate('ywcas-boost-condition', {index: numElements});

        conditionList.append(newCondition);
        jQuery(document.body).trigger('wc-enhanced-select-init');
        jQuery(document).trigger('yith_fields_init');
        jQuery(document.body).trigger('ywcas-new-boost-condition');
    }

    initConditionDeps(event) {
        const conditionList = jQuery(document).find('.ywcas-boost-conditions-list .ywcas-boost-condition-wrapper');
        const self = this;
        conditionList.each(function () {
            const singleConditionWrapper = jQuery(this);
            new ConditionFieldDependencies(
                singleConditionWrapper,
                '',
                '.ywcas-boost-condition-row'
            ).init();
            new ConditionFieldDependencies(
                singleConditionWrapper,
                '',
                '.option-element.ywcas-max-price',
                'ywcas-price-range-deps'
            ).init();
            self.filterValidOptions(singleConditionWrapper.find('select.ywcas-condition-for'), false);
        });
    }

    handleValidOptions(event) {
        this.filterValidOptions(jQuery(event.target), true);
    }

    filterValidOptions(conditionFor, handleChange) {
        const value = conditionFor.val(),
            conditionTypeWrap = conditionFor.parents('.ywcas-condition-config').find('select.ywcas-condition-type'),
            dataConfig = conditionTypeWrap.data('ywcas-valid-options-deps'),
            optionToEnable = dataConfig[value];

        if (optionToEnable.length > 0) {
            if (handleChange) {
                conditionTypeWrap.find('option').removeAttr('selected');
            }
            conditionTypeWrap.find('option').removeAttr('disabled');
            conditionTypeWrap.find('option').each(function () {
                const thisOption = jQuery(this);
                if (optionToEnable.indexOf(thisOption.val()) === -1) {
                    thisOption.attr('disabled', 'disabled');
                }
            });
            if (handleChange) {
                conditionTypeWrap.find('option:not([disabled]):first').attr('selected', 'selected');
                conditionTypeWrap.selectWoo('destroy').selectWoo().trigger('change');
            }
        }
    }

    addCustomCssSelect2(event) {
        if (jQuery(event.target).hasClass('ywcas-condition-type')) {
            jQuery('.select2-results').closest('.select2-container').addClass('ywcas-hidden-option');
        }
    }

    removeCondition(event) {
        event.preventDefault();
        jQuery(event.target).parents('.ywcas-boost-condition-wrapper').remove();
    }

    saveRule(event) {
        event.preventDefault();
        const self = this,
            form = jQuery(event.target.closest('form')),
            formData = new FormData(),
            block_params = {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                ignoreIfBlocked: true
            };

        if (!self.checkRequiredFields(form)) {
            return;
        }
        jQuery.each(form.serializeArray(), function (i, field) {
            formData.append(field.name, field.value);
        });

        formData.append('security', ywcas_boost_rule_params.saveRuleNonce);
        formData.append('action', 'yith_wcas_save_boost_rule');

        jQuery.ajax({
            url: ywcas_boost_rule_params.ajaxurl,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            type: 'POST',
            beforeSend: function () {
                form.block(block_params);
            },
            success: function (response) {
                form.unblock();
                location.reload();
            },
        });
    }

    checkRequiredFields(form) {
        const required_fields = form.find('.yith-plugin-fw--required'),
            validate_span = jQuery('<span class="validate-required">');
        let row = false,
            canSend = true;
        validate_span.html(ywcas_boost_rule_params.requiredError);
        required_fields.each(
            function () {

                const current_row = jQuery(this);

                if (current_row.is(':visible')) {
                    const select = current_row.find('select');

                    if (select.length) {
                        const selected = select.find(':selected');
                        if (selected.length === 0) {
                            canSend = false;
                            if (!row) {
                                row = current_row;
                            }
                            current_row.addClass('ywcas_required_check');
                            if (!current_row.find('.validate-required').length) {
                                current_row.find('.ywcas-boost-rule-desc').before(validate_span);
                            }

                        }
                    } else {
                        const input = current_row.find('input');
                        if ('' === input.val()) {
                            canSend = false;
                            if (!row) {
                                row = current_row;
                            }
                            current_row.addClass('ywcas_required_check');
                            if (!current_row.find('.validate-required').length) {
                                current_row.find('.ywcas-boost-rule-desc').before(validate_span);
                            }

                        }
                    }
                }
            }
        );

        if (canSend) {
            const conditions_row = form.find('.ywcas-boost-condition-wrapper .ywcas-boost-condition-row');

            conditions_row.each(
                function () {
                    const condition_row = jQuery(this)
                    if (condition_row.is(':visible')) {
                        const select = condition_row.find('select');

                        if (select.length) {
                            const selected = select.find(':selected');
                            if (selected.length === 0) {
                                canSend = false;
                                if (!row) {
                                    row = condition_row;
                                }
                                condition_row.addClass('ywcas_required_check');
                                if (!condition_row.find('.validate-required').length) {
                                    condition_row.find('.yith-plugin-fw-ajax-terms-field-wrapper').after(validate_span);
                                }

                            }
                        }
                    }
                }
            )
        }
        if (!canSend) {

            const selects = form.find('.yith-plugin-fw--required.ywcas_required_check select'),
                inputs = form.find('.yith-plugin-fw--required.ywcas_required_check input'),
            conditionSelects = form.find('.yith-plugin-fw--required .ywcas-boost-condition-wrapper .ywcas-boost-condition-row.ywcas_required_check select');

            selects.on(
                'select2:open',
                function (e) {
                    jQuery(this).parents('.yith-plugin-fw--required').removeClass('ywcas_required_check');
                    jQuery(this).parents('.yith-plugin-fw--required').find('.validate-required').remove();
                    e.stopImmediatePropagation();
                }
            );
            conditionSelects.on(
                'select2:open',
                function (e) {
                    jQuery(this).parents('.ywcas-boost-condition-row').removeClass('ywcas_required_check');
                    jQuery(this).parents('.ywcas-boost-condition-row').find('.validate-required').remove();
                    e.stopImmediatePropagation();
                }
            );
            inputs.on('change', function (e) {
                e.stopImmediatePropagation();
                jQuery(this).parents('.yith-plugin-fw--required').removeClass('ywcas_required_check');
                jQuery(this).parents('.yith-plugin-fw--required').find('.validate-required').remove();
            });
        }

        return canSend;
    }

    loadBoostMetaBox(boostID, title) {
        const self = this,
            formData = new FormData(),
            block_params = {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                ignoreIfBlocked: true
            },
            container = jQuery('.ywcas_boost_rules_table');

        formData.append('action', 'yith_wcas_load_boost_rule');
        formData.append('security', ywcas_boost_rule_params.loadBoostTemplateNonce);
        formData.append('boostRuleID', boostID);

        jQuery.ajax({
            url: ywcas_boost_rule_params.ajaxurl,
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            type: 'POST',
            beforeSend: function () {
                container.block(block_params);
            },
            success: function (response) {
                container.unblock();
                if (response?.data?.popup) {
                    const postLock = jQuery(response.data.popup).find('#active_post_lock'),
                        postID = jQuery(response.data.popup).find('#post_ID').val(),
                        nonce = jQuery(response.data.popup).find('#_wpnonce').val();
                    self.modal = yith.ui.modal({
                        ...self.defaultModalConfig,
                        title,
                        content: response.data.popup,
                        onClose: function () {

                            if (postLock.length) {
                                var data = new FormData();
                                data.append('action', 'wp-remove-post-lock');
                                data.append('_wpnonce', nonce);
                                data.append('post_ID', postID);
                                data.append('active_post_lock', postLock.val());

                                jQuery.ajax({
                                    dataType: 'json',
                                    contentType: false,
                                    processData: false,
                                    type: 'POST',
                                    data: data,
                                    url: ywcas_boost_rule_params.ajaxurl
                                });
                            }
                        }
                    });
                    self._initFields();
                }
            },
            error: function () {
                console.log('Error');
            }
        });
    }

    editRule(event) {
        event.preventDefault();
        event.stopPropagation();
        const self = this,
            row = jQuery(event.target).parents('tr'),
            postID = row.find('th.check-column input').val(),
            title = ywcas_boost_rule_params.editRuleTitle;

        this.loadBoostMetaBox(postID, title);
    }

    editInLineRule(event) {
        setTimeout(() => {
            const target = jQuery(event.target),
                row = target.parents('tr'),
                boost = row.find('.ywcas-boost-value').val(),
                active = row.find('.ywcas-boost-active input').is(':checked') ? 'yes' : 'no',
                postID = row.find('th.check-column input').val(),
                formData = new FormData();
            formData.append('security', ywcas_boost_rule_params.editInLineRuleNonce);
            formData.append('action', 'yith_wcas_edit_in_line_boost_rule');
            formData.append('boost', boost);
            formData.append('active', active);
            formData.append('id', postID);

            jQuery.ajax({
                url: ywcas_boost_rule_params.ajaxurl,
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                type: 'POST',
                success: function (response) {
                },
            });
        }, 500);
    }
}