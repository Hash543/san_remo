/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'jquery/ui',
    'mage/translate'
], function ($, confirm) {
    'use strict';

    $.widget('mage.delete', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this account?')
        },

        /**
         * Bind event handlers for adding and deleting addresses.
         * @private
         */
        _create: function () {
            var options         = this.options,
                deleteAccount   = options.deleteAccount;

            if (deleteAccount) {
                $(document).on('click', deleteAccount, this._deleteAccount.bind(this));
            }
        },

        /**
         * Delete the account whose id is specified in a data attribute after confirmation from the user.
         * @private
         * @param {jQuery.Event} e
         * @return {Boolean}
         */
        _deleteAccount: function (e) {
            var self = this;

            confirm({
                content: this.options.deleteConfirmMessage,
                actions: {

                    /** @inheritdoc */
                    confirm: function () {
                        if (typeof $(e.target).parent().data('delete') !== 'undefined') {
                            window.location = self.options.deleteUrlPrefix + $(e.target).parent().data('delete') +
                                '/form_key/' + $.mage.cookies.get('form_key');
                        } else {
                            window.location = self.options.deleteUrlPrefix + $(e.target).data('delete') +
                                '/form_key/' + $.mage.cookies.get('form_key');
                        }
                    }
                }
            });

            return false;
        }
    });

    return $.mage.delete;
});
