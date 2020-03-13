/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'jquery/ui',
    'Magento_ConfigurableProduct/js/configurable'
], function ($) {
    'use strict';

    $.widget('mage.configurable', $.mage.configurable, {

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            this._super();
            if (this.options.values) {
                var gallery = $(this.options.mediaGallerySelector),
                    $this = this;
                gallery.on('gallery:loaded', function () {
                    $this._changeProductImage();
                });
            }
            /*pre-selected configurable options*/
            // if (this.options.values) {
            //     this.options.settings.each($.proxy(function (index, element) {
            //         var attributeId = element.attributeId;
            //         element.value = this.options.values[attributeId] || '';
            //         if(!element.value){
            //             var attributeCode = this.options.spConfig.attributes[attributeId].code;
            //             var defaultValue = this.options.spConfig.defaultValues[attributeCode];
            //             $('#attribute'+attributeId).val(defaultValue).trigger('change');
            //         }
            //         this._configureElement(element);
            //     }, this));
            // }
            // localStorage.setItem('processed', '');
            // //pre-selected product options
            // if(!localStorage.getItem('processed')){
            //     var productId = this.simpleProduct;
            //     var config = this.options.spConfig;
            //     var currentURL = window.location.href;
            //     var simpleProductId = '';
            //     if (typeof config.urls !== 'undefined'){
            //         $.each(config.urls, function (productId, productUrl) {
            //             if(productUrl == currentURL){
            //                 simpleProductId = productId;
            //                 return true;
            //             }
            //         });
            //     }
            //     if(simpleProductId){
            //         $.each(config.attributes, function () {
            //             var item = this;
            //             var allOptions = item.options;
            //             $.each(allOptions, function (key, optionObj){
            //                 var products = optionObj.products;
            //                 for(var i = 0; i < products.length; i++){
            //                     var childProductId = optionObj.products[i];
            //                     if(simpleProductId == childProductId){
            //                        var selectedId = optionObj.id;
            //                         var select = $('#attribute'+item.id);
            //                         select.val(selectedId).trigger('change');
            //                     }
            //                 }
            //             });
            //         });
            //     }
            // }
            // localStorage.setItem('processed',true);
        },

        /**
         * Change product attributes.
         */
        _ReplaceData: function (simpleProductId, config) {
            if (typeof config.customAttributes[simpleProductId] !== 'undefined') {
                $.each(config.customAttributes[simpleProductId], function (attributeCode, data) {
                    var $block = $(data.class);

                    if (typeof data.replace != 'undefined' && data.replace) {
                        if (data.value == '') {
                            $block.remove();
                        }

                        if ($block.length > 0) {
                            $block.replaceWith(data.value);
                        } else {
                            $(data.container).html(data.value);
                        }
                    } else {
                        if ($block.length > 0) {
                            $block.html(data.value);
                        }
                    }
                });
            }
        },

        /**
         * @See \Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type\Configurable::getOptions()
         * @private
         */
        _changeProductImage: function () {
            this._super();
            var productId = this.simpleProduct;
            var config = this.options.spConfig;

            /**
             * Change product attributes.
             */
            this._ReplaceData(productId, config);

            /**
             * Change browser history URL
             */
            require(['jqueryHistory'], function () {
                if (typeof config.urls !== 'undefined' && typeof config.urls[productId] !== 'undefined') {
                    var url = config.urls[productId];
                    var title = null;
                    if (config.customAttributes[productId].name.value !== 'undefined') {
                        title = config.customAttributes[productId].name.value;
                    }
                    History.replaceState(null, title, url);
                }
            });
        }
    });

    return $.mage.configurable;
});
