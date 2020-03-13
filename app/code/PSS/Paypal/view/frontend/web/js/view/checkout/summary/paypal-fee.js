/**
 * @author Israel Yasis
 */
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'knockout',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'PSS_Paypal/js/action/set-payment-and-update-totals',
        'mage/translate',
    ],
    function (Component, ko, quote, priceUtils, totals, setPaymentAndUpdateTotalsAction, $t) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'PSS_Paypal/checkout/summary/paypal-fee',
                title: $t('PayPal Fee'),
                value: ko.observable(0.0),
                shouldDisplay: ko.observable(false)
            },
            initialize: function() {
                this._super();

                quote.paymentMethod.subscribe(function(newPaymentMethod) {
                    setPaymentAndUpdateTotalsAction(newPaymentMethod)
                });
                quote.totals.subscribe((function (newTotals) {
                    this.value(this.getFormattedTotalValue(newTotals));
                    this.shouldDisplay(this.isTotalDisplayed(newTotals));
                }).bind(this));
            },
            isTotalDisplayed: function(totals) {
                return this.getTotalValue(totals) > 0;
            },
            getTotalValue: function(totals) {
                if (typeof totals.total_segments === 'undefined' || !totals.total_segments instanceof Array) {
                    return 0.0;
                }
                return totals.total_segments.reduce(function (paypalFeeTotalValue, currentTotal) {
                    return currentTotal.code === 'paypal_fee_amount' ? currentTotal.value : paypalFeeTotalValue
                }, 0.0);
            },
            getFormattedTotalValue: function(totals) {
                const price = this.getTotalValue(totals);
                const priceFormat = quote.getPriceFormat();
                if( priceFormat.pattern.indexOf("SRP") !== -1) {
                    return this.getFormattedPrice(price);
                } else {
                    return priceUtils.formatPrice(price, quote.getBasePriceFormat());
                }
            }
        });
    }
);
