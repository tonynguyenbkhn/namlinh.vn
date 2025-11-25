/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/admin/src/boost-rule/boost-rule-table.js":
/*!************************************************************!*\
  !*** ./assets/js/admin/src/boost-rule/boost-rule-table.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ YWCAS_Admin_Boost_Rules)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _field_dependencies_field_dependecies__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../field-dependencies/field-dependecies */ "./assets/js/admin/src/field-dependencies/field-dependecies.js");
/* harmony import */ var _field_dependencies_condition_field_dependencies__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../field-dependencies/condition-field-dependencies */ "./assets/js/admin/src/field-dependencies/condition-field-dependencies.js");



function ownKeys(e, r) {
  var t = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    r && (o = o.filter(function (r) {
      return Object.getOwnPropertyDescriptor(e, r).enumerable;
    })), t.push.apply(t, o);
  }
  return t;
}
function _objectSpread(e) {
  for (var r = 1; r < arguments.length; r++) {
    var t = null != arguments[r] ? arguments[r] : {};
    r % 2 ? ownKeys(Object(t), !0).forEach(function (r) {
      (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__["default"])(e, r, t[r]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
      Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
    });
  }
  return e;
}


var YWCAS_Admin_Boost_Rules = /*#__PURE__*/function () {
  function YWCAS_Admin_Boost_Rules() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, YWCAS_Admin_Boost_Rules);
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
    };
    this.modal = null;
  }
  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(YWCAS_Admin_Boost_Rules, [{
    key: "addNewBoostRule",
    value: function addNewBoostRule(event) {
      event.preventDefault();
      var title = ywcas_boost_rule_params.newRuleTitle;
      this.loadBoostMetaBox(0, title);
    }
  }, {
    key: "__getTemplate",
    value: function __getTemplate(templateID, data) {
      var template = wp.template(templateID);
      return template(data);
    }
  }, {
    key: "_initFields",
    value: function _initFields() {
      jQuery(document).trigger('yith_fields_init');
      jQuery(document.body).trigger('yith-plugin-fw-init-radio');
      jQuery(document).find('.yith-plugin-fw-onoff-container input').trigger('change');
      new _field_dependencies_field_dependecies__WEBPACK_IMPORTED_MODULE_3__["default"]('#ywcas-boost-rule-panel', '.ywcas-boost-rule-row').init();
      this.initConditionDeps(null);
    }
  }, {
    key: "addNewCondition",
    value: function addNewCondition(event) {
      event.preventDefault();
      var conditionList = jQuery(document).find('.ywcas-boost-conditions-list'),
        numElements = conditionList.find('.ywcas-boost-condition-wrapper').size(),
        newCondition = this.__getTemplate('ywcas-boost-condition', {
          index: numElements
        });
      conditionList.append(newCondition);
      jQuery(document.body).trigger('wc-enhanced-select-init');
      jQuery(document).trigger('yith_fields_init');
      jQuery(document.body).trigger('ywcas-new-boost-condition');
    }
  }, {
    key: "initConditionDeps",
    value: function initConditionDeps(event) {
      var conditionList = jQuery(document).find('.ywcas-boost-conditions-list .ywcas-boost-condition-wrapper');
      var self = this;
      conditionList.each(function () {
        var singleConditionWrapper = jQuery(this);
        new _field_dependencies_condition_field_dependencies__WEBPACK_IMPORTED_MODULE_4__["default"](singleConditionWrapper, '', '.ywcas-boost-condition-row').init();
        new _field_dependencies_condition_field_dependencies__WEBPACK_IMPORTED_MODULE_4__["default"](singleConditionWrapper, '', '.option-element.ywcas-max-price', 'ywcas-price-range-deps').init();
        self.filterValidOptions(singleConditionWrapper.find('select.ywcas-condition-for'), false);
      });
    }
  }, {
    key: "handleValidOptions",
    value: function handleValidOptions(event) {
      this.filterValidOptions(jQuery(event.target), true);
    }
  }, {
    key: "filterValidOptions",
    value: function filterValidOptions(conditionFor, handleChange) {
      var value = conditionFor.val(),
        conditionTypeWrap = conditionFor.parents('.ywcas-condition-config').find('select.ywcas-condition-type'),
        dataConfig = conditionTypeWrap.data('ywcas-valid-options-deps'),
        optionToEnable = dataConfig[value];
      if (optionToEnable.length > 0) {
        if (handleChange) {
          conditionTypeWrap.find('option').removeAttr('selected');
        }
        conditionTypeWrap.find('option').removeAttr('disabled');
        conditionTypeWrap.find('option').each(function () {
          var thisOption = jQuery(this);
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
  }, {
    key: "addCustomCssSelect2",
    value: function addCustomCssSelect2(event) {
      if (jQuery(event.target).hasClass('ywcas-condition-type')) {
        jQuery('.select2-results').closest('.select2-container').addClass('ywcas-hidden-option');
      }
    }
  }, {
    key: "removeCondition",
    value: function removeCondition(event) {
      event.preventDefault();
      jQuery(event.target).parents('.ywcas-boost-condition-wrapper').remove();
    }
  }, {
    key: "saveRule",
    value: function saveRule(event) {
      event.preventDefault();
      var self = this,
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
        beforeSend: function beforeSend() {
          form.block(block_params);
        },
        success: function success(response) {
          form.unblock();
          location.reload();
        }
      });
    }
  }, {
    key: "checkRequiredFields",
    value: function checkRequiredFields(form) {
      var required_fields = form.find('.yith-plugin-fw--required'),
        validate_span = jQuery('<span class="validate-required">');
      var row = false,
        canSend = true;
      validate_span.html(ywcas_boost_rule_params.requiredError);
      required_fields.each(function () {
        var current_row = jQuery(this);
        if (current_row.is(':visible')) {
          var select = current_row.find('select');
          if (select.length) {
            var selected = select.find(':selected');
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
            var input = current_row.find('input');
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
      });
      if (canSend) {
        var conditions_row = form.find('.ywcas-boost-condition-wrapper .ywcas-boost-condition-row');
        conditions_row.each(function () {
          var condition_row = jQuery(this);
          if (condition_row.is(':visible')) {
            var select = condition_row.find('select');
            if (select.length) {
              var selected = select.find(':selected');
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
        });
      }
      if (!canSend) {
        var selects = form.find('.yith-plugin-fw--required.ywcas_required_check select'),
          inputs = form.find('.yith-plugin-fw--required.ywcas_required_check input'),
          conditionSelects = form.find('.yith-plugin-fw--required .ywcas-boost-condition-wrapper .ywcas-boost-condition-row.ywcas_required_check select');
        selects.on('select2:open', function (e) {
          jQuery(this).parents('.yith-plugin-fw--required').removeClass('ywcas_required_check');
          jQuery(this).parents('.yith-plugin-fw--required').find('.validate-required').remove();
          e.stopImmediatePropagation();
        });
        conditionSelects.on('select2:open', function (e) {
          jQuery(this).parents('.ywcas-boost-condition-row').removeClass('ywcas_required_check');
          jQuery(this).parents('.ywcas-boost-condition-row').find('.validate-required').remove();
          e.stopImmediatePropagation();
        });
        inputs.on('change', function (e) {
          e.stopImmediatePropagation();
          jQuery(this).parents('.yith-plugin-fw--required').removeClass('ywcas_required_check');
          jQuery(this).parents('.yith-plugin-fw--required').find('.validate-required').remove();
        });
      }
      return canSend;
    }
  }, {
    key: "loadBoostMetaBox",
    value: function loadBoostMetaBox(boostID, title) {
      var self = this,
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
        beforeSend: function beforeSend() {
          container.block(block_params);
        },
        success: function success(response) {
          var _response$data;
          container.unblock();
          if (response !== null && response !== void 0 && (_response$data = response.data) !== null && _response$data !== void 0 && _response$data.popup) {
            var postLock = jQuery(response.data.popup).find('#active_post_lock'),
              postID = jQuery(response.data.popup).find('#post_ID').val(),
              nonce = jQuery(response.data.popup).find('#_wpnonce').val();
            self.modal = yith.ui.modal(_objectSpread(_objectSpread({}, self.defaultModalConfig), {}, {
              title: title,
              content: response.data.popup,
              onClose: function onClose() {
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
            }));
            self._initFields();
          }
        },
        error: function error() {
          console.log('Error');
        }
      });
    }
  }, {
    key: "editRule",
    value: function editRule(event) {
      event.preventDefault();
      event.stopPropagation();
      var self = this,
        row = jQuery(event.target).parents('tr'),
        postID = row.find('th.check-column input').val(),
        title = ywcas_boost_rule_params.editRuleTitle;
      this.loadBoostMetaBox(postID, title);
    }
  }, {
    key: "editInLineRule",
    value: function editInLineRule(event) {
      setTimeout(function () {
        var target = jQuery(event.target),
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
          success: function success(response) {}
        });
      }, 500);
    }
  }]);
  return YWCAS_Admin_Boost_Rules;
}();


/***/ }),

