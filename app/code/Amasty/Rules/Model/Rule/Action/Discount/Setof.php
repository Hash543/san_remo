<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

namespace Amasty\Rules\Model\Rule\Action\Discount;

abstract class Setof extends AbstractRule
{
    const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     */
    protected function prepareSetRule($rule, $address)
    {
        if (!$rule->getAmrulesRule()->getPromoSkus() && !$rule->getAmrulesRule()->getPromoCats()) {
            return [];
        }
        $sortedProducts = [];
        $qtySkus = [];
        $qtyCats = [];
        if ($rule->getAmrulesRule()->getPromoSkus()) {
            $skus = $this->rulesDataHelper->getRuleSkus($rule);
            foreach ($skus as $sku) {
                $qtySkus[$sku] = 0;
            }
        }
        if ($rule->getAmrulesRule()->getPromoCats()) {
            $cats = $this->rulesDataHelper->getRuleCats($rule);
            foreach ($cats as $cat) {
                $qtyCats[$cat] = 0;
            }
        }
        $allItems = $this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER);
        foreach ($allItems as $item) {

            if (!$item->getAmrulesId()) {
                continue;
            }

            if ($rule->getAmrulesRule()->getPromoSkus() && in_array($item->getSku(), $skus)) {
                $qtySkus[$item->getSku()] += $this->getItemQty($item);
            }

            if ($rule->getAmrulesRule()->getPromoCats()
                && array_intersect($item->getProduct()->getCategoryIds(), $cats)
            ) {
                foreach (array_intersect($item->getProduct()->getCategoryIds(), $cats) as $category) {
                    $qtyCats[$category] += $this->getItemQty($item);
                }
            }

            $sortedProducts[$item->getAmrulesId()] = $item;
        }

        $qtySkus = $this->_setMinValue($qtySkus, $rule->getDiscountQty());
        $qtyCats = $this->_setMinValue($qtyCats, $rule->getDiscountQty());
        asort($sortedProducts);

        return [$qtySkus, $qtyCats, $sortedProducts];
    }

    /**
     * @param $array
     * @param $discountQty
     * @return mixed
     */
    protected function _setMinValue($array, $discountQty)
    {
        if (!$array) {
            return $array;
        }
        $min = min($array);
        if ($min == 0) {
            return $array;
        }

        if ($discountQty == 0) {
            $discountQty = $min;
        }
        $min = min($min, (int)$discountQty);
        foreach ($array as $key => $value) {
            $array[$key] = $min;
        }

        return $array;
    }

    protected function getMinQty($rule, $qtySkus, $qtyCats)
    {
        $minQty = 0;
        if ($qtySkus && $rule->getAmrulesRule()->getPromoSkus()) {
            $minQty = min($qtySkus);
        }

        if ($qtyCats && $rule->getAmrulesRule()->getPromoCats()) {
            $minQty = min($qtyCats);
        }

        return $minQty;
    }

}
