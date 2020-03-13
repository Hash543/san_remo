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
                template: 'Amasty_Gdpr/checkout/gdpr-consent'
            },
            isVisible: ko.observable(gdprConfig.isVisible),
            checkboxText: gdprConfig.checkboxText,
            checkboxCount: 0,

            initialize: function () {
                this._super();

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
