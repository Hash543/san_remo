/**
 * @override to add products to the cart
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data'
], function ($, authenticationPopup, customerData) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            event.preventDefault();

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }

            const formCart = $('.form-cart#form-validate');
            if(formCart.length > 0 ) {
                formCart[0].action = config.updateCartUrl;
                formCart[0].submit();
            } else {
                location.href = config.checkoutUrl;
            }

            /*$.ajax({
                url: config.updateCartUrl,
                type: "POST",
                data: formCart.serialize(),
                showLoader: true
            }).done(function (response) {
                if(response.success) {
                    setTimeout(function () {
                        location.href = config.checkoutUrl;
                    }, 1000);
                } else {
                    //location.href = config.cartUrl;
                    $(element).attr('disabled', false);
                }
            }).fail(function () {
                //location.href = config.cartUrl;
                $(element).attr('disabled', false);
            });*/
        });

    };
});
