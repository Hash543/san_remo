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

define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Ui/js/model/messages',
    'mage/storage',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    customer,
    stepNavigator,
    paymentService,
    urlBuilder,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    fullScreenLoader,
    errorProcessor,
    getTotals,
    Messages,
    storage,
    $t
) {
    'use strict';

    /** Set payment methods to collection */
    paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

    return Component.extend({

        defaults: {
            template: 'PSS_AddStepToCheckout/payment',
            activeMethod: ''
        },

        isVisible: ko.observable(quote.isVirtual()),

        quoteIsVirtual: quote.isVirtual(),

        isCustomerLoggedIn: customer.isLoggedIn,

        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        errorValidationMessage: ko.observable(true),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            checkoutDataResolver.resolvePaymentMethod();
            stepNavigator.registerStep(
                'payment',
                null,
                $t('Payments'),
                this.isVisible,
                _.bind(this.navigate, this),
                20
            );
            this.messageContainer = new Messages();
            return this;
        },

        /**
         * Navigate method.
         */
        navigate: function () {
            var self = this;

            getPaymentInformation().done(function () {
                self.isVisible(true);
            });
        },

        navigateToNextStep: function () {
            stepNavigator.next();
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        },

        /**
         * @return {*}
         */
        validate: function () {

            var paymentMethodInputPath = 'div.payment-group .payment-method._active';

            var activeMethod = false;

            $(paymentMethodInputPath).each(function (index, element) {
                if($(element).hasClass('_active')) {
                    activeMethod = true;
                }
            });

            if(!activeMethod){
                this.errorValidationMessage($t('You must select a payment Method'));
            }

            return activeMethod;
        }

    });
});
