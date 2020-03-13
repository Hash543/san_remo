/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_HidePrice
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
BssHidePrice = {
    method: {
        update: function (type) {

            index = "";
            selection = true;
            if (type == "swatch-attribute") {
                jQuery(".swatch-attribute").each(function () {

                    if (jQuery(this).find('.swatch-option.selected').length > 0)
                        index += jQuery(this).find('.swatch-option.selected').attr("option-id") + '_';
                });
            } else {
                jQuery(".super-attribute-select").each(function () {
                    option_id = jQuery(this).attr("option-selected");
                    if (typeof option_id === "undefined" && jQuery(this).val() !== "") {
                        option_id = jQuery(this).val()
                    }
                    if (option_id !== null && jQuery(this).val() !== "") {
                        index += option_id + '_';
                    } else {
                        selection = false;
                    }
                });
            }
            if (selection) {
                return index;
            }
        }

    },
};

define([
    'jquery',
    "mage/translate",
    "Magento_Swatches/js/swatch-renderer",
    "Magento_ConfigurableProduct/js/configurable"
], function (jQuery) {
    'use strict';

    jQuery.widget('bss.BssHidePrice', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
        },

        _init: function () {
            var $widget = this;
            jQuery(document).on("click", "*", function () {
                if(jQuery(this).prop("tagName")=="SELECT"){
                    var index = BssHidePrice.method.update("super-attribute-select");
                    if(index) {
                        $widget._Update(index);
                    }
                }
            });

            jQuery(document).on("click", ".swatch-option", function () {
                /* Only updates and shows price if configurable product is not Volume type */
                if (!jQuery(this).parents('.swatch-attribute.volumen').length) {
                    setTimeout(function () {
                        var index = BssHidePrice.method.update("swatch-attribute");
                        if(index) {
                            $widget._Update(index);
                        }
                    }, 100);
                }
            });
        },

        _Update: function (index) {
            var $widget = this,
                childProductData = this.options.jsonChildProduct;
            if (typeof childProductData !== "undefined" && !jQuery.isEmptyObject(childProductData)) {
                if (!childProductData['child'].hasOwnProperty(index)) {
                    $widget._ResetHidePrice();
                    return false;
                }
                $widget._UpdateHidePrice(
                    childProductData['child'][index]['hide_price'],
                    childProductData['child'][index]['hide_price_content'],
                    childProductData['child'][index]['show_price']
                );
            }
        },

        _UpdateHidePrice: function ($useAdvacedPrice, $content, $showPrice) {
            
            if (!$useAdvacedPrice) {
                jQuery('.price-box.price-final_price').css('display', 'block');
                jQuery('#product-addtocart-button').css('display', 'block');
                jQuery('#qty').parent().parent().css('display', 'block');
                jQuery('#product-addtocart-button').next().remove();
            } else {
                if($showPrice) {
                    jQuery('.price-box.price-final_price').css('display', 'none');
                }else {
                    jQuery('.price-box.price-final_price').css('display', 'block');
                }
                jQuery('#qty').parent().parent().css('display', 'none');
                jQuery('#product-addtocart-button').css('display', 'none');
                jQuery('#product-addtocart-button').next().remove();
                jQuery('#product-addtocart-button').parent().append($content);
            }
        },

        _ResetHidePrice: function () {
            jQuery('#qty').parent().parent().css('display', 'block');
            jQuery('.price-box.price-final_price').css('display', 'block');
            jQuery('#product-addtocart-button').css('display', 'block');
            jQuery('#product-addtocart-button').next().remove();
        },

    });

    return jQuery.bss.BssHidePrice;
});
