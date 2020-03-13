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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Api;

/**
 * Interface TransactionRepositoryInterface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists Transaction that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionSearchResultInterface Transaction search result
     *     interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionSearchResultInterface
     */
    public function getTransactionByAccountId($id);

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionSearchResultInterface
     */
    public function getTransactionByOrderId($id);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function expire($id);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function cancel($id);

    /**
     * Required(customer_id, point_amount)
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface $data);
}