/***/ "./assets/js/admin/src/field-dependencies/condition-field-dependencies.js":
/*!********************************************************************************!*\
  !*** ./assets/js/admin/src/field-dependencies/condition-field-dependencies.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ConditionFieldDependencies)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/possibleConstructorReturn */ "./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js");
/* harmony import */ var _babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/getPrototypeOf */ "./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js");
/* harmony import */ var _babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @babel/runtime/helpers/inherits */ "./node_modules/@babel/runtime/helpers/esm/inherits.js");
/* harmony import */ var _field_dependecies__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./field-dependecies */ "./assets/js/admin/src/field-dependencies/field-dependecies.js");







function _callSuper(t, o, e) {
  return o = (0,_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__["default"])(o), (0,_babel_runtime_helpers_possibleConstructorReturn__WEBPACK_IMPORTED_MODULE_2__["default"])(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], (0,_babel_runtime_helpers_getPrototypeOf__WEBPACK_IMPORTED_MODULE_3__["default"])(t).constructor) : o.apply(t, e));
}
function _isNativeReflectConstruct() {
  try {
    var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
  } catch (t) {}
  return (_isNativeReflectConstruct = function _isNativeReflectConstruct() {
    return !!t;
  })();
}

var ConditionFieldDependencies = /*#__PURE__*/function (_FieldDependencies) {
  (0,_babel_runtime_helpers_inherits__WEBPACK_IMPORTED_MODULE_4__["default"])(ConditionFieldDependencies, _FieldDependencies);
  function ConditionFieldDependencies(toggle, containerClass, parentClassField) {
    var _this;
    var depsName = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'ywcas-conditions-deps';
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, ConditionFieldDependencies);
    _this = _callSuper(this, ConditionFieldDependencies, [containerClass, parentClassField]);
    _this.toggleElement = toggle;
    _this.depsName = depsName;
    return _this;
  }
  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(ConditionFieldDependencies, [{
    key: "_getFields",
    value: function _getFields() {
      return this.toggleElement.find('[data-' + this.depsName + ']');
    }
  }, {
    key: "getDeps",
    value: function getDeps(field) {
      return field.data(this.depsName);
    }
  }, {
    key: "getTargetDep",
    value: function getTargetDep(dep) {
      return this.toggleElement.find('.' + dep.id).filter('input,select');
    }
  }]);
  return ConditionFieldDependencies;
}(_field_dependecies__WEBPACK_IMPORTED_MODULE_5__["default"]);


