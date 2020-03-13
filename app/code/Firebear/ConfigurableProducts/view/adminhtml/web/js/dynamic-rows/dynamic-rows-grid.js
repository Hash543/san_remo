define([
    'jquery',
    'ko',
    'underscore',
    'mage/translate',
    'Magento_Bundle/js/components/bundle-dynamic-rows-grid'
], function ($, ko, _,$t,dynamicRowsGrid) {
    'use strict';
    return dynamicRowsGrid.extend({

        /**
         * Initialize elements from grid
         *
         */
        initElements: function (data) {
            this._super();
            this.activeOrInactiveMultipleOption(data);

            return this;
        },

        activeOrInactiveMultipleOption: function(data) {
            var parentSelect = $('option[value=select]').closest('select');
            $.each(data, function(index, value) {
                if (value.productType == 'configurable') {
                    $('option[value=multi]').remove();
                    $(parentSelect).trigger('change');
                    return false;
                } else {
                    if($('option[value=multi]').length == 0) {
                        $(parentSelect).append('<option data-title="Multiple Select" value="multi">' + $.mage.__('Multiple Select') + '</option>');
                    }
                }
            });

        }
    });
});