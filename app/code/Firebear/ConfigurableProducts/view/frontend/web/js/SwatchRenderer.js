/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Swatches/js/SwatchRenderer'
], function ($, _) {
    'use strict';

    $.widget('mage.SwatchRenderer', $.custom.SwatchRenderer, {

        /**
         * Emulate mouse click on all swatches that should be selected
         *
         * @private
         */
        _EmulateSelected: function () {
            var $widget = this,
                $this = $widget.element,
                request = $.parseParams(window.location.search.substring(1));

            if (typeof this.options.jsonConfig.defaultValues != 'undefined') {
                var values = this.options.jsonConfig.defaultValues;
                $.each(values, function (key, value) {
                    var $option = $this.find('.' + $widget.options.classes.attributeClass
                        + '[attribute-id="' + key + '"] [option-id="' + value + '"]');

                    if ($option.prop("tagName") == 'OPTION') {
                        $option.parent().val(value).trigger('change');
                    } else {
                        $option.trigger('click');
                    }
                });
            }

            $.each(request, function (key, value) {
                $this.find('.' + $widget.options.classes.attributeClass
                    + '[attribute-code="' + key + '"] [option-id="' + value + '"]').trigger('click');
            });
        },

        /**
         * Change product attributes.
         */
        _ReplaceData: function(simpleProductId, $widget) {
            if (typeof $widget.options.jsonConfig.customAttributes[simpleProductId] !== 'undefined') {
                $.each($widget.options.jsonConfig.customAttributes[simpleProductId], function(attributeCode, data) {
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
         * Event for swatch options
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnClick: function ($this, $widget) {

            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $this.addClass('selected');
            }

            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            /* Start Firebear code*/
            var products = $widget._CalcProducts();
            if (products.length) {
                var simpleProductId = products[0];
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);

                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function() {

                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = null;
                        if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                            title = config.customAttributes[simpleProductId].name.value;
                        }
                        History.replaceState(null, title, url);
                    }
                });
            }
            /* End Firebear code */

            $widget._LoadProductMedia();
        },

        /**
         * Event for select
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();

            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            /* Start Firebear code*/
            var products = $widget._CalcProducts();
            if (products.length) {
                var simpleProductId = products[0];
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);

                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function() {
                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = null;
                        if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                            title = config.customAttributes[simpleProductId].name.value;
                        }
                        History.replaceState(null, title, url);
                    }
                });
            }
            /* End Firebear code */

            $widget._LoadProductMedia();
        }
    });

    return $.mage.SwatchRenderer;
});
