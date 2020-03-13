define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Conditions/js/action/recollect-totals',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address'
], function (ko, $, Component, quote, recollect, shippingService, shippingProcessor) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this.insertPolyfills();
            this._super();
            var prevBillAddress,
                prevShippingAddress,
                self = this;

            quote.shippingAddress.subscribe(function (newShippingAddress) {
                if (self.isVirtualQuote()
                    && (!newShippingAddress ^ !prevShippingAddress || newShippingAddress.getKey() !== prevShippingAddress.getKey())
                ) {
                    prevShippingAddress = newShippingAddress;
                    if (newShippingAddress) {
                        recollect();
                    }
                }
            });

            quote.billingAddress.subscribe(function(newBillAddress) {
                if (!newBillAddress ^ !prevBillAddress || newBillAddress.getKey() !== prevBillAddress.getKey()) {
                    prevBillAddress = newBillAddress;
                    if (newBillAddress) {
                        shippingProcessor.getRates(quote.billingAddress());
                    }
                }
                recollect();
            });

            shippingService.isLoading.subscribe(function(isLoading) {
                if (!isLoading && !self.isVirtualQuote()) {
                    recollect();
                }
            });

            quote.paymentMethod.subscribe(recollect);
            quote.shippingMethod.subscribe(recollect);

            return this;
        },

        isVirtualQuote: function () {
            return quote.isVirtual()
                || window.checkoutConfig.activeCarriers && window.checkoutConfig.activeCarriers.length === 0;
        },

        insertPolyfills: function () {
            if (typeof Object.assign != 'function') {
                // Must be writable: true, enumerable: false, configurable: true
                Object.defineProperty(Object, "assign", {
                    value: function assign(target, varArgs) { // .length of function is 2
                        'use strict';
                        if (target == null) { // TypeError if undefined or null
                            throw new TypeError('Cannot convert undefined or null to object');
                        }

                        var to = Object(target);

                        for (var index = 1; index < arguments.length; index++) {
                            var nextSource = arguments[index];

                            if (nextSource != null) { // Skip over if undefined or null
                                for (var nextKey in nextSource) {
                                    // Avoid bugs when hasOwnProperty is shadowed
                                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                        to[nextKey] = nextSource[nextKey];
                                    }
                                }
                            }
                        }
                        return to;
                    },
                    writable: true,
                    configurable: true
                });
            }
        }
    });
});
