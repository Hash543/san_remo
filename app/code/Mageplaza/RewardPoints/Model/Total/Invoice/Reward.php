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

namespace Mageplaza\RewardPoints\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Mageplaza\RewardPoints\Helper\Calculation;

/**
 * Class Reward
 * @package Mageplaza\RewardPoints\Model\Total\Invoice
 */
class Reward extends AbstractTotal
{
    /**
     * @var \Mageplaza\RewardPoints\Helper\Calculation
     */
    protected $calculation;

    /**
     * Reward constructor.
     *
     * @param \Mageplaza\RewardPoints\Helper\Calculation $calculation
     * @param array $data
     */
    public function __construct(
        Calculation $calculation,
        array $data = []
    ) {
        $this->calculation = $calculation;

        parent::__construct($data);
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setMpRewardEarn(0);
        $invoice->setMpRewardBaseDiscount(0);
        $invoice->setMpRewardDiscount(0);

        $totalEarningPoint = 0;
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        $order = $invoice->getOrder();
        $canEarnAfterInvoice = (bool) $order->getMpRewardEarnAfterInvoice();
        $addShippingDiscount = true;
        $fields = ['earn', 'discount', 'base_discount'];
        $itemInvoice = $this->calculation->getOldRewardData(
            $order->getInvoiceCollection(),
            $fields,
            $addShippingDiscount
        );

        /**
         * Calculate reward shipping(earn,discount)
         */
        if ($addShippingDiscount) {
            $totalEarningPoint += $canEarnAfterInvoice ? $order->getMpRewardShippingEarn() : 0;
            $totalDiscountAmount += $order->getMpRewardShippingDiscount();
            $baseTotalDiscountAmount += $order->getMpRewardShippingBaseDiscount();
        }

        /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemQty) {
                $this->calculateRewardDiscount(
                    $invoice,
                    $orderItem,
                    $item,
                    $itemInvoice,
                    $totalDiscountAmount,
                    $baseTotalDiscountAmount
                );
                $totalEarningPoint += $this->calculateRewardEarn(
                    $item,
                    $canEarnAfterInvoice,
                    $orderItem,
                    $itemInvoice,
                    $orderItemQty
                );
            }
        }

        $invoice->setMpRewardEarn($totalEarningPoint);
        $invoice->setMpRewardDiscount($totalDiscountAmount);
        $invoice->setMpRewardBaseDiscount($baseTotalDiscountAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);

        return $this;
    }

    /**
     * @param $item
     * @param $canEarnAfterInvoice
     * @param $orderItem
     * @param $itemInvoice
     * @param $orderItemQty
     *
     * @return float|int
     */
    public function calculateRewardEarn($item, $canEarnAfterInvoice, $orderItem, $itemInvoice, $orderItemQty)
    {
        $orderItemEarn = (int) $orderItem->getMpRewardEarn();
        $totalEarningPoint = 0;

        if ($canEarnAfterInvoice && $orderItemEarn) {
            $orderItemId = $orderItem->getId();
            $itemEarn = $orderItemEarn - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['earn'] : 0);
            if (!$item->isLast()) {
                $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                $itemEarn = floor($itemEarn / $activeQty * $item->getQty());
            }

            $item->setMpRewardEarn($itemEarn);

            $totalEarningPoint += $itemEarn;
        }

        return $totalEarningPoint;
    }

    /**
     * @param $invoice
     * @param $orderItem
     * @param $item
     * @param $itemInvoice
     * @param $totalDiscountAmount
     * @param $baseTotalDiscountAmount
     */
    public function calculateRewardDiscount(
        $invoice,
        $orderItem,
        $item,
        $itemInvoice,
        &$totalDiscountAmount,
        &$baseTotalDiscountAmount
    ) {
        $orderItemDiscount = (double) $orderItem->getMpRewardDiscount();
        $baseOrderItemDiscount = (double) $orderItem->getMpRewardBaseDiscount();
        $orderItemQty = $orderItem->getQtyOrdered();
        $orderItemId = $orderItem->getId();
        if ($orderItemDiscount) {
            $discount = $orderItemDiscount - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['discount'] : 0);
            $baseDiscount = $baseOrderItemDiscount - (isset($itemInvoice[$orderItemId]) ? $itemInvoice[$orderItemId]['base_discount'] : 0);
            if (!$item->isLast()) {
                $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                $discount = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
            }

            $item->setMpRewardDiscount($discount);
            $item->setMpRewardBaseDiscount($baseDiscount);

            $totalDiscountAmount += $discount;
            $baseTotalDiscountAmount += $baseDiscount;
        }
    }
}
