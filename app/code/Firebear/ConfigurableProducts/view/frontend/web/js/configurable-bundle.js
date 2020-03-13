/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';

    $.widget('mage.configurableBundle', {
        /**
         * @private
         */
        _init: function configureMultiOptions() {
            $('.bundle-selection-checkbox').each(function (key, option) {
                var elementId = $(option).attr('id');

                $('#' + elementId + '-qty-input').keyup(function () {
                    if ($('#' + elementId + '-qty-input').val() > 0) {
                        $('#' + elementId).prop('checked', true);
                    } else {
                        $('#' + elementId).prop('checked', false);
                    }

                    $('#' + elementId).trigger('change');
                });
            });

            $(document).ready(function () {
                $('.bundle-selection-qty').each(function () {
                    $(this).trigger('keyup');
                });
            });

            $('.bundle-selection-qty').on('keyup', function (key) {
                // Show custom options when its changed
                var qty = $(this).val();
                var toggleElement = $(this).attr('data-toggle');

                if (qty > 0) {
                    $(toggleElement).show();
                } else {
                    $(toggleElement).hide();
                }

                $('[name^="' + $(this).attr('data-options') + '"]').each(function (key, element) {
                    if ($(element).attr('is-required') == 'true' && qty > 0) {
                        $(element).attr('required', '');
                    } else {
                        $(element).removeAttr('required');
                    }
                });
            });
        }
    });

    return $.mage.configurableBundle;
});