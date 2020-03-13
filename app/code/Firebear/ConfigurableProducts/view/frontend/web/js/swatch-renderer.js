/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'Magento_Swatches/js/swatch-renderer',
    'jquery/ui'

], function ($, _, pricUtils) {
    'use strict';

    /* jQuery load event */
    $(document).ready(function () {
        localStorage.setItem('processed', '');
    });

    $.widget('mage.SwatchRenderer', $.mage.SwatchRenderer, {

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {

            if (typeof this.options.jsonConfig.defaultValues !== 'undefined') {
                return this.options.jsonConfig.defaultValues;
            }

            var hashIndex = window.location.href.indexOf('#'),
                selectedAttributes = {},
                params;

            if (hashIndex !== -1) {
                params = $.parseQuery(window.location.href.substr(hashIndex + 1));

                selectedAttributes = _.invert(_.mapObject(_.invert(params), function (attributeId) {
                    var attribute = this.options.jsonConfig.attributes[attributeId];

                    return attribute ? attribute.code : attributeId;
                }.bind(this)));
            }

            return selectedAttributes;
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            $('.description').append('<p class="firebear_custom_block1"></p><p class="firebear_custom_block2"></p><p class="firebear_custom_block3"></p><p class="firebear_custom_block4"></p>');
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                if ($widget.inProductList) {
                    $widget.productForm.append(input);
                    input = '';
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                    '" attribute-code="' + item.code +
                    '" attribute-id="' + item.id + '">' +
                    label +
                    '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                    options + select +
                    '</div>' + input +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price   : parseFloat(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelected: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                var el = this.element.find('.' + this.options.classes.attributeClass +
                    '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]');
                /*some websites use attribute-id instead of attribute-code*/
                if (el.length == 0)
                    el = this.element.find('.' + this.options.classes.attributeClass +
                        '[attribute-id="' + attributeCode + '"] [option-id="' + optionId + '"]');

                if (el.hasClass('selected')) {
                    return;
                }
                /*if swatch select option, use trigger change instead of click*/
                if (el.parent('select').hasClass('swatch-select'))
                    el.parent('select').val(optionId).trigger('change');
                else
                    el.trigger('click');
            }, this));
        },

        /**
         * Change product attributes.
         */
        _ReplaceData: function (simpleProductId, $widget) {
            if (typeof $widget.options.jsonConfig.customAttributes[simpleProductId] !== 'undefined') {
                $.each($widget.options.jsonConfig.customAttributes[simpleProductId], function (attributeCode, data) {
                    var $block = $(data.class);

                    if (typeof data.replace != 'undefined' && data.replace) {
                        if (data.value == '') {
                            $block.remove();
                        }

                        if ($block.length > 0 &&
                            attributeCode !== 'custom_1' &&
                            attributeCode !== 'custom_2' &&
                            attributeCode !== 'custom_3'
                        ) {
                            $block.replaceWith(data.value);
                        } else {
                            $(data.container).html(data.value);
                        }
                    } else {
                        if (attributeCode == 'custom_1' || attributeCode == 'custom_2' || attributeCode == 'custom_3') {
                            if ($('.custom_block_' + attributeCode).length == 0) {
                                $block.append('<br><span class="custom_block_' + attributeCode + '"></span>');
                            }
                            var customBlock = $('.custom_block_' + attributeCode);
                            customBlock.html(data.value)
                        }
                        else if ($block.length > 0) {
                            $block.html(data.value);
                        }
                    }
                });
            }
            var deliveryDateBlock = '.delivery_cpi_custom_block';
            if ($widget.options.jsonConfig.deliveryDate) {
                var today = new Date();
                today.setHours(0);
                today.setMinutes(0);
                today.setSeconds(0);
                today.setMilliseconds(0);
                today.setMicroseconds(0);
                var todatString = today.toString();
                today = today.valueOf();
                if ($(deliveryDateBlock).length == 0) {
                    $($widget.options.jsonConfig.deliveryDate.block).append('<span class="delivery_cpi_custom_block"></span>');
                }
                if ($widget.options.jsonConfig.deliveryDate[simpleProductId]) {
                    if (!$widget.options.jsonConfig.deliveryDate[simpleProductId].startdate) {
                        var startdateT = todatString;
                    }
                    else {
                        var startdateT = $widget.options.jsonConfig.deliveryDate[simpleProductId].startdate;
                    }
                    if ($widget.options.jsonConfig.deliveryDate[simpleProductId].enddate) {
                        var startdate = new Date(startdateT.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1')).valueOf(),
                            enddateT = $widget.options.jsonConfig.deliveryDate[simpleProductId].enddate,
                            enddate = new Date(enddateT.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1')).valueOf();
                        if (startdate <= today && enddate >= today) {
                            $(deliveryDateBlock).html('<br><span>' + $widget.options.jsonConfig.deliveryDate[simpleProductId].text + '</span>');
                        }
                    }
                }
                else {
                    if ($widget.options.jsonConfig.deliveryDate.parent) {
                        if (!$widget.options.jsonConfig.deliveryDate.parent.startdate) {
                            var startdateT = todatString;
                        }
                        else {
                            var startdateT = $widget.options.jsonConfig.deliveryDate.parent.startdate;
                        }
                        var startdate = new Date(startdateT.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1')).valueOf(),
                            enddateT = $widget.options.jsonConfig.deliveryDate.parent.enddate;
                        if (enddateT) {
                            var enddate = new Date(enddateT.replace(/(\d+)-(\d+)-(\d+)/, '$2/$3/$1')).valueOf();
                            if (startdate <= today && enddate >= today) {
                                $(deliveryDateBlock).html('<br><span>' + $widget.options.jsonConfig.deliveryDate.parent.text + '</span>');

                            }
                        }
                    }
                    else {
                        if ($(deliveryDateBlock).length != 0) {
                            $(deliveryDateBlock).remove();
                        }
                    }
                }
            }
        },

        _ReplaceDataParent: function ($widget) {
            if (typeof $widget.options.jsonConfig.customAttributes.parent !== 'undefined') {
                $.each($widget.options.jsonConfig.customAttributes.parent, function (attributeCode, data) {
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
                        if (attributeCode == 'custom_1' || attributeCode == 'custom_2' || attributeCode == 'custom_3') {
                            if ($('.custom_block_' + attributeCode).length == 0) {
                                $block.append('<br><span class="custom_block_' + attributeCode + '"></span>');
                            }
                            var customBlock = $('.custom_block_' + attributeCode);
                            customBlock.html(data.value)
                        }
                        else if ($block.length > 0) {
                            $block.html(data.value);
                        }
                    }
                });
            }
        },
        updateBaseImage   : function (images, context, isInProductView) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                imagesToUpdate,
                isInitial;

            if (isInProductView) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                isInitial = _.isEqual(imagesToUpdate, initialImages);

                try {
                    gallery.updateData(imagesToUpdate);
                    if (isInitial) {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    } else {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                            selectedOption   : this.getProduct(),
                            dataMergeStrategy: this.options.gallerySwitchStrategy
                        });
                    }
                    gallery.first();
                }
                catch (err) {
                    var imageObj = this;
                    var interval = window.setInterval(function () {
                        var gallery = context.find(imageObj.options.mediaGallerySelector).data('gallery');
                        if (gallery) {
                            gallery.updateData(imagesToUpdate);
                            if (isInitial) {
                                $(imageObj.options.mediaGallerySelector).AddFotoramaVideoEvents();
                            } else {
                                $(imageObj.options.mediaGallerySelector).AddFotoramaVideoEvents({
                                    selectedOption   : imageObj.getProduct(),
                                    dataMergeStrategy: imageObj.options.gallerySwitchStrategy
                                });
                            }
                            gallery.first();
                            clearInterval(interval);
                        }
                    }, 200);
                }

            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },
        /**
         * Event for swatch options
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnClick          : function ($this, $widget) {
            /* Fix issue cannot add product to cart */
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);
            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                if(typeof $widget.options.jsonConfig.allow_deselect_swatch == 'undefined')
                    return;
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                if (typeof $widget._toggleCheckedAttributes !== "undefined") {
                    $widget._toggleCheckedAttributes($this, $wrapper);
                }
            }
            /**
             * Get simpleProduct Id by simple product URL
             */
            var currentURL = window.location.href;
            var simpleProductId = '';
            if (!localStorage.getItem('processed')) {
                var selectedOptionId = '', selectedLabel = '';
                if (typeof this.options.jsonConfig.urls !== 'undefined') {
                    $.each(this.options.jsonConfig.urls, function (productId, productUrl) {
                        if (productUrl == currentURL) {
                            simpleProductId = productId;
                            return true;
                        }
                    });
                }
                if (simpleProductId) {
                    $.each(this.options.jsonConfig.attributes, function () {
                        var item = this;
                        var allOptions = item.options;
                        $.each(allOptions, function (key, optionObj) {
                            var products = optionObj.products;
                            for (var i = 0; i < products.length; i++) {
                                var childProductId = optionObj.products[i];
                                if (simpleProductId === childProductId) {
                                    selectedOptionId = optionObj.id;
                                    selectedLabel = optionObj.label;
                                    var select = $('div[attribute-id="' + item.id + '"]').find('select');
                                    if (select.find('option').length > 0) {
                                        select.val(selectedOptionId).trigger('change');
                                    } else {
                                        var parent = $('div[attribute-id="' + item.id + '"]'),
                                            label = parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                                            input = parent.find('.' + $widget.options.classes.attributeInput);
                                        parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                                        parent.attr('option-selected', selectedOptionId);
                                        label.text(selectedLabel);
                                        $('input[name="super_attribute[' + item.id + ']"]').val(selectedOptionId);
                                        $('.swatch-option[option-id=' + selectedOptionId + ']').addClass('selected');
                                    }
                                }
                            }
                        });
                    });
                }
            }

            $widget._Rebuild();
            if (typeof($widget.customOptionsPrice) !== 'undefined' ) {
                delete $widget.customOptionsPrice;
            }

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }
            else {
                if ($widget.inProductList) {
                    $widget._UpdatePriceCategory();
                    $widget._updateLink();
                }
            }


            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            var products = $widget._CalcProducts();
            
            /**
             * Do not replace data on category view page.
             * @see \Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type::afterGetJsonConfig()
             */
            if (products.length == 1 && !this.options.jsonConfig.doNotReplaceData) {
                if (!simpleProductId) {
                    var simpleProductId = products[0];
                }
                this._setOpenGraph(simpleProductId, $widget);
                this._RenderCustomOptionsBySimpleProduct(simpleProductId, $widget);
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);
                /* Update input type hidden - fix base image doesn't change when choose option */
                if (simpleProductId && document.getElementsByName('product').length) {
                    document.getElementsByName('product')[0].value = simpleProductId;
                }
                /**/
                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function () {

                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = $(document).find('title').text();
                        if (url) {
                            if (typeof config.customAttributes[simpleProductId].name == 'undefined') {
                                if (config.customAttributes[simpleProductId]['.breadcrumbs .items .product'].value !== 'undefined') {
                                    title = config.customAttributes[simpleProductId]['.breadcrumbs .items .product'].value;
                                }
                            }
                            else {
                                if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                                    title = config.customAttributes[simpleProductId].name.value;
                                }
                            }
                            History.replaceState(null, title, url);
                        }
                    }
                });
            }
            else {
                var configurableProductId = this.options.jsonConfig.productId;
                if(configurableProductId) {
                    this._setOpenGraph(configurableProductId, $widget);
                }
                if ($widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                ) {
                    $widget._ReplaceDataParent(this);
                    document.getElementsByName('product')[0].value = simpleProductId;
                }
            }
            $widget._loadMedia();
            $input.trigger('change');
            localStorage.setItem('processed', true);
        },

        /**
         * Event for select
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            /* Fix issue cannot add product to cart */
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);
            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }
            /**/

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }
            if (typeof($widget.customOptionsPrice) !== 'undefined' ) {
                delete $widget.customOptionsPrice;
            }

            $widget._Rebuild();
            $widget._UpdatePrice();

            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            var products = $widget._CalcProducts();
            var options = {};
            if (products.length == 0) {
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');

                    options[attributeId] = $(this).attr('option-selected');
                });

                var result = _.findKey($widget.options.jsonConfig.index, options);
                products = [result];
            }
            /**
             * Change From Price for normal swatch
             */
            this._ChangeFromToPrice(attributeId, $this.val(), $widget);
            /**
             * Do not replace data on category view page.
             * @see \Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type::afterGetJsonConfig()
             */
            if (products.length == 1 && !this.options.jsonConfig.doNotReplaceData) {
                var simpleProductId = products[0];
                this._RenderCustomOptionsBySimpleProduct(simpleProductId, $widget);
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);
                /**
                 * Update input type hidden - fix base image doesn't change when choose option
                 */
                if (simpleProductId && document.getElementsByName('product').length) {
                    document.getElementsByName('product')[0].value = simpleProductId;
                }
                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function () {
                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = $(document).find('title').text();
                        if (url) {
                            if (typeof config.customAttributes[simpleProductId].name == 'undefined') {
                                if (config.customAttributes[simpleProductId]['.breadcrumbs .items .product'].value !== 'undefined') {
                                    title = config.customAttributes[simpleProductId]['.breadcrumbs .items .product'].value;
                                }
                            }
                            else {
                                if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                                    title = config.customAttributes[simpleProductId].name.value;
                                }
                            }
                            History.replaceState(null, title, url);
                        }
                    }
                });
            }
            else {
                var configurableProductId = this.options.jsonConfig.productId;
                if(configurableProductId) {
                    this._setOpenGraph(configurableProductId, $widget);
                }
                if (!$widget.element.parents($widget.options.selectorProduct)
                        .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
                ) {
                    $widget._ReplaceDataParent(this);
                    document.getElementsByName('product')[0].value = '';
                }
            }

            $widget._loadMedia();

            $input.trigger('change');
        },

        _loadMedia: function (eventName) {
            var $main = this.inProductList ?
                    this.element.parents('.product-item-info') :
                    this.element.parents('.column.main'),
                images;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            }  else {
                images = this.options.jsonConfig.images[this.getProduct()];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }

                this.updateBaseImage(images.sort(this._comparePosition), $main, !this.inProductList, eventName);
            }
        },

        /**
         * Compare images position
         *
         * @private
         */
        _comparePosition: function (a,b) {
            if (parseInt(a.position )< parseInt(b.position))
                return -1;
            if (parseInt(a.position) > parseInt(b.position))
                return 1;
            return 0;
        },


        /**
         * Get human readable attribute code (eg. size, color) by it ID from configuration
         *
         * @param {Number} attributeId
         * @returns {*}
         * @private
         */
        _getAttributeCodeById: function (attributeId) {
            var attribute = this.options.jsonConfig.attributes[attributeId];

            return attribute ? attribute.code : attributeId;
        },

        /**
         * Update total price in category
         *
         * @private
         */
        _UpdatePriceCategory: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result,
                formatedPrice;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];
            if (result) {
                formatedPrice = pricUtils.formatPrice(result.finalPrice.amount, {
                    "pattern"          : $widget.options.jsonConfig.currencyFormat,
                    "precision"        : 2,
                    "requiredPrecision": 2,
                    "decimalSymbol"    : ".",
                    "groupSymbol"      : ",",
                    "groupLength"      : 3,
                    "integerRequired"  : 1
                });
                if ($widget.options.jsonConfig.defaultPriceWithRange && $widget.options.jsonConfig.priceRange) {
                    if (typeof $($product.find('.price-range')).html() !== 'undefined') {
                        if (-1 < $($product.find('.price-range')).html().indexOf('From')) {
                            if ($widget.options.jsonConfig.disaplyingFromToPrice) {
                                $($product.find('.price')[2]).html(formatedPrice);
                            }
                            else {
                                $($product.find('.price')[1]).html(formatedPrice);
                            }
                        }
                        else {
                            $($product.find('.price')[0]).html(formatedPrice);
                        }
                    }
                }
                else {
                    if (!$widget.options.jsonConfig.priceRange) {
                        $productPrice.html('<span class="price">' + formatedPrice + '</span>');
                    }
                }
                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice)
                    }
                );
            }


            if (typeof result !== 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },


        _updateLink: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;
            var deselectAll = true;	
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
                if($(this).attr('option-selected'))
                    deselectAll = false;
            });
            var linkSelectedOption = $widget.options.jsonConfig.urls[_.findKey($widget.options.jsonConfig.index, options)];
            if (_.findKey($widget.options.jsonConfig.index, options)) {
                if (linkSelectedOption) {
                    if (typeof $widget.options.jsonConfig.customAttributes[_.findKey($widget.options.jsonConfig.index, options)].name == 'undefined') {
                        $product.find('.product-item-link').attr('href', linkSelectedOption).html($widget.options.jsonConfig.customAttributes[_.findKey($widget.options.jsonConfig.index, options)]['.breadcrumbs .items .product'].value);
                    }
                    else {
                        $product.find('.product-item-link').attr('href', linkSelectedOption).html($widget.options.jsonConfig.customAttributes[_.findKey($widget.options.jsonConfig.index, options)].name.value);
                    }
                }
            }
            if(deselectAll){
                linkSelectedOption = $widget.options.jsonConfig.customAttributes.parent.parent_link;
                $product.find('.product-item-link').attr('href', linkSelectedOption).html($widget.options.jsonConfig.customAttributes.parent.name.value);
            }
            $($product.prev('.product-item-photo')).find('a').attr('href', linkSelectedOption);
        },

        _ChangeFromToPrice: function (attributeId, optionId, $widget) {
            var productsIds = [];
            var childProductIds = [];
            var productOptions = 0;
            var allSelectedOptionPrices = [];
            var minPrice = 0;
            var maxPrice = 0;
            var iteratorForCheckMinPrice = 0;
            var selectedAttributesForProducts = [];
            var iteratorForProductIds = 0;
            
            $.each($widget.options.jsonConfig.attributes, function ($key, $item) {
                if($('.'+$item.code+' option:selected').val() != 0 && $item.id != attributeId){
                    selectedAttributesForProducts[$item.id] = parseInt($('.'+$item.code+' option:selected').val());
                }
            });
            if(typeof $widget.options.jsonSwatchConfig !== 'undefined'){
                $.each($widget.options.jsonConfig.mappedAttributes[attributeId].options, function ($key, $item) {
                    if (optionId == 0) {
                        productOptions = 1;
                        // childProductIds = productsIds.concat($item.products);
                        childProductIds.push($item.products);
                    } else {
                        if ($item.id == optionId) {
                            productsIds[iteratorForProductIds] = $item.products;
                            iteratorForProductIds++;
                        }    
                    }
                });
                if (selectedAttributesForProducts.length != 0) {
                    selectedAttributesForProducts.forEach(function (item, i, selectedAttributesForProducts) {
                        $.each($widget.options.jsonConfig.mappedAttributes[i].options, function ($key, $item) {
                            if ($item.id == item) {
                                productsIds[iteratorForProductIds] = $item.products;
                                iteratorForProductIds++;
                            }
                        });
                    });
                    /*$.each(selectedAttributesForProducts, function ($key, $item) {

                    });*/
                }
            }
            if (productsIds.length != 0) {
                    $.each(productsIds, function ($key, $item) {
                       $.each($item, function ($id, $val) {
                            if ($widget.options.jsonConfig.optionPrices[$val].finalPrice.amount != 0) {
                                allSelectedOptionPrices[iteratorForCheckMinPrice] = $widget.options.jsonConfig.optionPrices[$val].finalPrice.amount;
                                iteratorForCheckMinPrice++;
                            }
                        });
                    });
                
                minPrice = Math.min.apply(Math, allSelectedOptionPrices);
                maxPrice = Math.max.apply(Math, allSelectedOptionPrices);
            
            $('.firebear_range_price').html('From ' + $widget.getFormattedPrice(minPrice,$widget) +' - ' + $widget.getFormattedPrice(maxPrice,$widget));
            }
            else
            {
                $('.firebear_range_price').html($('.price').html);
            }
        }, 

        getFormattedPrice: function (price, $widget) {
            //todo add format data
            return pricUtils.formatPrice(price, $widget.options.jsonConfig.priceFormat);
        },

        _RenderCustomOptionsBySimpleProduct: function (productId, $widget) {
            $.ajax({
                url       : $widget.options.jsonConfig.loadOptionsUrl,
                type      : 'POST',
                dataType  : 'json',
                showLoader: true,
                data      : {
                    productId: productId
                },
                success   : function (response, widget) {

                    if (!$('.product-options-wrapper .product-cpi-custom-options').html()) {
                        $('.product-options-wrapper').append('<div class="product-cpi-custom-options"></div>');
                        $('.product-cpi-custom-options').html('<div class="fieldset" tabindex="0">'+response.optionsHtml+'</div>');
                    }
                    else {
                        $('.product-cpi-custom-options').html('<div class="fieldset" tabindex="0">'+response.optionsHtml+'</div>');
                    }
                    $('.product-custom-option').on('change', function() {
                        var customOptionsPrice = [];
                        function getSum(total, num) {
                            return total + num;
                        }

                        $('.product-custom-option').each(function(key, el) {
                            var elementType = el.nodeName;
                            switch(elementType) {
                                case "INPUT":
                                    var inputType = $(el).attr('type');
                                    if (inputType == 'radio' || inputType == 'checkbox') {
                                        if (el.checked) {
                                            customOptionsPrice.push(parseFloat($(el).attr('price')));
                                        }
                                    }
                                    break;
                                case "SELECT":
                                    if (el.multiple) {
                                        $(el).find(":selected").each(function(index, selected) {
                                            customOptionsPrice.push(parseFloat($(selected).attr('price')));
                                        });
                                        break;
                                    } else {
                                        var singleSelectPrice = $(el).find(":selected").attr('price');
                                        if (typeof(singleSelectPrice) !== 'undefined') {
                                            customOptionsPrice.push(parseFloat(singleSelectPrice));
                                        } else {
                                            customOptionsPrice.push(0);
                                        }
                                        break;
                                    }
                            }
                        });
                        if (customOptionsPrice.length > 0) {
                            $widget['customOptionsPrice'] = customOptionsPrice.reduce(getSum);
                        }
                        $widget._UpdatePrice();
                    });
                }
            });
        },
        _setOpenGraph: function (productId, $widget) {
            $.ajax({
                url       : $widget.options.jsonConfig.setOpenGraphUrl,
                type      : 'POST',
                dataType  : 'json',
                data      : {
                    productId: productId,
                },
                success   : function (response) {
                    var property;
                    $.each($(response.openGraphHtml), function(){
                        property = $(this).attr('property');
                        if(property) {
                            $('meta[property="' + property + '"]').remove();
                        }

                    });
                    $('head').append(response.openGraphHtml);
                }
            });
        },

        /**
         * Update total price in product page
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            if(typeof($widget['resultBefore_' + _.findKey($widget.options.jsonConfig.index, options)]) == 'undefined') {
                $widget['resultBefore_' + _.findKey($widget.options.jsonConfig.index, options)] = JSON.parse(JSON.stringify(result));
            } else {
                result = null;
                result = JSON.parse(JSON.stringify($widget['resultBefore_' + _.findKey($widget.options.jsonConfig.index, options)]));
            }
            if (typeof ($widget.customOptionsPrice) !== 'undefined') {
                result.finalPrice.amount = result.finalPrice.amount + $widget.customOptionsPrice;
            }
            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

            if (typeof result !== 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },
    });

    return $.mage.SwatchRenderer;
});
