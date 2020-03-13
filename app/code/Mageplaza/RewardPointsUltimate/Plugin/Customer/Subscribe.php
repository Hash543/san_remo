<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Customer;

use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class Subscribe
 * @package Mageplaza\RewardPointsUltimate\Plugin\Customer
 */
class Subscribe
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * Subscribe constructor.
     *
     * @param \Mageplaza\RewardPointsUltimate\Helper\Data $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Newsletter\Block\Subscribe $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Newsletter\Block\Subscribe $subject, $result)
    {
        $pointContent = $this->helperData->getSubscribePointHtml(
            [
                'result' => $result,
                'query'  => '//div[@class="block newsletter"]'
            ]
        );

        if ($pointContent) {
            return $pointContent;
        }

        return $result;
    }
}
