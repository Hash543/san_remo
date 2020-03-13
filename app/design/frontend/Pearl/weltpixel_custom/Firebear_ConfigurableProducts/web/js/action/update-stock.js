/**
 * @api
 */
define([
    'jquery',
    'mage/storage'
], function ($, storage) {

    return function (productId) {
        var productStockContainer = $('.product-stock-wrapper');
        return storage.get(
            'configurable_ajax/ajax/productalert?productId='+productId,
            false
        ).done(
            function (response) {
                if(productStockContainer.length > 0) {
                    productStockContainer.html(response);
                }
            }
        );
    };
});