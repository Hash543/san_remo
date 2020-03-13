/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'Magento_Bundle/js/price-bundle'
], function ($, mageTemplate) {
    'use strict';

    /**
     * Widget product Summary:
     * Handles rendering of Bundle options and displays them in the Summary box
     */
    $.widget('mage.productSummary', {
        options          : {
            mainContainer         : '#product_addtocart_form',
            templates             : {
                summaryBlock: '[data-template="bundle-summary"]',
                optionBlock : '[data-template="bundle-option"]',
                superBlock  : '[data-template="bundle-super"]',
                customBlock : '[data-template="bundle-custom"]',
                priceBlock  : '[data-template="bundle-price"]'
            },
            optionSelector        : '[data-container="options"]',
            superSelector         : '[data-container="summary-super"]',
            customSelector        : '[data-container="summary-custom"]',
            priceSelector         : '[data-container="summary-price"]',
            summaryContainer      : '[data-container="product-summary"]',
            bundleSummaryContainer: '.bundle-summary',
            selectorProductPrice  : '[data-role=priceBox]'
        },
        cache            : {},
        /**
         * Method attaches event observer to the product form
         * @private
         */
        _create          : function () {
            this.element
                .closest(this.options.mainContainer)
                .on('updateProductSummary', $.proxy(this._renderSummaryBox, this))
                .priceBundle({})
            ;
        },
        /**
         * Method extracts data from the event and renders Summary box
         * using jQuery template mechanism
         * @param {Event} event
         * @param {Object} data
         * @private
         */
        _renderSummaryBox: function (event, data) {
            this.cache.currentElement = data.config;
            this.cache.currentElementCount = 0;

            // Clear Summary box
            this.element.html('');


            $.each(this.cache.currentElement.selected, $.proxy(this._renderOption, this));
            this.element
                .parents(this.options.bundleSummaryContainer)
                .toggleClass('empty', !this.cache.currentElementCount); // Zero elements equal '.empty' container
        },

        /**
         * @param {String} key
         * @param {String} row
         * @private
         */
        _renderOption: function (key, row) {
            var template;

            if (row && row.length > 0 && row[0] !== null) {
                template = this.element
                    .closest(this.options.summaryContainer)
                    .find(this.options.templates.summaryBlock)
                    .html();

                template = mageTemplate($.trim(template), {
                    data: {
                        _label_: this.cache.currentElement.options[key].title
                    }
                });

                this.cache.currentKey = key;
                this.cache.summaryContainer = $(template);
                this.element.append(this.cache.summaryContainer);

                $.each(row, this._renderOptionRow.bind(this));
                this.cache.currentElementCount += row.length;

                //Reset Cache
                this.cache.currentKey = null;
            }
        },

        _renderSuperOptions: function (key, currentOption, currentSelection) {
            var productForm = this.element.closest(this.options.mainContainer);
            var $widget = this;

            var superTemplate = this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.superBlock)
                .html();

            if (typeof(currentSelection.configurableOptions) != 'undefined') {
                $widget.cache.summaryContainer
                    .find($widget.options.superSelector)
                    .empty();

                $.each(currentSelection.configurableOptions, function (key, option) {
                    if (typeof(option.confAttributes) != 'undefined') {
                        $.each(option.confAttributes, function (key, attribute) {
                            var $input = productForm.find(
                                '[name="super_attribute[' + currentOption + '][' + attribute.id + ']"]'
                            );

                            var value = 'none';

                            if (typeof($input.val()) != 'undefined') {
                                if (typeof(option.confAttributes[attribute.id]) != 'undefined') {
                                    $.each(option.confAttributes[attribute.id].options, function (key, attributeValue) {
                                        if (attributeValue.id == $input.val()) {
                                            value = attributeValue.label;
                                        }
                                    });
                                }
                            }

                            var template = mageTemplate($.trim(superTemplate), {
                                data: {
                                    _label_: attribute.label,
                                    _value_: value
                                }
                            });

                            $widget.cache.summaryContainer
                                .find($widget.options.superSelector)
                                .append(template);
                        });
                    }
                });
            }
        },

        _renderPrice: function (key, currentOption, currentSelection) {
            var $widget = this;
            var subPriceTemplate = '0';

            var priceBox = this.element
                .closest(this.options.mainContainer)
                .find(this.options.selectorProductPrice);

            var priceTemplate = this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.priceBlock)
                .html();

            $widget.cache.summaryContainer
                .find($widget.options.priceSelector)
                .empty();

            if (typeof(currentSelection.configurableOptions) != 'undefined') {
                $.each(currentSelection.configurableOptions, function (key, option) {
                    var price = currentSelection.prices.finalPrice.amount * currentSelection.qty;

                    subPriceTemplate = mageTemplate($.trim(option.template), {
                        data: {
                            price: price
                        }
                    });
                });
            } else {
                if (currentSelection.prices) {
                    var price = currentSelection.prices.finalPrice.amount * currentSelection.qty;

                    subPriceTemplate = mageTemplate($.trim('<%- data.price %>'), {
                        data: {
                            price: price
                        }
                    });
                }

            }

            var currentElement = this.cache.currentElement.options[this.cache.currentKey];

            var optionId = currentOption;

            if (currentElement.isMulti == true) {
                optionId = currentOption + '-' + currentSelection.optionId;
            }

            var template = mageTemplate($.trim(priceTemplate), {
                data: {
                    _option_id_: optionId,
                    price      : subPriceTemplate
                }
            });

            if (currentElement.isMulti == true) {
                $widget.cache.summaryContainer
                    .find('[data-container="summary-price' + '-' + optionId +'"]')
                    .append(template);
            } else {
                $widget.cache.summaryContainer
                    .find($widget.options.priceSelector)
                    .append(template);
            }
        },

        /**
         * @param {String} key
         * @param {String} optionIndex
         * @private
         */
        _renderOptionRow: function (key, optionIndex) {
            var template,
                currentSelection,
                customTemplate;

            template = this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.optionBlock)
                .html();

            customTemplate = this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.customBlock)
                .html();

            currentSelection = this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex];
            var cacheCurrentKey = this.cache.currentKey;
            var currentOption = this.cache.currentElement.options[cacheCurrentKey];
            var isMulti = currentOption.isMulti;

            if (currentSelection.prices && isMulti) {
                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: currentSelection.qty,
                        _label_   : currentSelection.name,
                        _option_id_ : this.cache.currentKey + '-' + currentSelection.optionId,
                        is_multi: isMulti
                    }
                });
                this.cache.summaryContainer
                    .find(this.options.optionSelector)
                    .append(template);

                this._renderSuperOptions(key, this.cache.currentKey, currentSelection);
                this._renderPrice(key, this.cache.currentKey, currentSelection);
            } else {
                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: currentSelection.qty,
                        _label_   : currentSelection.name,
                    }
                });

                this.cache.summaryContainer
                    .find(this.options.optionSelector)
                    .append(template);

                this._renderSuperOptions(key, this.cache.currentKey, currentSelection);
                this._renderPrice(key, this.cache.currentKey, currentSelection);
            }
        }
    });

    return $.mage.productSummary;
});
