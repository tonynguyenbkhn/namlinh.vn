/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************************!*\
  !*** ./assets/js/admin/src/boost-product.js ***!
  \**********************************************/
var YWCAS_AdminBoostProduct = function YWCAS_AdminBoostProduct() {
  var $ = jQuery;
  var ajaxCall = false;
  var currentTerm = '';
  var modal = false;
  var blockParams = {
    message: null,
    overlayCSS: {
      background: '#fff',
      opacity: 0.7
    },
    ignoreIfBlocked: true
  };
  var init = function init() {
    $(document).on('click', '.ywcas-boost-product-button', openModal);
    $(document).on('click', '.ywcas-boost-product-modal-content #ywcas-add-boost-product', addProducts);
    $(document).on('click', '.yith-plugin-fw__action-button--delete-action', deleteBoost);
    $(document).on('click', 'input[name="ywcas_boost"]', updateBoost);
    $(document).on('click', '#doaction', bulkActionConfirm);
    $(document).on('keydown', '.ywcas-boost-product-modal-content #ywcas-boost-product-search', searchProducts);
    $(document).on('click', '.ywcas-boost-detail .notice-dismiss', hideNotice);
    var isEmpty = $(document).find('.yith-plugin-fw__list-table-blank-state ');
    if (isEmpty.length > 0) {
      $(document).find('#yith_wcas_panel_boost-boost-product #yith-plugin-fw-panel-custom-tab-boost-boost-boost-product').removeClass('list-full');
    } else {
      $(document).find('#yith_wcas_panel_boost-boost-product #yith-plugin-fw-panel-custom-tab-boost-boost-boost-product').addClass('list-full');
    }
    showAddProductButton();
  };
  var showNotice = function showNotice() {
    $(document).find('.ywcas-boost-detail #message').addClass('show');
  };
  var hideNotice = function hideNotice() {
    $(document).find('.ywcas-boost-detail #message').removeClass('show');
  };
  var openModal = function openModal(ev) {
    ev.preventDefault();
    modal = yith.ui.modal({
      title: ywcas_boost_product_params.modalTitle,
      content: $('#ywcas-modal-content').html(),
      width: 560,
      allowWpMenu: false,
      closeWhenClickingOnOverlay: true,
      classes: {
        content: 'ywcas-boost-product-modal-content'
      }
    });
    init();
  };
  var searchProducts = function searchProducts() {
    var term = $('.ywcas-boost-product-modal-content #ywcas-boost-product-search').val();
    if (currentTerm === term || term.length < 2) {
      return;
    }
    if (false !== ajaxCall) {
      ajaxCall.abort();
    }
    var wrapper = $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results');
    currentTerm = term;
    ajaxCall = $.ajax({
      url: ywcas_boost_product_params.ajaxurl,
      data: {
        security: ywcas_boost_product_params.searchProductNonce,
        action: 'ywcas_search_product',
        term: term
      },
      type: 'POST',
      beforeSend: function beforeSend() {
        wrapper.block(blockParams);
      },
      success: function success(response) {
        if (response.success) {
          var _response$data;
          var responseModal = jQuery((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.content);
          var resultBox = responseModal.find('.ywcas-boost-product-search-results');
          $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results').html(resultBox.html());
        }
      },
      complete: function complete() {
        wrapper.unblock();
      }
    });
  };
  var addProducts = function addProducts(e) {
    e.preventDefault();
    e.stopPropagation();
    var productList = $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-checked');
    var productChecked = productList.filter(':checked');
    if (productChecked.length < 1) {
      $('.ywcas-boost-product-modal-content .boost-product-error').show();
    } else {
      $('.ywcas-boost-product-modal-content .boost-product-error').hide();
    }
    var form = jQuery(e.target.closest('form'));
    var formData = new FormData();
    var wrapper = $('.ywcas-boost-table');
    jQuery.each(form.serializeArray(), function (i, field) {
      formData.append(field.name, field.value);
    });
    formData.append('security', ywcas_boost_product_params.searchProductNonce);
    formData.append('action', 'ywcas_boost_product');
    formData.append('href', document.location.href);
    if (false !== ajaxCall) {
      ajaxCall.abort();
    }
    ajaxCall = jQuery.ajax({
      url: ywcas_boost_product_params.ajaxurl,
      data: formData,
      dataType: 'json',
      contentType: false,
      processData: false,
      type: 'POST',
      beforeSend: function beforeSend() {
        modal.close();
        wrapper.block(blockParams);
      },
      success: function success(response) {
        var _response$data2;
        if ((_response$data2 = response.data) !== null && _response$data2 !== void 0 && _response$data2.content) {
          var _response$data3;
          var responseModal = $((_response$data3 = response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.content);
          var resultBox = responseModal.find('.ywcas-boost-detail');
          $(document).find('.ywcas-boost-table .ywcas-boost-detail').html(resultBox.html());
          init();
        }
      },
      complete: function complete() {
        wrapper.unblock();
      }
    });
  };
  var deleteBoost = function deleteBoost(e) {
    e.stopPropagation();
    e.preventDefault();
    yith.ui.confirm({
      title: ywcas_boost_product_params.confirm_message.title,
      message: ywcas_boost_product_params.confirm_message.desc,
      confirmButton: ywcas_boost_product_params.confirm_message.confirmButton,
      closeAfterConfirm: true,
      classes: {
        wrap: 'ywcas-warning-popup'
      },
      onConfirm: function onConfirm() {
        var post_id = $(e.target.closest('.action-wrapper')).data('post_id');
        var currentRow = $(e.target.closest('tr'));
        var rows = $(e.target.closest('#the-list')).find('tr').length;
        var wrapper = currentRow;
        $.ajax({
          url: ywcas_boost_product_params.ajaxurl,
          data: {
            security: ywcas_boost_product_params.searchProductNonce,
            action: 'ywcas_delete_boosted_product',
            post_id: post_id,
            rows: rows
          },
          type: 'POST',
          beforeSend: function beforeSend() {
            wrapper.block(blockParams);
          },
          success: function success(response) {
            if (response.success) {
              var _response$data4;
              if (((_response$data4 = response.data) === null || _response$data4 === void 0 ? void 0 : _response$data4.content) !== '') {
                var _response$data5;
                var responseModal = $((_response$data5 = response.data) === null || _response$data5 === void 0 ? void 0 : _response$data5.content);
                var resultBox = responseModal.find('.ywcas-boost-detail');
                $(document).find('.ywcas-boost-table .ywcas-boost-detail').html(resultBox.html());
                init();
              } else {
                currentRow.remove();
              }
              showNotice();
            }
          },
          complete: function complete() {
            wrapper.unblock();
          }
        });
      }
    });
  };
  var updateBoost = function updateBoost(e) {
    if (false !== ajaxCall) {
      ajaxCall.abort();
    }
    var postID = $(e.target).data('post_id');
    var newBoost = $(e.target).val();
    ajaxCall = $.ajax({
      url: ywcas_boost_product_params.ajaxurl,
      data: {
        security: ywcas_boost_product_params.searchProductNonce,
        action: 'ywcas_update_boost',
        postID: postID,
        newBoost: newBoost
      },
      type: 'POST',
      success: function success(response) {
        if (response.success) {
          var _response$data6;
          var responseModal = jQuery((_response$data6 = response.data) === null || _response$data6 === void 0 ? void 0 : _response$data6.content);
          var resultBox = responseModal.find('.ywcas-boost-product-search-results');
          $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results').html(resultBox.html());
        }
      }
    });
  };
  var showAddProductButton = function showAddProductButton() {
    var checkButton = $(document).find('#yith_wcas_panel_boost-boost-product h1 #header-add-product');
    var emptyTable = $(document).find('.yith-plugin-fw__list-table-blank-state__icon');
    if (checkButton.length > 0 && emptyTable.length > 0) {
      checkButton.remove();
    }
    if (checkButton.length > 0 || emptyTable.length > 0) {
      return;
    }
    var addButton = $(document).find('#header-add-product');
    if (addButton.length > 0) {
      $('#yith_wcas_panel_boost-boost-product h1').append(addButton);
    }
  };
  var bulkActionConfirm = function bulkActionConfirm(e) {
    e.stopPropagation();
    e.preventDefault();
    var bulk = $(document).find('#bulk-action-selector-top');
    var bulks = $(document).find('input[name="boost[]"]:checked').length;
    if ('delete' !== bulk.val() || bulks === 0) {
      return false;
    }
    yith.ui.confirm({
      title: ywcas_boost_product_params.confirm_message.title,
      message: ywcas_boost_product_params.confirm_message.desc,
      confirmButton: ywcas_boost_product_params.confirm_message.confirmButton,
      closeAfterConfirm: true,
      classes: {
        wrap: 'ywcas-warning-popup'
      },
      onConfirm: function onConfirm() {
        return $(document).find('#ywcas-boost-product-list-form').submit();
      }
    });
  };
  init();
};
YWCAS_AdminBoostProduct();
var __webpack_export_target__ = window;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=boost-product.js.map