/***/ }),

/***/ "./assets/js/admin/src/field-dependencies/field-dependecies.js":
/*!*********************************************************************!*\
  !*** ./assets/js/admin/src/field-dependencies/field-dependecies.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FieldDependencies)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");




var FieldDependencies = /*#__PURE__*/function () {
  function FieldDependencies(containerClass, parentClassField) {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, FieldDependencies);
    this.containerClass = containerClass;
    this.parentClass = parentClassField;
    this.target_deps = [];
    this.target_deps_id = [];
  }
  (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(FieldDependencies, [{
    key: "init",
    value: function init() {
      this.fields = this._getFields();
      this._initFields();
      this.handleFieldsChange();
    }
  }, {
    key: "_getFields",
    value: function _getFields() {
      return jQuery(this.containerClass).find('[data-ywcas-boost-deps]');
    }
  }, {
    key: "_initFields",
    value: function _initFields() {
      var self = this;
      this.fields.each(function () {
        var t = jQuery(this);
        self.handleField(t);
      });
      jQuery(document).trigger('ywcas-init-fields', [self]);
    }
  }, {
    key: "getTargetDep",
    value: function getTargetDep(dep) {
      return jQuery(document).find('#' + dep.id);
    }
  }, {
    key: "getDeps",
    value: function getDeps(field) {
      return field.data('ywcas-boost-deps');
    }
  }, {
    key: "handleField",
    value: function handleField(field) {
      var self = this,
        parent = field.closest(self.parentClass),
        deps = this.getDeps(field),
        show = true;
      jQuery.each(deps, function (i, dep) {
        var target_dep = self.getTargetDep(dep),
          compare = typeof dep.compare === 'undefined' ? '==' : dep.compare,
          property = typeof dep.property === 'undefined' ? false : dep.property,
          current_value;
        // it's a radio button.
        if (target_dep.hasClass('yith-plugin-fw-radio')) {
          current_value = target_dep.find('input[type="radio"]').filter(':checked').val();
        } else if (target_dep.hasClass('yith-plugin-fw-select') || target_dep.hasClass('yith-post-search') || target_dep.hasClass('wc-enhanced-select')) {
          current_value = target_dep.val();
        } else if (target_dep.hasClass('yith-plugin-fw-onoff-container')) {
          current_value = target_dep.find('input[type="checkbox"]').is(':checked') ? 'yes' : 'no';
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
            var value = dep.value.split(',');
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
  }, {
    key: "handleFieldsChange",
    value: function handleFieldsChange() {
      var self = this;
      jQuery.each(self.target_deps, function (i, field) {
        field.on('change', function () {
          self._initFields();
        });
      });
    }
  }]);
  return FieldDependencies;
}();


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js":
/*!**************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _assertThisInitialized)
/* harmony export */ });
function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }
  return self;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _classCallCheck)
