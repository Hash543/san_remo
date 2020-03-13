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

namespace Mageplaza\RewardPoints\Model\Total\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Mageplaza\RewardPoints\Helper\Calculation;

/**
 * Class Spending
 * @package Mageplaza\RewardPoints\Model\Total\Quote
 */
class Spending extends AbstractTotal
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Mageplaza\RewardPoints\Helper\Calculation
     */
    protected $calculation;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Spending constructor.
     *
     * @param EventManager $eventManager
     * @param Session $checkoutSession
     * @param Calculation $calculation
     */
    public function __construct(
        EventManager $eventManager,
        Session $checkoutSession,
        Calculation $calculation
    ) {
        $this->_eventManager = $eventManager;
        $this->calculation = $calculation;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!($items = $shippingAssignment->getItems())) {
            return $this;
        }

        if (!$this->calculation->isRewardAccountActive()) {
            return $this;
        }

        $storeId = $quote->getStoreId();
        $pointSpent = (int) $quote->getMpRewardSpent();
        $ruleId = $quote->getMpRewardApplied();
        $this->calculation->resetRewardData(
            $items,
            $quote,
            [
                'mp_reward_base_discount',
                'mp_reward_discount',
                'mp_reward_shipping_base_discount',
                'mp_reward_shipping_discount'
            ],
            ['mp_reward_base_discount', 'mp_reward_discount', 'mp_reward_spent']
        );
        if ($this->calculation->isEnabled($storeId) || $ruleId) {
            if ($total->getBaseGrandTotal() <= 0) {
                $quote->setMpRewardSpent(0);

                return $this;
            }

            $this->_eventManager->dispatch('mpreward_spending_refer_points_before', [
                'quote'               => $quote,
                'items'               => $items,
                'total'               => $total,
                'shipping_assignment' => $shippingAssignment
            ]);

            $this->_eventManager->dispatch('mpreward_spending_points_before', [
                'quote'               => $quote,
                'items'               => $items,
                'total'               => $total,
                'shipping_assignment' => $shippingAssignment
            ]);

            if ($ruleId == 'rate' && $pointSpent) {
                $spendingRate = $this->calculation->getSpendingRateByQuote($quote);
                if (!$spendingRate->getId()) {
                    $this->calculation->addLocalizedException($quote);

                    return $this;
                }
                $pointSpent = min($pointSpent, $this->calculation->getMaxSpendingPointsByRate($quote, $spendingRate));

                if ($pointSpent) {
                    $totalPointSpent = 0;
                    $totalBaseDiscount = 0;
                    $totalDiscount = 0;
                    $lastItem = null;
                    $spendingTotal = $this->calculation->getSpendingTotal($quote, false, true);
                    $totalDiscountByRate = $spendingRate->getDiscountByPoint($pointSpent);
                    /** @var Quote\Item $item */
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }

                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            /** @var Quote\Item $child */
                            foreach ($item->getChildren() as $child) {
                                $this->calculateDiscount(
                                    $child,
                                    $pointSpent,
                                    $spendingTotal,
                                    $totalPointSpent,
                                    $totalBaseDiscount,
                                    $totalDiscount,
                                    $totalDiscountByRate
                                );
                                $lastItem = $child;
                            }
                        } else {
                            $this->calculateDiscount(
                                $item,
                                $pointSpent,
                                $spendingTotal,
                                $totalPointSpent,
                                $totalBaseDiscount,
                                $totalDiscount,
                                $totalDiscountByRate
                            );
                            $lastItem = $item;
                        }
                    }

                    if ($this->calculation->isSpendingOnShippingFee($quote->getStoreId())) {
                        $shippingSpent = $pointSpent - $totalPointSpent;
                        $shippingTotal = $this->calculation->getShippingTotalForDiscount($quote, false, true);
                        $baseShippingDiscount = ($shippingTotal / $spendingTotal) * $totalDiscountByRate;
                        $baseShippingDiscount = $this->calculation->roundPrice($baseShippingDiscount, 'base');
                        $shippingDiscount = $this->calculation->convertPrice(
                            $baseShippingDiscount,
                            false,
                            false,
                            $item->getStoreId()
                        );
                        $shippingDiscount = $this->calculation->roundPrice($shippingDiscount);
                        $totalBaseDiscount += $baseShippingDiscount;
                        $totalDiscount += $shippingDiscount;

                        $quote->setMpRewardShippingSpent($quote->getMpRewardShippingSpent() + $shippingSpent)
                            ->setMpRewardShippingBaseDiscount($quote->getMpRewardShippingBaseDiscount() + $baseShippingDiscount)
                            ->setMpRewardShippingDiscount($quote->getMpRewardShippingDiscount() + $shippingDiscount);
                        /**
                         * base mp shipping discount amount is  shipping discount amount of all Mageplaza extensions
                         * //->setBaseMpShippingDiscountAmount($baseShippingDiscount);
                         */
                    } else {
                        /**
                         * Rounding for last item
                         */
                        if ($pointSpent > $totalPointSpent && $lastItem) {
                            $lastItem->setMpRewardSpent($lastItem->getMpRewardSpent() + ($pointSpent - $totalPointSpent));
                        }
                    }

                    $quote->setMpRewardDiscount($quote->getMpRewardDiscount() + $totalDiscount)
                        ->setMpRewardBaseDiscount($quote->getMpRewardBaseDiscount() + $totalBaseDiscount);

                    /**
                     * base mp discount amount is discount amount of all Mageplaza extensions
                     * ->setBaseMpDiscountAmount($totalBaseDiscount);
                     */

                    $total->setBaseGrandTotal($total->getBaseGrandTotal() - $totalBaseDiscount);
                    $total->setGrandTotal($total->getGrandTotal() - $totalDiscount);
                    if ($total->getGrandTotal() < 0) {
                        $total->setBaseGrandTotal(0);
                        $total->setGrandTotal(0);
                    }
                }

                $quote->setMpRewardSpent($pointSpent);
            }
        }

        $this->_eventManager->dispatch('mpreward_spending_points_after', [
            'quote'               => $quote,
            'shipping_assignment' => $shippingAssignment,
            'total'               => $total
        ]);

        return $this;
    }

    /**
     * @param $item
     * @param $pointSpent
     * @param $spendingTotal
     * @param $totalPointSpent
     * @param $totalBaseDiscount
     * @param $totalDiscount
     * @param $totalDiscountByRate
     */
    protected function calculateDiscount(
        $item,
        $pointSpent,
        $spendingTotal,
        &$totalPointSpent,
        &$totalBaseDiscount,
        &$totalDiscount,
        $totalDiscountByRate
    ) {
        $itemTotal = $this->calculation->getItemTotalForDiscount($item, true, false, true);
        $itemPointSpent = ($itemTotal / $spendingTotal) * $pointSpent;
        $itemPointSpent = $this->calculation->deltaRoundPoint($itemPointSpent, 'spent_rate');
        $baseDiscount = ($itemTotal / $spendingTotal) * $totalDiscountByRate;

        $baseDiscount = $this->calculation->roundPrice($baseDiscount, 'base');
        $discount = $this->calculation->convertPrice($baseDiscount, false, false, $item->getStoreId());
        $discount = $this->calculation->roundPrice($discount);

        $totalPointSpent += $itemPointSpent;
        $totalBaseDiscount += $baseDiscount;
        $totalDiscount += $discount;
        $item->setMpRewardSpent($item->getMpRewardSpent() + $itemPointSpent)
            ->setMpRewardBaseDiscount($item->getMpRewardBaseDiscount() + $baseDiscount)
            ->setMpRewardDiscount($item->getMpRewardDiscount() + $discount);
        /**
         *  calculate all discount on Mageplaza extensions
         *  ->setBaseMpDiscountAmount($baseDiscount);
         */
    }

    /**
     * Retrieve reward total data
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return array|null
     */
    public function fetch(Quote $quote, Total $total)
    {
        $totals = [];
        if ($this->calculation->isEnabled() && $this->calculation->isRewardAccountActive()) {
            $discount = $quote->getMpRewardDiscount();
            if ($discount > 0.001) {
                $totals[] = [
                    'code'  => 'mp_reward_discount',
                    'title' => $this->calculation->getDiscountLabel($quote->getStoreId()),
                    'value' => -$discount,
                ];
            }

            $spent = $quote->getMpRewardSpent();
            if ($spent > 0.001) {
                $totals[] = [
                    'code'  => $this->getCode(),
                    'title' => __('You will spend'),
                    'value' => $spent,
                ];
            }
        }

        return $totals;
    }
}
