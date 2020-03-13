/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            configurable : 'Firebear_ConfigurableProducts/js/configurable',
            jqueryHistory: 'Firebear_ConfigurableProducts/js/jquery.history',

            productSummary    : 'Firebear_ConfigurableProducts/js/product-summary',
            priceBundle       : 'Firebear_ConfigurableProducts/js/price-bundle',
            configurableBundle: 'Firebear_ConfigurableProducts/js/configurable_bundle'
        }
    },
    config: {
        mixins: {
            'Magento_ProductVideo/js/fotorama-add-video-events': {
                'Firebear_ConfigurableProducts/js/fotorama-add-video-events-mixin': true
            }
        }
    }
};