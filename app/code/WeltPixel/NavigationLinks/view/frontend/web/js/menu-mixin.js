define(['jquery'], function($) {
    'use strict';

    return function(navigationMenu) {
        $.widget('mage.menu', navigationMenu.menu, {
            options: {
                mediaBreakpoint: '(max-width: ' + window.widthThreshold + 'px)'
            }
        });
        return $.mage.menu;
    }
});