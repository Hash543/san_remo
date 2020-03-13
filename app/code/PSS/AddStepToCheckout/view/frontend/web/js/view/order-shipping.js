/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_AddStepToCheckout
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    [
        'ko',
        'uiComponent',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/sidebar',
        'Magento_Checkout/js/model/payment-service'
    ],
    function(
        ko,
        Component,
        customer,
        quote,
        stepNavigator,
        sidebarModel,
        paymentInformation
    ) {
        'use strict';

        return Component.extend({

            defaults: {
               template: 'PSS_AddStepToCheckout/order-shipping'
            },

            /**
             * @return {Boolean}
             */
            isVisible: function () {
                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            },

            /**
             * @return {String}
             */
            getShippingMethodTitle: function () {
                var shippingMethod = quote.shippingMethod();

                return shippingMethod ? shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'] : 'N/A';
            },

            /**
             * @return {String}
             */
            getPaymentMethodTitle: function () {

                var paymentMethod = quote.paymentMethod();

                var paymentMethods = paymentInformation.getAvailablePaymentMethods();

                var paymentTitle = 'N/A';

                if(paymentMethod) {
                    paymentMethods.forEach(
                        function (element) {
                            if (element.method === paymentMethod['method']) {
                                paymentTitle = element.title;
                            }
                        }
                    );
                }

                return paymentTitle;

            },

            /**
             * Back step.
             */
            back: function () {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping');
            },

            /**
             * Back to shipping method.
             */
            backToShippingMethod: function () {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping', 'opc-shipping_method');
            },

            /**
             * Back to payment method.
             */
            backToPaymentMethod: function () {
                sidebarModel.hide();
                stepNavigator.navigateTo('payment', 'opc-payment_method');
            },


            isCustomerLoggedIn: customer.isLoggedIn,
            customerData: customer.customerData,

        });
    }
);
