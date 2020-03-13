define([
    'Magento_SalesRule/js/view/summary/discount',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rules/summary/discount-breakdown'
        },

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            this.initCollapseBreakdown();
        },

        /**
         * getRules
         */
        getRules: function () {
            return this.amount.length ? this.amount : '';
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },

        /**
         * @override
         *
         * @returns {Boolean}
         */
        initCollapseBreakdown: function () {
            $(document).on('click', this.selector, function () {
                $(".total-rules").toggle();
                $(this).find('.title').toggleClass('collapsed');
            });
        },

        showDiscountArrow: function () {
            $('.totals .title').addClass('enabled');
        }
    });
});
