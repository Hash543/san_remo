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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model;

use Magento\Catalog\Model\Product;
use Mageplaza\RewardPointsPro\Model\Source\Catalogrule\Earning;

/**
 * Class CatalogRule
 * @package Mageplaza\RewardPointsPro\Model
 */
class CatalogRule extends Rules
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init('Mageplaza\RewardPointsPro\Model\ResourceModel\CatalogRule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get conditions instance
     * @return mixed
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombine->create();
    }

    /**
     * Get actions instance
     * @return mixed
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'website_ids');
        $this->bindRuleToEntity($this->getResource(), 'customer_group_ids');
        if ($this->isObjectNew()) {
            $this->getMatchingProductIds();
            if (!empty($this->_productIds) && is_array($this->_productIds)) {
                $this->_ruleProductProcessor->reindexList($this->_productIds);
            }
        } else {
            $this->_ruleProductProcessor->getIndexer()->invalidate();
        }

        parent::afterSave();
    }

    /**
     * Check if rule behavior changed
     *
     * @return bool
     */
    public function isRuleBehaviorChanged()
    {
        if (!$this->isObjectNew()) {
            $arrayDiff = $this->dataDiff($this->getOrigData(), $this->getStoredData());
            unset($arrayDiff['name']);
            unset($arrayDiff['description']);
            if (empty($arrayDiff)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get array with data differences
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function dataDiff($array1, $array2)
    {
        $result = [];
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                }
            } else {
                $result[$key] = true;
            }
        }

        return $result;
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => $this->productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param int|array $productIds
     *
     * @return void
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
        }
        $this->_productIds[$product->getId()] = $results;
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if (is_null($website->getDefaultStore())) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }

        return $map;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointEarnFromRules(Product $product)
    {
        $pointEarn = $this->getPointEarnFromProduct($product);

        return $this->helperData->getPointHelper()->round($pointEarn);
    }

    /**
     * @param $item
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointEarnFromItem($item)
    {
        $pointEarn = $this->calculatePointEarnFromRules(null, $item);

        return $this->helperData->getPointHelper()->round($pointEarn);
    }

    /**
     * @param $product
     * @param null $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculatePointEarnFromRules($product, $item = null)
    {
        $pointEarn = 0;
        $isMaxEarn = false;
        $price = 0;
        $isEarnPointFromTax = $this->helperData->isEarnPointFromTax();
        if ($item == null) {
            $qty = 1;
            $productType = $product->getTypeId();
            if ($productType == 'grouped' || $productType == 'bundle') {
                $minPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice();
                $regularPrice = $finalPrice = $minPrice->getValue();
            } else {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount();
                $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount();
                if ($isEarnPointFromTax) {
                    $regularPrice = $regularPrice->getValue();
                    $finalPrice = $finalPrice->getValue();
                } else {
                    $regularPrice = $regularPrice->getBaseAmount();
                    $finalPrice = $finalPrice->getBaseAmount();
                }
            }
        } else {
            $product = $item->getProduct();
            $price = $isEarnPointFromTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal();
            $qty = $item->getQty();
        }

        $rules = $this->_getRulesFromProduct($product);
        try {
            if (count($rules)) {
                foreach ($rules as $rule) {
                    if ($rule['action'] == Earning::TYPE_FIXED) {
                        $pointEarn += $rule['point_amount'] * $qty;
                    } else {
                        if ($item != null) {
                            if ($rule['action'] == Earning::TYPE_PROFIT) {
                                $profit = $item->getProduct()->getCost() * $qty;
                                if ($price > $profit) {
                                    $price -= $profit;
                                }
                            } else {
                                $price -= ($item->getDiscountAmount() + $item->getMpRewardDiscount());
                            }
                        } else {
                            $price = $rule['action'] == Earning::TYPE_PRICE ? $finalPrice : ($regularPrice - $product->getCost());
                        }

                        $earnItem = ($rule['point_amount'] * $price) / $rule['money_step'];

                        if ($rule['max_points'] && $rule['max_points'] > 0) {
                            if ($earnItem > $rule['max_points']) {
                                $earnItem = $rule['max_points'];
                                $isMaxEarn = true;
                            }
                        }
                        $pointEarn += $earnItem;
                    }

                    if ($rule['action_stop'] || $isMaxEarn) {
                        break;
                    }
                }
                if ($item != null) {
                    $item->setMpRewardEarnFromCatalog($pointEarn);
                    $pointEarn = $this->helperData->getCalculationHelper()->deltaRoundPoint($pointEarn, 'catalog');
                    $item->setMpRewardEarn($item->getMpRewardEarn() + $pointEarn);
                    $this->helperData->getCalculationHelper()->setLastItemMatchRule($item);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $pointEarn;
    }

    /**
     * @param $product
     * @param null $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointEarnFromProduct($product, $item = null)
    {
        $pointEarn = $this->calculatePointEarnFromRules($product, $item);

        return $pointEarn;
    }

    /**
     * @param $product
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRulesFromProduct($product)
    {
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);

        return $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
    }
}
