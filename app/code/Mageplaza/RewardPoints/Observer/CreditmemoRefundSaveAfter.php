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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class CreditmemoRefundSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class CreditmemoRefundSaveAfter implements ObserverInterface
{
    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperData;

    /**
     * CreditmemoRefundSaveAfter constructor.
     *
     * @param \Mageplaza\RewardPoints\Helper\Data $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if ($creditmemo->getMpRewardEarn() > 0) {
            $this->helperData->addTransaction(
                HelperData::ACTION_EARNING_REFUND,
                $creditmemo->getOrder()->getCustomerId(),
                -$creditmemo->getMpRewardEarn(),
                $creditmemo->getOrder()
            );
        }

        if ($creditmemo->getMpRewardSpent() > 0) {
            $this->helperData->addTransaction(
                HelperData::ACTION_SPENDING_REFUND,
                $creditmemo->getOrder()->getCustomerId(),
                $creditmemo->getMpRewardSpent(),
                $creditmemo->getOrder()
            );
        }
    }
}
