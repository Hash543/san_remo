define([
    'jquery',
    'ko',
    'mage/storage',
    'mage/url'
], function (
    $,
    ko,
    storage,
    urlBuilder,
) {
    'use strict';

    return {
        getStores: function () {
            let self = this;

            $.ajax({
                    url: urlBuilder.build('storeinfo/ajax/stores'),
                    type: 'POST',
                    async: false,
                    data: JSON.stringify(false),
                    global: false,
                    contentType: 'application/json'
                }
            ).done(function( response ) {
                /** Do your code here **/
                self.Stores = response;
            });

            return self;
        },

        getStock: function() {
            let data = window.dataLayer;
            let Id = data[0].ecommerce.detail.products[0].id;
            let qty = $('#product_addtocart_form select[name=qty]').val();

            let self = this;

            $.ajax({
                url: urlBuilder.build('rest/V1/stockexpress/getlist'),
                type: 'POST',
                async: false,
                data: JSON.stringify({productId: Id, quantity: qty}),
                global: true,
                contentType: 'application/json'
                }
            ).done(function( response ) {
                /** Do your code here **/
                if(Array.isArray(response)) {
                    self.Stock = response[0];
                } else {
                    self.Stock = false;
                }
            });

            return self;
        },

        getStockStores: function () {
            let self = this;

            self.StockStores = new Array();
            self.Stock = new Array();

            let Stores = ko.computed(ko.observable(self.getStores()));
            let Stock = ko.computed(ko.observable(self.getStock()));
            let StockStores = ko.computed(ko.observable());

            if(self.Stock) {
                self.Stock.forEach(
                    function (stock) {
                        if(stock) {
                            if(stock._value.c[1] === '1') { //Sólo consultamos la información de la tienda que tiene stock
                                self.getStoreInfo(stock._value.c[0]);
                            }
                        }
                    }
                )
            }
            return self.StockStores;
        },

        getStoreInfo: function (storeId) {
            let self = this;
            let item;

            if(storeId) {
                if (self.Stores) {
                    self.Stores.forEach(
                        function (storeValue) {
                            item = '';
                            if (parseInt(storeValue['id_sr']) === parseInt(storeId)) {
                                item = '<div><span>' + storeValue.name + '</span>, ' + '<span>' + storeValue.city + '</span></div>' ;
                                self.StockStores[storeId] = item;
                            }
                        }
                    )
                }
            }
        }
    };
});