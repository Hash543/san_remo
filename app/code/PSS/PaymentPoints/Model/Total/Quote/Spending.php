<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Model\Total\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Class Spending
 * @package PSS\PaymentPoints\Model\Total\Quote
 */
class Spending extends \Mageplaza\RewardPoints\Model\Total\Quote\Spending {
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
                    if($spendingTotal < $totalDiscount) {
                        $totalDiscount = $spendingTotal;
                        $totalBaseDiscount = $spendingTotal;
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
}