require([
    'jquery',
    'priceBox'
], function($) {

    $.widget('mage.ampromoPriceUpdater', {
        options: {
            productId: '',
            priceConfig: ''
        },

        _init: function () {
            var dataPriceBoxSelector = '.price-box-' + this.options.productId,
                dataProductIdSelector = '[data-product-id=' + this.options.productId + ']',
                priceBoxes = $(dataPriceBoxSelector + dataProductIdSelector);

            priceBoxes = priceBoxes.filter(function(index, elem){
                return !$(elem).find('.price-from').length;
            });

            priceBoxes.priceBox({
                'productId': this.options.productId,
                'priceConfig': this.options.priceConfig
            });
        }
    });
});
