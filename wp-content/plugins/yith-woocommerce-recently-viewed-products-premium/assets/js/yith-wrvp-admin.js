/**
 * admin.js
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Recently Viewed Products
 * @version 1.0.0
 */

jQuery(document).ready(function ($) {
    "use strict";

    var emailXhr,
        couponXhr;

    function escapeHtml( unsafe ) {
        return unsafe
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    $( '.yith-wrvp-shortcode-tab' ).each( function(){
        var shortcode_option = $(this).find( '.form-table input, .form-table select' ),
            preview = $( this ).find( '.shortcode-preview' );

        if( typeof $.fn.select2 != 'undefined' ) {
            shortcode_option.filter( 'select' ).select2();
        }

        shortcode_option.each( function(){

            $(this).on( 'change', function(){

                if( this.type == 'radio' && ! $(this).is( ':checked' ) ) {
                    return;
                }

                var value       = ( this.type == 'checkbox' && ! $(this).is( ':checked' ) ) ? 'no' : $(this).val(),
                    name        = this.name.replace('[]', ''),
                    shortcode   = preview.text(),
                    attr;

                // remove old
                var reg = new RegExp( name + '="([^"]*)"', 'g' );
                shortcode = shortcode.replace( reg, '' );

                if( ! value ) {
                    preview.text( shortcode );
                    return;
                }

                // else add attr
                shortcode = shortcode.replace(']', '');

                if ( typeof value == 'object' ) {
                    value = value.join(',');
                }

                value   = escapeHtml( value );
                attr    = name + '="' + value + '"';
                preview.text( shortcode + ' ' + attr + ']' );

            }).change();
        });
    });

    /*#############################
     ** TEST EMAIL
     #############################*/

    function validateEmail( email ) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test( email );
    }

    $( document ).on( 'keyup', '#yith-wrvp-test-mail', function() {
        if ( emailXhr ) {
            emailXhr.abort();
        }

        $( this ).removeClass( 'error valid loading' );
    });

    $( document ).on( 'click', '.ywrvp-send-test-email', function( ev ) {
        ev.preventDefault();

        var trigger = $(this),
            wrap    = trigger.closest( '.yith-wrvp-test-email-wrap' ),
            input   = $( '#yith-wrvp-test-mail' ),
            email   = input.val().toLowerCase(),
            data;

        if ( emailXhr ) {
            emailXhr.abort();
        }

        if( ! email || ! validateEmail( email ) ) {
            input.addClass( 'error' );
            return false;
        }

        data = $( '#plugin-fw-wc' ).serializeArray();
        // add action and nonce
        data.push(
            { name: 'action', value: ywrvp.testEmailAction },
            { name: 'security', value: ywrvp.testEmailNonce }
        );

        emailXhr = $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                input.addClass( 'loading' )
            },
            success: function( response ) {
                input.removeClass( 'loading' );
                if( response.success ) {
                    input.removeClass( 'error' ).addClass( 'valid' );
                } else {
                    input.removeClass( 'valid' ).addClass( 'error' );
                }
            },
            complete: function() {
                emailXhr = false;
            }
        });
    });

    /*####################################
      ** HANDLE MULTIPLE EMAIL PANEL DEPS
      ###################################*/

    $( '#yith_wrvp_panel_email' ).find( '[data-deps]' ).each( function() {

        var t           = $(this),
            wrap        = t.closest( 'tr' ),
            deps        = t.attr('data-deps').split(','),
            values      = t.attr('data-deps_value').split(','),
            conditions  = [];

        $.each( deps, function( i, dep ) {
            $( '[name="' + dep + '"]').on( 'change', function(){

                var value           = this.value,
                    check_values    = '';

                // exclude radio if not checked
                if( this.type == 'radio' && ! $(this).is(':checked') ){
                    return;
                }

                if( this.type == 'checkbox' ){
                    value = $(this).is(':checked') ? 'yes' : 'no';
                }

                check_values = values[i] + ''; // force to string
                check_values = check_values.split('|');
                conditions[i] = $.inArray( value, check_values ) !== -1;

                if( $.inArray( false, conditions ) === -1 ){
                    wrap.fadeIn();
                } else {
                    wrap.fadeOut();
                }

            }).change();
        });
    });

    /*#############################
      ** VALIDATE COUPON
      ############################*/

    $(document).find('input.yith_wrvp_coupon_validate').on( 'keyup', function(){
        var t   = $(this),
            val = t.val();

        if ( couponXhr ) {
            couponXhr.abort();
        }

        if( ! val ) {
            t.removeClass( 'error valid loading' );
        }
        else {
            t.addClass( 'loading' );
            couponXhr = $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: ywrvp.validateCouponAction,
                    security: ywrvp.validateCouponNonce,
                    code: val
                },
                dataType: 'json',
                success: function( response ) {
                    t.removeClass( 'loading' );
                    if( response.valid) {
                        t.removeClass( 'error' ).addClass( 'valid' );
                    }
                    else {
                        t.removeClass( 'valid' ).addClass( 'error' );
                    }
                },
                complete: function() {
                    couponXhr = false;
                }
            });
        }
    }).keyup();

    /*##########################
      CUSTOM CHECKLIST
    ###########################*/

    var array_unique_noempty, element_box;

    array_unique_noempty = function (array) {
        var out = [];

        $.each(array, function (key, val) {
            val = $.trim(val);

            if (val && $.inArray(val, out) === -1) {
                out.push(val);
            }
        });

        return out;
    };

    element_box = {
        clean: function (tags) {
            tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
            return tags;
        },

        parseTags: function (el) {
            var id = el.id,
                num = id.split('-check-num-')[1],
                element_box = $(el).closest('.ywrvp-checklist-div'),
                values = element_box.find('.ywrvp-values'),
                current_values = values.val().split(','),
                new_elements = [];

            delete current_values[num];

            $.each(current_values, function (key, val) {
                val = $.trim(val);
                if (val) {
                    new_elements.push(val);
                }
            });

            values.val(this.clean(new_elements.join(',')));

            this.quickClicks(element_box);
            return false;
        },

        quickClicks: function (el) {

            var values = $('.ywrvp-values', el),
                values_list = $('.ywrvp-value-list ul', el),
                id = $(el).attr('id'),
                current_values;

            if (!values.length)
                return;

            current_values = values.val().split(',');
            values_list.empty();

            $.each(current_values, function (key, val) {

                var item, xbutton;

                val = $.trim(val);

                if (!val)
                    return;

                item = $('<li class="select2-selection__choice" />');
                xbutton = $('<span id="' + id + '-check-num-' + key + '" class="select2-selection__choice__remove" tabindex="0"></span>');

                xbutton.on('click keypress', function (e) {

                    if (e.type === 'click' || e.keyCode === 13) {

                        if (e.keyCode === 13) {
                            $(this).closest('.ywrvp-checklist-div').find('input.ywrvp-insert').focus();
                        }

                        element_box.parseTags(this);
                    }

                });

                item.prepend(val).prepend(xbutton);

                values_list.append(item);

            });
        },

        flushTags: function (el, a, f) {
            var current_values,
                new_values,
                text,
                values = $('.ywrvp-values', el),
                add_new = $('input.ywrvp-insert', el);

            a = a || false;

            text = a ? $(a).text() : add_new.val();

            if ('undefined' === typeof (text)) {
                return false;
            }

            current_values = values.val();
            new_values = current_values ? current_values + ',' + text : text;
            new_values = this.clean(new_values);
            new_values = array_unique_noempty(new_values.split(',')).join(',');
            values.val(new_values);

            this.quickClicks(el);

            if (!a)
                add_new.val('');
            if ('undefined' === typeof (f))
                add_new.focus();

            return false;

        },

        init: function () {
            var ajax_div = $('.ywrvp-checklist-ajax');

            $('.ywrvp-checklist-div').each(function () {
                element_box.quickClicks(this);
            });

            $('input.ywrvp-insert', ajax_div).keyup(function (e) {
                if (13 === e.which) {
                    element_box.flushTags($(this).closest('.ywrvp-checklist-div'));
                    return false;
                }
            }).keypress(function (e) {
                if (13 === e.which) {
                    e.preventDefault();
                    return false;
                }
            });


        }
    };

    element_box.init();
});