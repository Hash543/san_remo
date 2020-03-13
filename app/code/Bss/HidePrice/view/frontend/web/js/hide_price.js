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
define([
    'jquery'
], function ($) {
    "use strict";
    return function (config, element) {
        var selector = config.selector;
        var outofStockSelector = '.stock.unavailable';
        if(config.showPrice){
            $(element).parents('.product-info-main').find('.price-box.price-final_price').hide();
        }
        if($(element).attr('id')) {
            var related = "#" + $(element).attr('id').replace("hideprice_text_","related-checkbox");
            $(element).parent().find(related).remove();
        }
        hidePrice('.action.tocart', $(element), 0);
        if(selector != "") {
            hidePrice(selector, $(element), 0);
        }
        function hidePrice(sel, el, count) {
            if(el.parent().find(sel).length > 0) {
                el.parent().find(sel).parent().html(element);
            }else {
                count++;
                if(count < 3) {
                    el = el.parent();
                    hidePrice(sel, el, count);
                }
            }
        }
    }
});
