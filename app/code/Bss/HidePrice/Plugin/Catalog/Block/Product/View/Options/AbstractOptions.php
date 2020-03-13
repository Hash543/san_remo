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
namespace Bss\HidePrice\Plugin\Catalog\Block\Product\View\Options;

class AbstractOptions
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
     * Hide option price html
     * @param $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetFormatedPrice(
        $subject,
        callable $proceed
    ) {        
        if ($this->helper->activeHidePrice($subject->getProduct())) {
            return '';
        }

        return $proceed();
    }

    public function afterGetValuesHtml($subject, $result)
    {
        $result = str_replace('<span class="price-notice">+</span>', '', $result);

        return $result;
    }
}
