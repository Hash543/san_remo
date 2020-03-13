<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin;

/**
 * Class Topmenu
 * @package Mageplaza\Shopbybrand\Plugin
 */
class Topmenu
{
    /**
     * @param \Magento\Theme\Block\Html\Topmenu $topMenu
     * @param $html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topMenu, $html)
    {
        $brandHtml = $topMenu->getLayout()->createBlock('Mageplaza\Shopbybrand\Block\Link\CategoryMenu')->toHtml();

        return $html . $brandHtml;
    }
}
