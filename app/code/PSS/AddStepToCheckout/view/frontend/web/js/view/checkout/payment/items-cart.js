define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';
    console.log(window.checkoutConfig.custom_config);
    return Component.extend({
        defaults: {
            template: 'PSS_AddStepToCheckout/checkout/payment/items-cart'
        },

        initialize: function () {
            var self = this;
            this._super();
        }
        // visibleCustomBlock: window.checkoutConfig.custom_config
    });
});