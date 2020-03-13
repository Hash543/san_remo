<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Model;

class Rule extends \Magento\Framework\Model\AbstractModel
{
    const SAME_PRODUCT = 'ampromo_product';
    const PER_PRODUCT = 'ampromo_items';
    const WHOLE_CART = 'ampromo_cart';
    const SPENT = 'ampromo_spent';
    const EACHN = 'ampromo_eachn';

    const RULE_TYPE_ALL = 0;
    const RULE_TYPE_ONE = 1;

    const NOT_AUTO_FREE_ITEMS = 0;
    const AUTO_FREE_ITEMS = 1;
    const AUTO_FREE_DISCOUNTED_ITEMS = 2;

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Promo\Model\ResourceModel\Rule');
        $this->setIdFieldName('entity_id');
    }

    public function loadBySalesrule(\Magento\SalesRule\Model\Rule $rule)
    {
        if ($ampromoRule = $rule->getData('ampromo_rule')) {
            return $ampromoRule;
        }

        $this->load($rule->getId(), 'salesrule_id');

        $rule->setData('ampromo_rule', $this);

        return $this;
    }
}
