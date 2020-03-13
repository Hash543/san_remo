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

/**
 * Class ShoppingCartSpendingRule
 * @package Mageplaza\RewardPointsPro\Model
 */
class ShoppingCartSpendingRule extends Rules
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init('Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartSpendingRule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get Rule label by specified store
     *
     * @param null $store
     *
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLabel($store = null)
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $labels = (array) $this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve rule store labels
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * @param $storeId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLabelByStoreId($storeId)
    {
        $storeLabels = $this->getStoreLabels();
        if (isset($storeLabels[$storeId]) && trim($storeLabels[$storeId])) {
            return $storeLabels[$storeId];
        } elseif (isset($storeLabels[0]) && trim($storeLabels[0])) {
            return $storeLabels[0];
        }

        return $this->getName();
    }
}
