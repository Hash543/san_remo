/**
 * @api
 */

define([
    'jquery',
    'Magento_Checkout/js/view/summary/shipping',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
], function ($, Component, quote, priceUtils) {
    'use strict';

    var displayMode = window.checkoutConfig.reviewShippingDisplayMode;

    return Component.extend({
        defaults: {
            displayMode: displayMode,
            template: 'Magento_Tax/checkout/summary/shipping'
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displayMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingDisplayed: function () {
            return this.displayMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isExcludingDisplayed: function () {
            return this.displayMode == 'excluding'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null;
        },

        /**
         * @return {*}
         */
        getIncludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }

            price = this.totals()['shipping_incl_tax'];

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*}
         */
        getExcludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }

            price = this.totals()['shipping_amount'];

            return this.getFormattedPrice(price);
        },
        /**
         * @return {*}
         */
        getValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            const totals = this.totals();
            if(totals['base_currency_code'] !== totals['quote_currency_code']) {
                price =  totals['base_shipping_amount'];
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            } else {
                price =  totals['shipping_amount'];
                return this.getFormattedPrice(price);
            }
        }
    });
});
