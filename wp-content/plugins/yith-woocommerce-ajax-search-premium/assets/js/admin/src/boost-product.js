const YWCAS_AdminBoostProduct = () => {
  const $ = jQuery;
  let ajaxCall = false;
  let currentTerm = '';
  let modal = false;
  var blockParams = {
    message: null,
    overlayCSS: {background: '#fff', opacity: 0.7},
    ignoreIfBlocked: true,
  };

  const init = () => {
    $(document).on('click', '.ywcas-boost-product-button', openModal);
    $(document).on('click', '.ywcas-boost-product-modal-content #ywcas-add-boost-product', addProducts);
    $(document).on('click', '.yith-plugin-fw__action-button--delete-action', deleteBoost);
    $(document).on('click', 'input[name="ywcas_boost"]', updateBoost);
    $(document).on('click', '#doaction', bulkActionConfirm );
    $(document).on('keydown', '.ywcas-boost-product-modal-content #ywcas-boost-product-search', searchProducts);
    $(document).on('click', '.ywcas-boost-detail .notice-dismiss', hideNotice );
    const isEmpty = $(document).find('.yith-plugin-fw__list-table-blank-state ');

    if( isEmpty.length > 0 ) {
      $(document).find('#yith_wcas_panel_boost-boost-product #yith-plugin-fw-panel-custom-tab-boost-boost-boost-product').removeClass('list-full');
    }else{
      $(document).find('#yith_wcas_panel_boost-boost-product #yith-plugin-fw-panel-custom-tab-boost-boost-boost-product').addClass('list-full');
    }
    showAddProductButton();
  };

  const showNotice = ( ) =>{

    $(document).find('.ywcas-boost-detail #message').addClass('show');
  }

  const hideNotice = () =>{
    $(document).find('.ywcas-boost-detail #message').removeClass('show');
  }

  const openModal = (ev) => {
    ev.preventDefault();

    modal = yith.ui.modal(
        {
          title: ywcas_boost_product_params.modalTitle,
          content: $('#ywcas-modal-content').html(),
          width: 560,
          allowWpMenu: false,
          closeWhenClickingOnOverlay: true,
          classes: {
            content: 'ywcas-boost-product-modal-content',
          },
        },
    );
    init();
  };

  const searchProducts = () => {
    const term = $('.ywcas-boost-product-modal-content #ywcas-boost-product-search').val();
    if (currentTerm === term || term.length < 2) {
      return;
    }

    if (false !== ajaxCall) {
      ajaxCall.abort();
    }
    const wrapper = $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results');
    currentTerm = term;

    ajaxCall = $.ajax({
      url: ywcas_boost_product_params.ajaxurl,
      data: {security: ywcas_boost_product_params.searchProductNonce, action: 'ywcas_search_product', term},
      type: 'POST',
      beforeSend: function() {
        wrapper.block(blockParams);
      },
      success: function(response) {
        if (response.success) {
          const responseModal = jQuery(response.data?.content);
          const resultBox = responseModal.find('.ywcas-boost-product-search-results');
          $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results').html(resultBox.html());
        }
      },
      complete: function() {
        wrapper.unblock();
      },
    });
  };

  const addProducts = (e) => {
    e.preventDefault();
    e.stopPropagation();

    const productList = $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-checked');
    const productChecked = productList.filter(':checked');

    if (productChecked.length < 1) {
      $('.ywcas-boost-product-modal-content .boost-product-error').show();
    } else {
      $('.ywcas-boost-product-modal-content .boost-product-error').hide();
    }

    const form = jQuery(e.target.closest('form'));
    const formData = new FormData();
    const wrapper = $('.ywcas-boost-table');
    jQuery.each(form.serializeArray(), function(i, field) {
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
      beforeSend: function() {
        modal.close();
        wrapper.block(blockParams);
      },
      success: function(response) {
        if (response.data?.content) {
          const responseModal = $(response.data?.content);
          const resultBox = responseModal.find('.ywcas-boost-detail');
          $(document).find('.ywcas-boost-table .ywcas-boost-detail').html(resultBox.html());
          init();
        }
      },
      complete: function() {
        wrapper.unblock();
      },
    });

  };

  const deleteBoost = (e) => {
    e.stopPropagation();
    e.preventDefault();

    yith.ui.confirm({
      title: ywcas_boost_product_params.confirm_message.title,
      message: ywcas_boost_product_params.confirm_message.desc,
      confirmButton: ywcas_boost_product_params.confirm_message.confirmButton,
      closeAfterConfirm: true,
      classes: {
        wrap: 'ywcas-warning-popup',
      },
      onConfirm: function onConfirm() {
        const post_id = $(e.target.closest('.action-wrapper')).data('post_id');
        const currentRow = $(e.target.closest('tr'));
        const rows = $(e.target.closest('#the-list')).find('tr').length;
        const wrapper = currentRow;
        $.ajax({
          url: ywcas_boost_product_params.ajaxurl,
          data: {security: ywcas_boost_product_params.searchProductNonce, action: 'ywcas_delete_boosted_product', post_id, rows},
          type: 'POST',
          beforeSend: function() {
            wrapper.block(blockParams);
          },
          success: function(response) {
            if (response.success) {
              if (response.data?.content !== '') {
                const responseModal = $(response.data?.content);
                const resultBox = responseModal.find('.ywcas-boost-detail');
                $(document).find('.ywcas-boost-table .ywcas-boost-detail').html(resultBox.html());
                init();
              } else {
                currentRow.remove();
              }

              showNotice();

            }
          },
          complete: function() {
            wrapper.unblock();
          },
        });
      },
    });

  };

  const updateBoost = (e) => {
    if (false !== ajaxCall) {
      ajaxCall.abort();
    }

    const postID = $(e.target).data('post_id');
    const newBoost = $(e.target).val();
    ajaxCall = $.ajax({
      url: ywcas_boost_product_params.ajaxurl,
      data: {security: ywcas_boost_product_params.searchProductNonce, action: 'ywcas_update_boost', postID, newBoost},
      type: 'POST',

      success: function(response) {
        if (response.success) {
          const responseModal = jQuery(response.data?.content);
          const resultBox = responseModal.find('.ywcas-boost-product-search-results');
          $(document).find('.ywcas-boost-product-modal-content .ywcas-boost-product-search-results').html(resultBox.html());
        }
      },

    });

  };

  const showAddProductButton = () => {
    const checkButton = $(document).find('#yith_wcas_panel_boost-boost-product h1 #header-add-product');
    const emptyTable = $(document).find('.yith-plugin-fw__list-table-blank-state__icon');

    if (checkButton.length > 0 && emptyTable.length > 0) {
      checkButton.remove();
    }

    if (checkButton.length > 0 || emptyTable.length > 0) {
      return;
    }

    const addButton = $(document).find('#header-add-product');
    if (addButton.length > 0) {
      $('#yith_wcas_panel_boost-boost-product h1').append(addButton);
    }
  };

  const bulkActionConfirm = ( e ) => {
    e.stopPropagation();
    e.preventDefault();
    const bulk = $(document).find('#bulk-action-selector-top');
    const bulks = $(document).find('input[name="boost[]"]:checked').length;

    if( 'delete' !== bulk.val() || bulks === 0){
      return false;
    }

    yith.ui.confirm({
      title: ywcas_boost_product_params.confirm_message.title,
      message: ywcas_boost_product_params.confirm_message.desc,
      confirmButton: ywcas_boost_product_params.confirm_message.confirmButton,
      closeAfterConfirm: true,
      classes: {
        wrap: 'ywcas-warning-popup',
      },
      onConfirm: function onConfirm() {
        return $(document).find('#ywcas-boost-product-list-form').submit();
      },
    });
  }

  init();
};

YWCAS_AdminBoostProduct();