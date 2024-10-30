(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $( window ).load(function() {
        jQuery('#wpinv-add-item').attr('id', 'wpinv-dp-add-item');
	 	jQuery('#wpinv-save-item').attr('id', 'wpinv-dp-save-item');
        jQuery('#wpinv-recalc-totals').attr('id', 'wpinv-dp-recalc-totals');
        jQuery('.wpinv-item-remove').removeClass('wpinv-item-remove').addClass('wpinv-dp-item-remove');
	 });


    $(document).on('click', '#wpinv-dp-save-item', function(e) {
        e.preventDefault();
        var metaBox = $('#wpinv_items_wrap');
        var gdTotals = $('.wpinv-totals', metaBox);
        var invoice_id = metaBox.closest('form[name="post"]').find('input#post_ID').val();
        var item_title = $('[name="_wpinv_quick[name]"]', metaBox).val();
        var item_price = $('[name="_wpinv_quick[price]"]', metaBox).val();
        if (!(invoice_id > 0)) {
            return false;
        }
        if (!item_title) {
            $('[name="_wpinv_quick[name]"]', metaBox).focus();
            return false;
        }
        if (item_price === '') {
            $('[name="_wpinv_quick[price]"]', metaBox).focus();
            return false;
        }
        var customPrice = {};
        var customDescription = {};
        $('textarea.dp_description').each(function(){
            var item_id = $(this).attr('id');
            item_id = item_id.replace("dp_description_", "");
            var desContent = $('#dp_description_'+item_id+'_ifr').contents().find('.dp_description_'+item_id).html();
            customDescription[item_id] = desContent;
        })
        $('.dp_price').each(function(){
            var item_id = $(this).attr('data-invoiceid');
            customPrice[item_id] = $(this).val();
        })

        wpinvBlock(metaBox);
        var data = {
            action: 'wpinv_dp_create_invoice_item',
            invoice_id: invoice_id,
            customDescription: customDescription,
            extraPrice: customPrice,
            _nonce: WPInv_Admin.invoice_item_nonce
        };
        var fields = $('[name^="_wpinv_quick["]');
        for (var i in fields) {
            data[fields[i]['name']] = fields[i]['value'];
        }
        var user_id, country, state;
        if (user_id = $('[name="post_author_override"]').val()) {
            data.user_id = user_id;
        }
        if (parseInt($('#wpinv_new_user').val()) == 1) {
            data.new_user = true;
        }
        if (country = $('#wpinv-address [name="wpinv_country"]').val()) {
            data.country = country;
        }
        if (state = $('#wpinv-address [name="wpinv_state"]').val()) {
            data.state = state;
        }
        $.post(WPInv_Admin.ajax_url, data, function(response) {
            wpinvUnblock(metaBox);
            if (response && typeof response == 'object') {
                if (response.success === true) {
                    $('[name="_wpinv_quick[name]"]', metaBox).val('');
                    $('[name="_wpinv_quick[price]"]', metaBox).val('');
                    update_inline_items(response.data, metaBox, gdTotals);
                } else if (response.msg) {
                    alert(response.msg);
                }
            }
        });
    });

    $(document).on('click', '#wpinv-dp-add-item', function(e) {
        var metaBox = $('#wpinv_items_wrap');
        var gdTotals = $('.wpinv-totals', metaBox);
        var item_id = $('#wpinv_invoice_item').val();
        var invoice_id = metaBox.closest('form[name="post"]').find('input#post_ID').val();
        if (!(item_id > 0 && invoice_id > 0)) {
            return false;
        }

        
        var customPrice = {};
        var customDescription = {};
        $('textarea.dp_description').each(function(){
            var item_id = $(this).attr('id');
            item_id = item_id.replace("dp_description_", "");
            var desContent = $('#dp_description_'+item_id+'_ifr').contents().find('.dp_description_'+item_id).html();
            customDescription[item_id] = desContent;
        })
        $('.dp_price').each(function(){
            var item_id = $(this).attr('data-invoiceid');
            customPrice[item_id] = $(this).val();
        })

        wpinvBlock(metaBox);
        var data = {
            action: 'wpinv_dp_add_invoice_item',
            invoice_id: invoice_id,
            item_id: item_id,
            customDescription: customDescription,
            extraPrice: customPrice,
            _nonce: WPInv_Admin.invoice_item_nonce
        };
        var user_id, country, state;
        if (user_id = $('[name="post_author_override"]').val()) {
            data.user_id = user_id;
        }
        if (parseInt($('#wpinv_new_user').val()) == 1) {
            data.new_user = true;
        }
        if (country = $('#wpinv-address [name="wpinv_country"]').val()) {
            data.country = country;
        }
        if (state = $('#wpinv-address [name="wpinv_state"]').val()) {
            data.state = state;
        }
        $.post(WPInv_Admin.ajax_url, data, function(response) {
            wpinvUnblock(metaBox);
            if (response && typeof response == 'object') {
                if (response.success === true) {
                    update_inline_items(response.data, metaBox, gdTotals);
                } else if (response.msg) {
                    alert(response.msg);
                }
            }
        });
    });

    $(document).on('click', '#wpinv-dp-recalc-totals', function(e) {
        e.preventDefault();
        var metaBox = $('#wpinv_items_wrap');
        var gdTotals = $('.wpinv-totals', metaBox);
        var invoice_id = metaBox.closest('form[name="post"]').find('input#post_ID').val();
        if (!invoice_id > 0) {
            return false;
        }
        if (!parseInt($(document.body).find('.wpinv-line-items > .item').length) > 0) {
            if (!window.wpiConfirmed) {
                alert(WPInv_Admin.emptyInvoice);
                $('#wpinv_invoice_item').focus();
            }
            return false;
        }
        if (!window.wpiConfirmed && !window.confirm(WPInv_Admin.confirmCalcTotals)) {
            return false;
        }
        wpinvBlock(metaBox);

        var customPrice = {};
        var customDescription = {};
        $('textarea.dp_description').each(function(){
            var item_id = $(this).attr('id');
            item_id = item_id.replace("dp_description_", "");
            var desContent = $('#dp_description_'+item_id+'_ifr').contents().find('.dp_description_'+item_id).html();
            customDescription[item_id] = desContent;
        })
        $('.dp_price').each(function(){
            var item_id = $(this).attr('data-invoiceid');
            customPrice[item_id] = $(this).val();
        })

        var data = {
            action: 'wpinv_admin_recalculate_totals',
            invoice_id: invoice_id,
            customDescription: customDescription,
            extraPrice: customPrice,
            _nonce: WPInv_Admin.wpinv_nonce
        };
        var user_id, country, state;
        if (user_id = $('[name="post_author_override"]').val()) {
            data.user_id = user_id;
        }
        if (parseInt($('#wpinv_new_user').val()) == 1) {
            data.new_user = true;
        }
        if (country = $('#wpinv-address [name="wpinv_country"]').val()) {
            data.country = country;
        }
        if (state = $('#wpinv-address [name="wpinv_state"]').val()) {
            data.state = state;
        }
        $.post(WPInv_Admin.ajax_url, data, function(response) {
            wpinvUnblock(metaBox);
            if (response && typeof response == 'object') {
                if (response.success === true) {
                    update_inline_items(response.data, metaBox, gdTotals);
                }
            }
        });
    });
    $(document).on('change', '.dp_price', function(e) {
        e.preventDefault();
        var metaBox = $('#wpinv_items_wrap');
        var gdTotals = $('.wpinv-totals', metaBox);
        var invoice_id = metaBox.closest('form[name="post"]').find('input#post_ID').val();
        if (!invoice_id > 0) {
            return false;
        }
        if (!parseInt($(document.body).find('.wpinv-line-items > .item').length) > 0) {
            if (!window.wpiConfirmed) {
                alert(WPInv_Admin.emptyInvoice);
                $('#wpinv_invoice_item').focus();
            }
            return false;
        }

        wpinvBlock(metaBox);

        
        var customPrice = {};
        var customDescription = {};
        $('textarea.dp_description').each(function(){
            var item_id = $(this).attr('id');
            item_id = item_id.replace("dp_description_", "");
            var desContent = $('#dp_description_'+item_id+'_ifr').contents().find('.dp_description_'+item_id).html();
            customDescription[item_id] = desContent;
        })
        $('.dp_price').each(function(){
            var item_id = $(this).attr('data-invoiceid');
            customPrice[item_id] = $(this).val();
        })

        var data = {
            action: 'wpinv_admin_recalculate_totals',
            invoice_id: invoice_id,
            customDescription: customDescription,
            extraPrice: customPrice,
            _nonce: WPInv_Admin.wpinv_nonce
        };
        var user_id, country, state;
        if (user_id = $('[name="post_author_override"]').val()) {
            data.user_id = user_id;
        }
        if (parseInt($('#wpinv_new_user').val()) == 1) {
            data.new_user = true;
        }
        if (country = $('#wpinv-address [name="wpinv_country"]').val()) {
            data.country = country;
        }
        if (state = $('#wpinv-address [name="wpinv_state"]').val()) {
            data.state = state;
        }
        $.post(WPInv_Admin.ajax_url, data, function(response) {
            wpinvUnblock(metaBox);
            if (response && typeof response == 'object') {
                if (response.success === true) {
                    update_inline_items(response.data, metaBox, gdTotals);
                }
            }
        });
    });


    $(document).on('click', '.wpinv-dp-item-remove', function(e) {
        var item = $(this).closest('.item');
        var count = $(document.body).find('.wpinv-line-items > .item').length;
        var qty = parseInt($('.qty', item).data('quantity'));
        qty = qty > 0 ? qty : 1;
        if (count === 1 && qty == 1) {
            alert(WPInv_Admin.OneItemMin);
            return false;
        }
        if (confirm(WPInv_Admin.DeleteInvoiceItem)) {
            e.preventDefault();
            var metaBox = $('#wpinv_items_wrap');
            var gdTotals = $('.wpinv-totals', metaBox);
            var item_id = item.data('item-id');
            var invoice_id = metaBox.closest('form[name="post"]').find('input#post_ID').val();
            var index = $(item).index();
            if (!(item_id > 0 && invoice_id > 0)) {
                return false;
            }
            wpinvBlock(metaBox);

            var customPrice = {};
            var customDescription = {};
            $('textarea.dp_description').each(function(){
                var item_id = $(this).attr('id');
                item_id = item_id.replace("dp_description_", "");
                var desContent = $('#dp_description_'+item_id+'_ifr').contents().find('.dp_description_'+item_id).html();
                customDescription[item_id] = desContent;
            })
            $('.dp_price').each(function(){
                var item_id = $(this).attr('data-invoiceid');
                customPrice[item_id] = $(this).val();
            })

            var data = {
                action: 'wpinv_dp_remove_invoice_item',
                invoice_id: invoice_id,
                item_id: item_id,
                customDescription: customDescription,
                extraPrice: customPrice,
                index: index,
                _nonce: WPInv_Admin.invoice_item_nonce
            };
            $.post(WPInv_Admin.ajax_url, data, function(response) {
                item.remove();
                wpinvUnblock(metaBox);
                if (response && typeof response == 'object') {
                    if (response.success === true) {
                        update_inline_items(response.data, metaBox, gdTotals);
                    } else if (response.msg) {
                        alert(response.msg);
                    }
                }
            });
        }
    });

    $(document).on('click', '.dp_description-wrap > label', function(){
        if(jQuery(this).parents('.dp_description-wrap').hasClass('active'))
        {
            jQuery(this).parents('.dp_description-wrap').removeClass('active');
        }
        else
        {
            jQuery(this).parents('.dp_description-wrap').addClass('active');
        }
    });


function update_inline_items(data, metaBox, gdTotals) {
    if (data.discount > 0) {
        data.discountf = '&ndash;' + data.discountf;
    }
    $('.wpinv-line-items', metaBox).html(data.items);
    $('.subtotal .total', gdTotals).html(data.subtotalf);
    $('.tax .total', gdTotals).html(data.taxf);
    $('.extra_price .total', gdTotals).html(data.extraPrice);
    $('.discount .total', gdTotals).html(data.discountf);
    $('.total .total', gdTotals).html(data.totalf);
    $('#wpinv-details input[name="wpinv_discount"]').val(data.discount);
    $('#wpinv-details input[name="wpinv_tax"]').val(data.tax);
}
})( jQuery );

