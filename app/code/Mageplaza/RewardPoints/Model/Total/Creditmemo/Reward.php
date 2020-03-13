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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Model\Total\Creditmemo;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class Reward
 * @package Mageplaza\RewardPoints\Model\Total\Creditmemo
 */
class Reward extends AbstractTotal
{
    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Mageplaza\RewardPoints\Helper\Calculation
     */
    protected $calculation;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Item
     */
    protected $invoiceItem;

    /**
     * Reward constructor.
     *
     * @param \Mageplaza\RewardPoints\Helper\Data $helperData
     * @param \Mageplaza\RewardPoints\Helper\Calculation $calculation
     * @param \Magento\Sales\Model\Order\Invoice\Item $invoiceItem
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        Calculation $calculation,
        InvoiceItem $invoiceItem,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->calculation = $calculation;
        $this->invoiceItem = $invoiceItem;

        parent::__construct($data);
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setMpRewardEarn(0);
        $creditmemo->setMpRewardBaseDiscount(0);
        $creditmemo->setMpRewardDiscount(0);
        $order = $creditmemo->getOrder();
        $mpRewardEarnAfterInvoice = (bool) $order->getMpRewardEarnAfterInvoice();

        if ((!$order->getMpRewardEarn() && !$order->getMpRewardSpent())) {
            return $this;
        }

        $totalEarningPoint = 0;
        $totalSpendingPoint = 0;
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        $isRefundPointEarn = $this->helperData->getPointHelper()->isRefundPointsEarn($order->getStoreId());
        $isRefundPointSpent = $this->helperData->getPointHelper()->isRestorePointAfterRefund($order->getStoreId());
        $isAddRewardShippingEarn = false;
        $itemCreditmemo = $this->calculation->getOldRewardData(
            $order->getCreditmemosCollection(),
            ['mp_reward_earn', 'mp_reward_spent', 'mp_reward_base_discount', 'mp_reward_discount'],
            $isAddRewardShippingEarn
        );

        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }

            if (!$mpRewardEarnAfterInvoice) {
                $rewardItem = $this->getItemRewardData($orderItem, $orderItem->getMpRewardSpent());
            } else {
                $invoiceItem = $this->invoiceItem->load($item->getOrderItemId(), 'order_item_id');
                $rewardItem = $this->getItemRewardData($invoiceItem, $orderItem->getMpRewardSpent());
            }

            /**
             * Calculate point earn
             */
            if ($this->canRefundEarn(
                $order,
                $isRefundPointEarn,
                $mpRewardEarnAfterInvoice
            ) && $rewardItem->getMpRewardEarn()) {
                $itemEarn = $this->calculatePoint('mp_reward_earn', $item, $orderItem, $rewardItem, $itemCreditmemo);
                $item->setMpRewardEarn($itemEarn);
                $totalEarningPoint += $itemEarn;
            }

            /**
             * Calculate point spent
             */
            if ($isRefundPointSpent && $rewardItem->getMpRewardSpent()) {
                $itemSpent = $this->calculatePoint('mp_reward_spent', $item, $orderItem, $rewardItem, $itemCreditmemo);
                $item->setMpRewardSpent($itemSpent);
                $totalSpendingPoint += $itemSpent;
            }

            /**
             * Calculate discount
             */
            if ($rewardItem->getMpRewardDiscount() > 0) {
                $itemDiscount = $creditmemo->roundPrice(
                    ($rewardItem->getMpRewardDiscount() * $item->getQty()) / $rewardItem->getQty(),
                    'regular',
                    true
                );
                $itemBaseDiscount = $creditmemo->roundPrice(
                    ($rewardItem->getMpRewardBaseDiscount() * $item->getQty()) / $rewardItem->getQty(),
                    'base',
                    true
                );
                $item->setMpRewardDiscount($itemDiscount);
                $item->setMpRewardBaseDiscount($itemBaseDiscount);
                $totalDiscountAmount += $itemDiscount;
                $baseTotalDiscountAmount += $itemBaseDiscount;
            }
        }

        /**
         * Calculate reward shipping(earn, spent, discount)
         */
        if ($creditmemo->getShippingAmount()) {
            if (abs(($creditmemo->getShippingAmount() + $order->getShippingRefunded()) - $order->getShippingAmount()) < 0.00001) {
                if ($this->canRefundEarn($order, $isRefundPointEarn, $mpRewardEarnAfterInvoice)) {
                    $totalEarningPoint += $order->getMpRewardShippingEarn();
                }
                if ($isRefundPointSpent) {
                    $totalSpendingPoint += $order->getMpRewardShippingSpent();
                }
                $totalDiscountAmount += $order->getMpRewardShippingDiscount();
                $baseTotalDiscountAmount += $order->getMpRewardShippingBaseDiscount();
            }
        }

        $creditmemo->setMpRewardEarn($totalEarningPoint);
        $creditmemo->setMpRewardSpent($totalSpendingPoint);
        $creditmemo->setMpRewardDiscount($totalDiscountAmount);
        $creditmemo->setMpRewardBaseDiscount($baseTotalDiscountAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
        if ($creditmemo->getGrandTotal() == 0) {
            if ($totalDiscountAmount > 0 || $totalEarningPoint > 0 || $totalSpendingPoint > 0) {
                $creditmemo->setAllowZeroGrandTotal(true);
            }
        }

        return $this;
    }

    /**
     * @param $field
     * @param $item
     * @param $orderItem
     * @param $rewardItem
     * @param $itemCreditmemo
     *
     * @return int
     */
    public function calculatePoint($field, $item, $orderItem, $rewardItem, $itemCreditmemo)
    {
        if ($item->getQty() + $orderItem->getQtyRefunded() == $orderItem->getQtyOrdered()) {
            $point = $orderItem->getData($field);
            if (isset($itemCreditmemo[$orderItem->getId()])) {
                $point -= $itemCreditmemo[$orderItem->getId()][$field];
            }
        } else {
            $point = floor(($rewardItem->getDate($field) * $item->getQty()) / $rewardItem->getQty());
        }

        return $point;
    }

    /**
     * @param $order
     * @param $isRefundPointEarn
     * @param $mpRewardEarnAfterInvoice
     *
     * @return bool
     */
    public function canRefundEarn($order, $isRefundPointEarn, $mpRewardEarnAfterInvoice)
    {
        return $isRefundPointEarn &&
               ($mpRewardEarnAfterInvoice ||
                (!$mpRewardEarnAfterInvoice && ($order->getState() == Order::STATE_COMPLETE))
               );
    }

    /**
     * @param $item
     * @param $itemSpent
     *
     * @return \Magento\Framework\DataObject
     */
    public function getItemRewardData($item, $itemSpent)
    {
        $rewardData = new \Magento\Framework\DataObject(
            [
                'qty'                     => 0,
                'mp_reward_earn'          => 0,
                'mp_reward_spent'         => 0,
                'mp_reward_base_discount' => 0,
                'mp_reward_discount'      => 0
            ]
        );
        if ($item->getId()) {
            $qty = $item instanceof Item ? $item->getQtyOrdered() : $item->getQty();
            $rewardData->setQty($qty)
                ->setMpRewardEarn($item->getMpRewardEarn())
                ->setMpRewardSpent($itemSpent)
                ->setMpRewardBaseDiscount($item->getMpRewardBaseDiscount())
                ->setMpRewardDiscount($item->getMpRewardDiscount());
        }

        return $rewardData;
    }
}
