/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        var consentInputPath = '.payment-method._active div.amasty-gdpr-consent input';

        return {
            /**
             * Validate checkout agreements
             *
             * @returns {boolean}
             */
            validate: function() {
                if (!gdprConfig.isEnabled) {
                    return true;
                }

                if (!$(consentInputPath).is(':visible')) {
                    return true;
                }
                var isValid = true;

                if (!$.validator.validateSingleElement($(consentInputPath)[0], {
                    errorElement: 'div',
                    errorClass: 'mage-error',
                    meta: 'validate',
                    errorPlacement: function (error, element) {
                        element.siblings('label').last().after(error);
                    }
                })) {
                    isValid = false;
                }

                return isValid;
            }
        }
    }
);
