/**
 * @override To substract the shipping amount from the totals of the cart
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/grand-total',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/quote'
], function (Component, totals, quote) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isDisplayed: function () {
            return true;
        },
        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;
            if (this.totals()) {
                price = totals.getSegment('grand_total').value;
            }
            if(this.isCalculated()) {
                const shippingAmount = this.totals()['shipping_amount'] ? this.totals()['shipping_amount'] : 0;
                price = price - shippingAmount;
            }
            return this.getFormattedPrice(price);
        },

        /**
         * @return {*|String}
         */
        getBaseValue: function () {
            var price = 0;

            if (this.totals()) {

                price = this.totals()['base_grand_total'];
            }
            if(this.isCalculated()) {
                const shippingAmount = this.totals()['shipping_amount'] ? this.totals()['shipping_amount'] : 0;
                price = price - shippingAmount;
            }
            return this.formatPrice(price);
        },
        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },
    });
});
