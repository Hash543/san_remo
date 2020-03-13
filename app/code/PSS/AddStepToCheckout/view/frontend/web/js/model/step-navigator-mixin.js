define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        return function (targetModule) {
            targetModule.getActiveItemIndex = function () {
                var activeIndex = 0;
                /*targetModule.steps.sort(this.sortItems).some(function (element, index) {

                    if (element.isVisible()) {
                        activeIndex = index;
                        if (activeIndex == 1) {
                            $('.opc-summary-wrapper').show();
                        } else {
                            $('.opc-summary-wrapper').hide();
                        }
                        return true;
                    }

                    return false;
                });*/
                targetModule.steps.sort(this.sortedItems).some(function (element, index) {
                    if (element.isVisible()) {
                        activeIndex = index;
                        if (activeIndex == 1) {
                            console.log('1');
                            $('.opc-summary-wrapper').show();
                        } else {
                            console.log('2');
                            $('.opc-summary-wrapper').hide();
                        }
                        return true;
                    }
                    return false;
                });
            };
            return targetModule;
        };
    }
);

