/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define(
    [
        'underscore',
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/totals',
        'Mageplaza_RewardPoints/js/model/resource-url-manager',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/quote',
        'Mageplaza_RewardPoints/js/model/points'
    ],
    function (_,
              $,
              storage,
              errorProcessor,
              totals,
              resourceUrlManager,
              customerData,
              getPaymentInformationAction,
              quote,
              pointsModel) {
        'use strict';

        return function (points, ruleId) {
            totals.isLoading(true);

            var payload = {
                points: points || 0,
                ruleId: ruleId || '',
                addressInformation: {}
            };
            payload.addressInformation = {address: _.pick(quote.shippingAddress(), ['countryId', 'region', 'regionId', 'postcode'])};
            if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
                payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
                payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
            }

            return storage.post(
                resourceUrlManager.getUrlForSpendingInformation(),
                JSON.stringify(payload)
            ).done(function (response) {
                quote.setTotals(response);
                if (!pointsModel.isCheckoutCart) {
                    var deferred = $.Deferred();
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        totals.isLoading(false);
                    });
                } else {
                    totals.isLoading(false);
                }

                // update cart data cache
                var cartData = customerData.get('cart-data')();
                cartData.totals = response;
                customerData.set('cart-data', cartData);

                // Update point earning on minicart sidebar
                var earningPoint = totals.getSegment('mp_reward_earn');
                if (earningPoint && earningPoint.value) {
                    earningPoint = pointsModel.format(earningPoint.value);

                    var cart = customerData.get('cart')();
                    cart.rewardEarn = earningPoint;
                    customerData.set('cart', cart);
                }
            }).fail(function (response) {
                errorProcessor.process(response);
                $("#mp-reward-message").html('<div class="message-error error message"><div>' + response.responseJSON.message + '</div></div>');
                setTimeout(function () {
                    $("#mp-reward-message .message-error").remove();
                }, 3000);
            }).always(function () {
                totals.isLoading(false);
            });
        };
    }
);
