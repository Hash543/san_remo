<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

class SetofFixed extends Setof
{
    const RULE_VERSION = '1.0.0';

    public static $cachedDiscount = [];
    public static $allItems;

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data Data
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule, $item, $qty);

        if (!isset(self::$allItems)) {
            self::$allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        }

        $discountData = $this->calculateDiscount($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param $rule
     * @param $item
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    protected function calculateDiscount($rule, $item)
    {
        $ruleId = $this->getRuleId($rule);

        if (!array_key_exists($ruleId, self::$cachedDiscount)) {
            $this->calculateDiscountForRule($rule, $item);
        }

        $discountData = isset(self::$cachedDiscount[$ruleId][$item->getId()])
            ? self::$cachedDiscount[$ruleId][$item->getId()]
            : $this->discountFactory->create();

        return $discountData;
    }

    /**
     * @param $rule
     * @param $item
     *
     * @return $this
     */
    protected function calculateDiscountForRule($rule, $item)
    {
        list($setQty,$itemsForSet) = $this->prepareDataForCalculation($rule);

        if (!$itemsForSet) {
            return $this;
        }

        $totalPrice = $this->getItemsPrice($itemsForSet);
        $quoteAmount = $setQty * $rule->getDiscountAmount();

        if ($totalPrice < $quoteAmount) {
            return $this;
        }

        $countItemsForDiscount = count($itemsForSet);
        $this->calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $countItemsForDiscount, $quoteAmount);

        foreach ($itemsForSet as $i => $item) {
            unset(self::$allItems[$i]);
        }
    }

    /**
     * @param $rule
     * @return array|null
     */
    protected function prepareDataForCalculation($rule)
    {
        $promoSkus = $rule->getAmrulesRule()->getPromoSkus();

        if ($promoSkus) {
            list($qtySkus,$itemsForSet) = $this->getItemsForSet($rule);
            $setQty = min($qtySkus);

            if ($rule->getDiscountQty() != null) {
                   $setQty = min($setQty, (int)$rule->getDiscountQty());
            }

            if ($setQty > 0) {
                $itemsForSet = $this->removeExcessItems($itemsForSet, $qtySkus, $setQty);

                return [
                    $setQty,
                    $itemsForSet
                ];
            }
        }

        return null;
    }

    /**
     * @param $totalPrice
     * @param $rule
     * @param $itemsForSet
     * @param $maxDiscountQty
     * @param $quoteAmount
     */
    protected function calculateDiscountForItems($totalPrice, $rule, $itemsForSet, $maxDiscountQty, $quoteAmount)
    {
        $ruleId = $this->getRuleId($rule);
        foreach ($itemsForSet as $item) {
            if ($maxDiscountQty > 0) {
                $discountData = $this->discountFactory->create();

                $baseItemPrice = $this->rulesProductHelper->getItemBasePrice($item);
                $baseItemOriginalPrice = $this->rulesProductHelper->getItemBaseOriginalPrice($item);

                $percentage = $baseItemPrice / $totalPrice;
                $baseDiscount = $baseItemPrice - $quoteAmount * $percentage;
                $itemDiscount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());
                $baseOriginalDiscount = $baseItemOriginalPrice - $quoteAmount * $percentage;
                $originalDiscount = ($baseItemOriginalPrice/$baseItemPrice) *
                    $this->priceCurrency->convert($baseOriginalDiscount, $item->getQuote()->getStore());

                if (!isset(self::$cachedDiscount[$ruleId][$item->getId()])) {
                    $discountData->setAmount($itemDiscount);
                    $discountData->setBaseAmount($baseDiscount);
                    $discountData->setOriginalAmount($originalDiscount);
                    $discountData->setBaseOriginalAmount($baseOriginalDiscount);
                } else {
                    $cachedItem = self::$cachedDiscount[$ruleId][$item->getId()];
                    $discountData->setAmount($itemDiscount + $cachedItem->getAmount());
                    $discountData->setBaseAmount($baseDiscount + $cachedItem->getBaseAmount());
                    $discountData->setOriginalAmount($originalDiscount + $cachedItem->getOriginalAmount());
                    $discountData->setBaseOriginalAmount($baseOriginalDiscount + $cachedItem->getBaseOriginalAmount());
                }
                $maxDiscountQty--;
                self::$cachedDiscount[$ruleId][$item->getId()] = $discountData;
            } else {
                break;
            }
        }
    }

    /**
     * @param $items
     * @return float|int
     */
    protected function getItemsPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item) {
            $totalPrice += $this->validator->getItemBasePrice($item);
        }

        return $totalPrice;
    }

    /**
     * @param $rule
     * @return array
     */
    protected function getItemsForSet($rule)
    {
        $qtySkus = [];
        $itemsForSet = self::$allItems;
        $skus = $this->rulesDataHelper->getRuleSkus($rule);

        foreach ($skus as $sku) {
            $qtySkus[$sku] = 0;
        }

        foreach ($itemsForSet as $i => $item) {
            if (in_array($item->getSku(), $skus)) {
                $qtySkus[$item->getSku()]++;
            } else {
                unset($itemsForSet[$i]);
            }
        }

        return [
            $qtySkus,
            $itemsForSet
        ];
    }

    /**
     * @param $itemsForSet
     * @param $qtySkus
     * @param $setQty
     * @return mixed
     */
    protected function removeExcessItems($itemsForSet, $qtySkus, $setQty)
    {
        foreach ($itemsForSet as $i => $item) {
            if ($qtySkus[$item->getSku()] > $setQty) {
                $qtySkus[$item->getSku()]--;
                unset($itemsForSet[$i]);
            }
        }

        return $itemsForSet;
    }
}
