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
 * Interface RewardCustomerRepositoryInterface
 * @api
 */
interface RewardCustomerRepositoryInterface
{
    /**
     * Lists Reward customer that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerSearchResultInterface Reward customer search
     *     result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface
     */
    public function getAccountById($id);

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteAccountById($id);

    /**
     * @param string $email
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface
     */
    public function getAccountByEmail($email);

    /**
     * Required(customer_id)
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface $data);
}
