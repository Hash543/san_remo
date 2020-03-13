<?php
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
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Plugin;

use Magento\Catalog\Block\Product\View as MagentoView;

class HideButtonCart
{
    /**
     * @var \Bss\HidePrice\Helper\Data
     */
    private $helper;

    public function __construct(
        \Bss\HidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Hide Add to cart Button
     * @param MagentoView $subject
     * @param $result
     * @return string
     */
    public function afterToHtml(
        MagentoView $subject,
        $result
    ) {
        $matchedNames = [
            'product.info.addtocart.additional',
            'product.info.addtocart',
            'product.info.addtocart.bundle'
        ];

        if (in_array($subject->getNameInLayout(), $matchedNames)
            && $this->helper->activeHidePrice($subject->getProduct())
        ) {
            $product = $subject->getProduct();
            $result = '<h2 id="hideprice_text_'.$product->getId().'" class="hideprice_text">'. $this->helper->getHidepriceMessage($product).'</h2>';
        }

        return $result;
    }
}
