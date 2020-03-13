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
 * Interface InvitationRepositoryInterface
 * @api
 */
interface InvitationRepositoryInterface
{
    /**
     * Lists Invitation that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface Invitation search result
     *     interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $email
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getReferralByEmail($email);

    /**
     * @param string $email
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getInvitedByEmail($email);

    /**
     * @return int
     */
    public function count();
}
