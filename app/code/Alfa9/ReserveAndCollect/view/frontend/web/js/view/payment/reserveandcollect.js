define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'reserveandcollect',
                component: 'Alfa9_ReserveAndCollect/js/view/payment/method-renderer/reserveandcollect-method'
            }
        );
        return Component.extend({});
    }
);