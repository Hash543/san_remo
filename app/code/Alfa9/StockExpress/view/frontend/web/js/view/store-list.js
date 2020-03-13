define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'mage/url',
        'mage/storage',
        'mage/translate',
        'Alfa9_StockExpress/js/model/store-list',
    ],
    function(
        $,
        ko,
        Component,
        _,
        urlBuilder,
        storage,
        t,
        storeList,
    ){
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Alfa9_StockExpress/store-list'
            },

            isVisible: ko.observable(true),

            getList: function () {
                $('#stock-express.content').html(storeList.getStockStores()).show();
            },

            initialize: function () {
                this._super();
                var storesStringEmpty = $.mage.__("Express Delivery is not available at the moment");
                var storesStringFull = $.mage.__("Express Delivery is available in the following Stores");


                function updateList(){
                    let content = storeList.getStockStores();
                    let text = (content.length === 0) ? storesStringEmpty : storesStringFull;

                    $('.stock-express').text(text);
                    $('#stock-express.content').html(content).show();
                }

                updateList();

                $('#product_addtocart_form select[name=qty]').change(function() {
                    updateList()
                });
            },
        });
    }
);