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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Total\Invoice;

use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class ConvertSellPoints
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Total\Invoice
 */
class ConvertSellPoints
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\SellPoint
     */
    protected $sellPoint;

    /**
     * ConvertSellPoints constructor.
     *
     * @param SellPoint $sellPoint
     */
    public function __construct(SellPoint $sellPoint)
    {
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $subject
     */
    public function beforeCollectTotals(\Magento\Sales\Model\Order\Invoice $subject)
    {
        $this->sellPoint->setMpRewardSellPoints($subject->getItems());
    }
}
