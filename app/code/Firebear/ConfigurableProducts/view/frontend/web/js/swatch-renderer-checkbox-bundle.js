/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'Magento_Bundle/js/price-bundle'
], function ($, _, mageTemplate) {
    'use strict';

    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('mage.SwatchRendererTooltip', {
        options: {
            delay       : 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value'),
                $image,
                $title,
                $corner;

            if (!$element.size()) {
                $element = $('<div class="' +
                    $widget.options.tooltipClass +
                    '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>');
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;

                            if (type === 2) {
                                // Image
                                $image.css({
                                    'background'     : 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });
                                $image.show();
                            } else if (type === 1) {
                                // Color
                                $image.css({
                                    background: value
                                });
                                $image.show();
                            } else if (type === 0 || type === 3) {
                                // Default
                                $image.hide();
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top : $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });
            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
    $.widget('mage.SwatchRenderer', {
        options: {
            classes: {
                attributeClass                   : 'swatch-attribute',
                attributeLabelClass              : 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper          : 'swatch-attribute-options',
                attributeInput                   : 'swatch-input',
                optionClass                      : 'swatch-option',
                selectClass                      : 'swatch-select',
                moreButton                       : 'swatch-more',
                loader                           : 'swatch-option-loading'
            },

            // option's json config
            multiJsonConfig: {},

            // swatch's json config
            multiJsonSwatchConfig: {},

            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // Preselected product configuration
            productConfiguration: {},

            selectedProduct: 0,

            // Get the custom option html
            customOptions: {},

            // Id of the selected option
            optionId: 1,

            productSelector: '',

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            optionsSelector: '.product-custom-option',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // text for more button
            moreButtonText: 'More',

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [],

            //
            onlyMainImg: false
        },

        /**
         * Get chosen product
         *
         * @returns array
         */
        getProduct: function () {
            return this._CalcProducts().shift();
        },

        /**
         * @private
         */
        _init: function () {
            /*if (this.options.jsonConfig !== '') {
                this._sortAttributes();
                //this._RenderControls();
            } else {
                console.log('SwatchRenderer: No input data received');
            }*/

            var $widget = this;

            // Select the  MAIN products by clicking on their image
            $($widget.options.productSelector).change(function (e) {
                $widget.element.empty();

                if ($(this).val() != null) {
                    $widget.options.jsonConfig = $widget.options.multiJsonConfig[$(this).val()];
                    $widget.options.jsonSwatchConfig = $widget.options.multiJsonSwatchConfig[$(this).val()];
                    $widget.options.selectedProduct = $(this).val();
                    $widget._EventListener();

                    if ($widget.options.jsonConfig) {
                        $widget._sortAttributes();
                        $widget._RenderControls();
                    } else {
                        $widget._RenderCustomOptions();
                        $widget._UpdatePrice();
                    }
                }
            });

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
                $('.input-text.qty').each(function () {
                    if ($(this).val() > 0) {
                        $(this).trigger('keyup');
                    }
                });
            });
        },

        /**
         * @private
         */
        _sortAttributes: function () {
            if (typeof(this.options.jsonConfig) != 'undefined') {
                this.options.jsonConfig.attributes = _.sortBy(this.options.jsonConfig.attributes, function (attribute) {
                    return attribute.position;
                });
            }
        },

        /**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (isProductViewExist) {
                gallery.on('gallery:loaded', function () {
                    var galleryObject = gallery.data('gallery');

                    if (options.mediaGalleryInitial.length == 0) {
                        options.mediaGalleryInitial = galleryObject.returnCurrentImages();
                    }
                });
            } else {
                if (options.mediaGalleryInitial.length == 0) {
                    options.mediaGalleryInitial = [{
                        'img': $main.find('.product-image-photo').attr('src')
                    }];
                }
            }
            this.productForm = this.element.parents(this.options.selectorProduct).find('form:first');
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            var isChecked = $('.bundle-option-' + this.options.optionId + '-' + this.options.jsonConfig.productId).prop('checked');

            if (isChecked !== false) {
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
                            '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>';
                    }

                    $('[data-role="bundle-option-description-' + $widget.options.optionId + '"]').append(
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>'
                    );

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
                        if (this.products.length > 0 && $widget.options.jsonConfig.optionPrices[this.products[0]]) {
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
            }

            $widget._RenderCustomOptions();

            $widget.productForm.find(this.options.optionsSelector).trigger('change');

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

            // Preset selected qtys
            if ($widget.options.productConfiguration['bundle_option_qty']) {
                $.each($widget.options.productConfiguration['bundle_option_qty'], function (optionId, qty) {
                    if (optionId == $widget.options.optionId) {
                        $('[name="bundle_option_qty[' + optionId + ']"]')
                            .val(qty)
                            .change();
                    }
                });
            }

            // Preset selected attributes
            if ($widget.options.productConfiguration['super_attribute']) {
                $.each($widget.options.productConfiguration['super_attribute'], function (optionId, attribute) {
                    if (optionId == $widget.options.optionId) {
                        $.each(attribute, function (attributeKey, attributeValue) {
                            $('[data-role="swatch-options-' + optionId + '"]')
                                .find('div[attribute-id="' + attributeKey + '"]')
                                .find('div[option-id="' + attributeValue + '"]')
                                .click();
                        });
                    }
                });
            }

            // Preset selected custom optoins
            if ($widget.options.productConfiguration['bundle_custom_options']) {
                $.each($widget.options.productConfiguration['bundle_custom_options'], function (optionId, customOption) {
                    if (optionId == $widget.options.optionId) {
                        $.each(customOption, function (customOptionKey, customOptionValue) {
                            $('[name="bundle_custom_options[' + optionId + '][' + customOptionKey + ']"]')
                                .val(customOptionValue)
                                .change();
                        });
                    }
                });
            }
        },

        _RenderCustomOptions: function () {
            var $widget = this;

            var $customOptionInput = $widget.productForm.find('.product-custom-options-' + $widget.options.optionId);
            $customOptionInput.empty();
            $customOptionInput.append($widget.options.customOptions[$widget.options.selectedProduct]);
        },

        /**
         * Render swatch options by part of config
         *
         * @param {Object} config
         * @returns {String}
         * @private
         */
        _RenderSwatchOptions: function (config) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function () {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    attr;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                label = this.label ? this.label : '';
                attr =
                    ' option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        '" style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Defaualt
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        /**
         * Render select by part of config
         *
         * @param {Object} config
         * @param {String} chooseText
         * @returns {String}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            var html;
            var attributes = '';
            var requiredClass = '';
            var requiredData = '';

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            var qty = $('#bundle-option-' + this.options.optionId + '-' + this.options.jsonConfig.productId + '-qty-input').val();

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                attributes = 'name="super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + config.id + ']"';
                requiredData = 'data-selector="' + 'super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + config.id + ']"';

            }

            if (qty > 0) {
                requiredData += 'data-validate="{required:true}" ' + 'aria-required="true" ';
                requiredClass = '';
            }

            html =
                '<select class="super-attribute-select ' +
                this.options.classes.selectClass +
                ' ' +
                config.code +
                requiredClass +
                '" ' +
                requiredData +
                'attribute-id="' +
                config.id +
                '"' +
                attributes +
                '>' +
                '<option value="" option-id="0">' + chooseText + '</option>';

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        /**
         * Input for submit form.
         * This control shouldn't have "type=hidden", "display: none" for validation work :(
         *
         * @param {Object} config
         * @private
         */
        _RenderFormInput: function (config) {
            var requiredClass = '';
            var requiredData = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            var qty = $('#bundle-option-' + this.options.optionId + '-' + this.options.jsonConfig.productId + '-qty-input').val();

            if (qty > 0) {
                requiredClass = 'required';
            }

            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select ' +
                requiredClass + '" ' +
                'name="super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + config.id + ']" ' +
                requiredData +
                'style="visibility: hidden; position:absolute; left:-1000px"><input type="hidden" name="only_for_checkbox_bundle[' + this.options.optionId + ']['+this.options.jsonConfig.productId+']">';
        },

        /**
         * Trigger anything which needs to happen if Qt changes
         * @private
         */
        _OnQtyChange: function ($this, $widget) {
            var qty = $this.val();
            var toggleElement = $this.attr('data-toggle');

            if (qty == 0) {
                $(toggleElement).hide();
                //$('[name="bundle_option[' + this.options.optionId + ']"]').val(0).change();
                $('[name^="super_attribute[' + this.options.optionId + ']"]').removeAttr('required');
                $('[name^="bundle_custom_options[' + this.options.optionId + ']"]').removeAttr('required');
                $('[name^="bundle_custom_options[' + this.options.optionId + ']"]').removeClass('required');
                $('[name^="bundle_custom_options[' + this.options.optionId + ']"]').removeAttr('aria-required');
            } else {
                $(toggleElement).show();
                $('[name^="super_attribute[' + this.options.optionId + ']"]').attr('required', '');
                $('[name^="bundle_custom_options[' + this.options.optionId + ']"]').attr('required', '');
            }

            $('[name^="super_attribute[' + this.options.optionId + ']"]').removeAttr('aria-invalid');
            $('[name^="super_attribute[' + this.options.optionId + ']"]').removeAttr('aria-describedby');
            $('[name^="super_attribute[' + this.options.optionId + ']"]').removeClass('mage-error');
            $('[for^="super_attribute[' + this.options.optionId + ']"]').remove();

            $widget._UpdatePrice();
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function (e) {
                $widget._OnClick($(this), $widget);

                if (e.originalEvent) {
                    $widget.productForm.find('.selected').trigger('click').trigger('click');
                }
            });

            $widget.element.on('change', '.' + this.options.classes.selectClass, function (e) {
                $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            $('[name^="bundle_option_qty"]').on('keyup', function (e) {
                $widget._OnQtyChange($(this), $widget);
            });
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {

            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.children('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + attributeId + ']"]'
                );

            if ($this.hasClass('disabled')) {
                return;
            }

            $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
            $label.text($this.attr('option-label'));
            $input.val($this.attr('option-id'));
            $this.addClass('selected');

            // Let the system know the summary is changed
            var checkBox = $widget.productForm.find('input[name="summary-changed"]');
            checkBox.attr("checked", !checkBox.attr("checked"));
            // checkBox.trigger('change');

            $widget._Rebuild();

            $widget._UpdatePrice();

            $input.trigger('change');
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + this.options.optionId + ']['+this.options.jsonConfig.productId+'][' + attributeId + ']"]'
                );

            if ($this.val() > 0) {
                $input.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();

            var checkBox = $widget.productForm.find('input[name="summary-changed"]');
            checkBox.attr("checked", !checkBox.attr("checked"));
            // checkBox.trigger('change');

            $widget._UpdatePrice();

            $input.trigger('change');
        },

        /**
         * Event for more switcher
         *
         * @param {Object} $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },

        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {

            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.size() === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                    }
                });
            });
        },

        /**
         * Get selected product list
         *
         * @returns {Array}
         * @private
         */
        _CalcProducts: function ($skipAttributeId) {
            var $widget = this,
                products = [];

            // Generate intersection of products
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var id = $(this).attr('attribute-id'),
                    option = $(this).attr('option-selected');

                if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length === 0) {
                    products = $widget.optionsMap[id][option].products;
                } else {
                    products = _.intersection(products, $widget.optionsMap[id][option].products);
                }
            });

            return products;
        },

        _FetchMediaGallery: function () {
            var products = {};
            var $widget = this;
            var $this = $widget.element;

            $('[name^="bundle_option\["]').each(function (key, option) {
                var product = {};
                $(option).parent().parent().children('.swatch-opt').each(function (key, element) {
                    $('[data-role=' + $(element).attr('data-role') + ']').find('.swatch-attribute').each(function (key, subelement) {
                        if ($(subelement).attr('option-selected')) {
                            product[$(subelement).attr('attribute-id')] = $(subelement).attr('option-selected');
                        }
                    });

                    $('[data-role=' + $(element).attr('data-role') + ']').find('.swatch-select').each(function (key, subelement) {
                        if ($(subelement).val()) {
                            product[$(subelement).attr('attribute-id')] = $(subelement).val();
                        }
                    });
                });

                products[$(option).val()] = product;
            });

            // Do the AJAX call
            $widget._XhrKiller();
            $widget._EnableProductMediaLoader($this);

            jQuery.ajax({
                type    : "POST",
                url     : "/cb/gallery",
                data    : $widget.productForm.serialize(),
                dataType: 'json',
                success : function (data) {
                    var isProductViewExist = $('body.catalog-product-view').size() > 0;
                    var $main = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info');
                    var gallery = $main.find($widget.options.mediaGallerySelector).data('gallery');
                    var images = [];

                    if (gallery) {
                        if ($widget.options.mediaGalleryInitial.length == 0) {
                            $widget.options.mediaGalleryInitial = gallery.returnCurrentImages();
                        }

                        $.each($widget.options.mediaGalleryInitial, function () {
                            images.push({
                                full : this.full,
                                img  : this.img,
                                thumb: this.thumb
                            });
                        });

                        $.each(data, function () {
                            images.push({
                                full : this.large,
                                img  : this.medium,
                                thumb: this.small
                            });
                        });

                        gallery.updateData(images);
                        $($widget.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    }

                    $widget._DisableProductMediaLoader($this);
                    $widget._XhrKiller();
                }
            });
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            var el = $('[name^="bundle_option[' + $widget.options.optionId + '][' + $widget.options.jsonConfig.productId + ']"');

            $(el).each(function (key, element) {
                var selection = $(element).val();

                if (selection && $widget.options.multiJsonConfig[selection]) {
                    var selectionData = $widget.options.multiJsonConfig[selection];

                    if (selectionData.prices) {
                        var prices = {};
                        var qty = $('#bundle-option-' + $widget.options.optionId + '-' + $widget.options.jsonConfig.productId + '-qty-input').val();

                        var result = jQuery.extend(true, {}, selectionData.prices);
                        result.finalPrice.amount = selectionData.prices.finalPrice.amount * qty;

                        prices['bundle-option-' + $(element).attr('name')] = result;
                        if ($product.find('[data-role=bundle-summary-price-' + $widget.options.optionId + '-' + selectionData['productId'] + ']')) {
                            $product.find('[data-role=bundle-summary-price-' + $widget.options.optionId + '-' +  selectionData['productId'] + ']').innerHTML = mageTemplate($.trim(selectionData.template), {
                                data: {
                                    price: result.finalPrice.amount
                                }
                            });
                        }
                    }
                }
            });

            $product.find('[data-role="swatch-options-' + $widget.options.optionId + '-' + $widget.options.jsonConfig.productId + '"]').each(function (key, element) {
                var optionData = {};

                $(element).children('.swatch-attribute').each(function (key, subelement) {
                    if ($(subelement).attr('option-selected')) {
                        optionData[$(subelement).attr('attribute-id')] = $(subelement).attr('option-selected');
                    }
                });

                $(element).find('.swatch-select').each(function (key, subelement) {
                    if ($(subelement).val()) {
                        optionData[$(subelement).attr('attribute-id')] = $(subelement).val();
                    }
                });

                if (Object.keys(optionData).length > 0) {
                    var refResult = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, optionData)];

                    var result = jQuery.extend(true, {}, refResult);

                    if (result.finalPrice) {
                        // Add custom options price
                        if (typeof(result) != 'undefined' && $widget.options.isFixedPrice == 0) {
                            var prices = {};
                            var qty = $('#bundle-option-' + $widget.options.optionId + '-' + $widget.options.jsonConfig.productId + '-qty-input').val();

                            result.finalPrice.amount = refResult.finalPrice.amount * qty;
                            prices['bundle-option-bundle_option[' + $widget.options.optionId + '][' + $widget.options.jsonConfig.productId + ']' + '##' + $widget.options.selectedProduct] = result;

                            var subPriceTemplate = mageTemplate($.trim($widget.options.jsonConfig.template), {
                                data: {
                                    price: result.finalPrice.amount
                                }
                            });

                            if ($product.find('[data-role=bundle-summary-price-' + $widget.options.optionId + '-' + $widget.options.jsonConfig.productId + ']')[0]) {
                                $product.find('[data-role=bundle-summary-price-' + $widget.options.optionId + '-' + $widget.options.jsonConfig.productId + ']')[0].innerHTML = subPriceTemplate;

                                $productPrice.trigger(
                                    'updatePrice',
                                    prices
                                );
                            }
                        }
                    }
                }
            });
        },

        /**
         * Get prices
         *
         * @param {Object} newPrices
         * @param {Object} displayPrices
         * @returns {*}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            var $widget = this;

            if (_.isEmpty(newPrices)) {
                newPrices = $widget.options.jsonConfig.prices;
            }

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                attributes = {},
                productId = 0,
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data);
                    $widget._DisableProductMediaLoader($this);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            $this.find('[option-selected]').each(function () {
                var $selected = $(this);

                attributes[$selected.attr('attribute-code')] = $selected.attr('option-selected');
            });

            if ($('body.catalog-product-view').size() > 0) {
                //Product Page
                productId = document.getElementsByName('product')[0].value;
            } else {
                //Category View
                productId = $this.parents('.product.details.product-item-details')
                    .find('.price-box.price-final_price').attr('data-product-id');
            }

            mediaCallData = {
                'product_id': productId,
                'attributes': attributes,
                'additional': $.parseQuery()
            };
            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.post(
                    $widget.options.mediaCallback,
                    mediaCallData,
                    mediaSuccessCallback,
                    'json'
                ).done(function () {
                    $widget._XhrKiller();
                });
            }
        },

        /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .addClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .addClass($widget.options.classes.loader);
            }
        },

        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .removeClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .removeClass($widget.options.classes.loader);
            }
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {
            var isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isProductViewExist);

                return;
            }

            images.push({
                full  : response.large,
                img   : response.medium,
                thumb : response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full : this.large,
                        img  : this.medium,
                        thumb: this.small
                    });
                });
            }

            this.updateBaseImage(images, $main, isProductViewExist);
        },

        /**
         * Check if images to update are initial and set their type
         * @param {Array} images
         */
        _setImageType: function (images) {
            var initial = this.options.mediaGalleryInitial[0].img;

            if (images[0].img === initial) {
                images = $.extend(true, [], this.options.mediaGalleryInitial);
            } else {
                images.map(function (img) {
                    img.type = 'image';
                });
            }

            return images;
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isProductViewExist
         */
        updateBaseImage: function (images, context, isProductViewExist) {
            var justAnImage = images[0],
                updateImg,
                imagesToUpdate,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                item;

            if (isProductViewExist) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];

                if (this.options.onlyMainImg) {
                    updateImg = imagesToUpdate.filter(function (img) {
                        return img.isMain;
                    });
                    item = updateImg.length ? updateImg[0] : imagesToUpdate[0];
                    gallery.updateDataByIndex(0, item);

                    gallery.seek(1);
                } else {
                    gallery.updateData(imagesToUpdate);
                    $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                }
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },

        /**
         * Kill doubled AJAX requests
         *
         * @private
         */
        _XhrKiller: function () {
            var $widget = this;

            if ($widget.xhr !== undefined && $widget.xhr !== null) {
                $widget.xhr.abort();
                $widget.xhr = null;
            }
        },

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {
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
        }
    });

    return $.mage.SwatchRenderer;
});