/* harmony export */ });
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/createClass.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/createClass.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _createClass)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(descriptor.key), descriptor);
  }
}
function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  Object.defineProperty(Constructor, "prototype", {
    writable: false
  });
  return Constructor;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _defineProperty)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperty(obj, key, value) {
  key = (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(key);
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }
  return obj;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/getPrototypeOf.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _getPrototypeOf)
/* harmony export */ });
function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inherits.js":
/*!*************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inherits.js ***!
  \*************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _inherits)
/* harmony export */ });
/* harmony import */ var _setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPrototypeOf.js */ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }
  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  Object.defineProperty(subClass, "prototype", {
    writable: false
  });
  if (superClass) (0,_setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__["default"])(subClass, superClass);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js":
/*!******************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/possibleConstructorReturn.js ***!
  \******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _possibleConstructorReturn)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _assertThisInitialized_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./assertThisInitialized.js */ "./node_modules/@babel/runtime/helpers/esm/assertThisInitialized.js");


function _possibleConstructorReturn(self, call) {
  if (call && ((0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(call) === "object" || typeof call === "function")) {
    return call;
  } else if (call !== void 0) {
    throw new TypeError("Derived constructors may only return object or undefined");
  }
  return (0,_assertThisInitialized_js__WEBPACK_IMPORTED_MODULE_1__["default"])(self);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _setPrototypeOf)
/* harmony export */ });
function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };
  return _setPrototypeOf(o, p);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPrimitive)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");

function toPrimitive(t, r) {
  if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(t) || !t) return t;
  var e = t[Symbol.toPrimitive];
  if (void 0 !== e) {
    var i = e.call(t, r || "default");
    if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i)) return i;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return ("string" === r ? String : Number)(t);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPropertyKey)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./toPrimitive.js */ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js");


function toPropertyKey(t) {
  var i = (0,_toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__["default"])(t, "string");
  return "symbol" == (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i) ? i : String(i);
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!***********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _typeof)
/* harmony export */ });
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*************************************************!*\
  !*** ./assets/js/admin/src/boost-rule/index.js ***!
  \*************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _boost_rule_table__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./boost-rule-table */ "./assets/js/admin/src/boost-rule/boost-rule-table.js");

new _boost_rule_table__WEBPACK_IMPORTED_MODULE_0__["default"]();
})();

var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=boost-rule.js.map