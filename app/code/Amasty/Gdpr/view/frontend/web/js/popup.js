define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return function (config, element) {
        config.buttons = [
            {
                text: $.mage.__('I have read and accept'),
                'class': 'action action-primary',
                click: function () {
                    var checkbox = $('[data-role="amasty-gdpr-consent"] input[type="checkbox"]');
                    checkbox.prop('checked', true);
                    checkbox.trigger('change');
                    this.closeModal();
                }
            }
        ];
        var popup = modal(config, element);
        $('[data-role="amasty-gdpr-consent"] a').on('click', function () {
            popup.openModal();
            $('#amprivacy-popup').closest('.modal-popup').css('z-index', 100001);
            $('.modals-overlay').css('z-index', 100000);
            return false;
        });
    };
});
