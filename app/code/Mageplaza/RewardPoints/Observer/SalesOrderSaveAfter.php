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
 * Class SalesOrderSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperData;

    /**
     * SalesOrderSaveAfter constructor.
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();
        $pointAmount = $this->helperData->getCalculationHelper()
            ->calculatePointOrderCompleteByAction($order, HelperData::ACTION_EARNING_ORDER);
        if ($pointAmount) {
            $this->helperData->addTransaction(
                HelperData::ACTION_EARNING_ORDER,
                $order->getCustomerId(),
                $pointAmount,
                $order
            );
        }
    }
}
