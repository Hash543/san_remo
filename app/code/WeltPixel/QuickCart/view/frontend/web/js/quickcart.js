define(['jquery', 'jquery/ui', 'domReady'], function ($) {
    "use strict";

    var quickcart =
        {
            initialize: function() {
                if (this.getIsEnabled()) {
                    $('.quickcart-content-wrapper').on('click', '.qty-update', function () {
                        quickcart.updateQty($(this));
                    });
                    $('.showcart').on('click', function () {
                        quickcart.checkSafariBrowser();
                    });
                    $('.quickcart-content-wrapper').on('click', '.product-item-photo, .product-item-name a', function () {
                        var href = $(this).attr('href');
                        window.location.href = href;
                    });
                    if (this.openMinicart()) {
                        var minicart = $('.minicart-wrapper');
                        minicart.on('contentLoading', function () {
                            minicart.on('contentUpdated', function () {
                                quickcart.checkSafariBrowser();
                                minicart.find('[data-role="dropdownDialog"]').dropdownDialog("open");
                            });
                        });
                    }
                }
            },
            getIsEnabled: function () {
                if (window.quickcartEnabled == 1) {
                    return true;
                } else {
                    return false;
                }
            },
            openMinicart: function() {
                if (window.openMinicart == 1) {
                    return true;
                } else {
                    return false;
                }
            },
            updateQty: function (el) {
                var qtyContainer = el.closest('.details-qty'),
                    currentQty = parseFloat(qtyContainer.find('input').val());

                if (el.hasClass('item-plus')) {
                    var newQty = currentQty + 1;
                    this.updateItemQty(el, newQty);
                } else {
                    if (currentQty > 1) {
                        var newQty = parseFloat(currentQty) - 1;
                        this.updateItemQty(el, newQty);
                    } else {
                        this.deleteCartItem(el);
                    }
                }
            },
            showSpinner: function (el) {
                el.closest('.details-qty').find('.spinner').show();
                this.updateUpdateCart(el);
            },
            updateItemQty: function (el, qty) {
                el.closest('.details-qty').find('input').val(qty).hide();
                this.showSpinner(el);
            },
            updateUpdateCart: function (el) {
                el.closest('.details-qty').find('button.update-cart-item').trigger('click');
            },
            deleteCartItem: function (el) {
                el.closest('.product-item-details').find('.product .action.delete').trigger('click');
            },
            checkSafariBrowser: function () {
                var is_safari =  navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1 &&  navigator.userAgent.indexOf('Android') == -1
                if (is_safari){
                    $('.page-wrapper').css('overflow-x','visible')
                }else{
                    $('.page-wrapper').css('overflow-x','hidden')
                }
            }
        };

    return quickcart;
});