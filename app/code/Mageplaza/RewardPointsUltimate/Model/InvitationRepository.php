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

namespace Mageplaza\RewardPointsUltimate\Model;

use Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Api\InvitationRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class InvitationRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class InvitationRepository implements InvitationRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * InvitationRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $helperData
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $helperData
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData = $helperData;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     */
    public function getReferralByEmail($email)
    {
        return $this->searchResultFactory->create()->addFieldToFilter('referral_email', $email);
    }

    /**
     * {@inheritDoc}
     */
    public function getInvitedByEmail($email)
    {
        return $this->searchResultFactory->create()->addFieldToFilter('invited_email', $email);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->searchResultFactory->create()->getTotalCount();
    }
}
