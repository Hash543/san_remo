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

namespace Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule;

use Mageplaza\RewardPointsPro\Model\ResourceModel\AbstractCollection;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Mageplaza\RewardPointsPro\Model\ShoppingCartEarningRule',
            'Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule'
        );
    }

    /**
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter('rule_type', Type::SHOPPING_CART_EARNING);

        return $this;
    }
}
