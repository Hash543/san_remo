define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, Component, quote) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        return Component.extend({
            defaults: {
                template: 'Amasty_Gdpr/checkout/summary/gdpr-consent'
            },
            isVisible: ko.observable(gdprConfig.isVisible),
            checkboxText: gdprConfig.checkboxText,
            checkboxCount: 0,
            checkedGdprConsent: ko.observable(false),

            initialize: function () {
                this._super();
                this.checkedGdprConsent.subscribe(function (value) {
                    const checkboxInput = $('.payment-method .amasty-gdpr-consent input[type="hidden"]');
                    if(checkboxInput.length === 0){
                        return;
                    }
                    if(value) {
                        checkboxInput.val(1);
                    } else {
                        checkboxInput.val(0);
                    }
                });
                quote.billingAddress.subscribe(function (billingAddress) {
                    if (!billingAddress) {
                        return;
                    }
                    var country = billingAddress.countryId;

                    if (!country) {
                        return;
                    }

                    var isVisible = gdprConfig.isEnabled,
                        countryFilter = gdprConfig.visibleInCountries;

                    if (countryFilter) {
                        isVisible &= countryFilter.indexOf(country) !== -1;
                    }

                    this.isVisible(isVisible);
                }.bind(this));

                return this;
            },

            /**
             *
             * @return {string}
             */
            getId: function () {
                return 'amgdpr_agree_' + this.checkboxCount;
            },

            /**
             *
             * @return {string}
             */
            getNewId: function () {
                this.checkboxCount += 1;

                return 'amgdpr_agree_' + this.checkboxCount;
            },

            /**
             *
             * @return {string}
             */
            getTitle: function () {
                return $.mage.__('Accept privacy policy');
            },

            initModal: function (element) {
                $(element).find('a').click(function (e) {
                    e.preventDefault();
                    $('#amprivacy-popup').modal('openModal');
                })
            }
        });
    }
);
