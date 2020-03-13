define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/summary',
        'Magento_Checkout/js/model/step-navigator',
        'mage/url',
        'mage/validation'
    ],
    function(
        $,
        ko,
        Component,
        stepNavigator,
        url,
        validation
    ) {
        'use strict';

        return Component.extend({

            isVisible: function () {
                return stepNavigator.isProcessed('shipping');
            },
            initialize: function () {
                var self = this;
                this._super();
            },

            goToPrevStep: function()
            {
                stepNavigator.navigateTo('shipping');
            },

            goToKeepBuying: function () {
                window.location.replace(url.build(''));
            },
            submitOrder: function () {
                var dataAdditionalForm = $('#additional-options-form');
                if(dataAdditionalForm.validation() && dataAdditionalForm.validation('isValid')) {
                    $(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
                }
            },
            showSummaryBlock: function (element) {
                if(element.component === 'PSS_AddStepToCheckout/js/view/summary/additional-form') {
                    return false;
                } else {
                    return true;
                }
            }
        });
    }
);