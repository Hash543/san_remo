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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin\Model\ResourceModel\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGrid;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Plugin\Model\ResourceModel\Grid
 */
class Collection
{
    /**
     * Flag to check whether the join query is added or not
     *
     * @var bool $isJoint
     */
    protected $isJoin = false;

    /**
     * @param CustomerGrid $subject
     * @param $result
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    public function afterGetSelect(CustomerGrid $subject, $result)
    {
        $rewardCustomerTable = $subject->getTable('mageplaza_reward_customer');
        /** @var $result \Magento\Framework\DB\Select */
        if ($result && $result->getPart('from') && !$this->isJoin) {
            $result = $result->joinLeft(
                $rewardCustomerTable,
                "main_table.entity_id = {$rewardCustomerTable}.customer_id",
                [
                    'point_balance' => "{$rewardCustomerTable}.point_balance",
                    'is_active'     => "{$rewardCustomerTable}.is_active",
                ]
            );

            $this->isJoin = true;
        }

        return $result;
    }
}
