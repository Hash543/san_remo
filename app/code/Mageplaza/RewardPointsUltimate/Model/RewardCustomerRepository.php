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

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Api\RewardCustomerRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\RewardCustomerFactory;

/**
 * Class RewardCustomerRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class RewardCustomerRepository implements RewardCustomerRepositoryInterface
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\RewardCustomerFactory
     */
    protected $rewardCustomerFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * RewardCustomerRepository constructor.
     *
     * @param Data $helperData
     * @param \Mageplaza\RewardPointsUltimate\Model\RewardCustomerFactory $rewardCustomerFactory
     * @param CustomerFactory $customerFactory
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        Data $helperData,
        RewardCustomerFactory $rewardCustomerFactory,
        CustomerFactory $customerFactory,
        SearchResultFactory $searchResultFactory
    ) {
        $this->rewardCustomerFactory = $rewardCustomerFactory;
        $this->customerFactory = $customerFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData = $helperData;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountById($id)
    {
        if (!$id) {
            throw new InputException(__('Reward account id required'));
        }

        $rewardCustomer = $this->rewardCustomerFactory->create()->load($id);

        if (!$rewardCustomer->getId()) {
            throw new NoSuchEntityException(__('Reward account doesn\'t exist'));
        }

        return $rewardCustomer;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->searchResultFactory->create()->getTotalCount();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAccountById($id)
    {
        $rewardCustomer = $this->getAccountById($id);
        try {
            $rewardCustomer->delete();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountByEmail($email)
    {
        $searchResult = $this->searchResultFactory->create();
        $searchResult->getSelect()->join(
            ['customer' => $searchResult->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['email']
        )->where('customer.email = ?', $email);
        $rewardCustomer = $searchResult->getFirstItem();
        if (!$rewardCustomer->getRewardId()) {
            throw new NoSuchEntityException(__('Reward account email doesn\'t exist'));
        }

        return $rewardCustomer;
    }

    /**
     * {@inheritDoc}
     */
    public function save(\Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface $data)
    {
        if (!$data->getCustomerId()) {
            throw new InputException(__('Customer id required'));
        }

        $customer = $this->customerFactory->create()->load($data->getCustomerId());
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Customer doesn\'t exist'));
        }

        $rewardCustomer = $this->rewardCustomerFactory->create()->loadByCustomerId($customer->getId());
        if ($rewardCustomer->getId()) {
            throw new CouldNotSaveException(__('There is already a reward account with this customer'));
        }

        try {
            $rewardCustomer->setCustomerId($customer->getId());
            $rewardCustomer->create(['point_balance' => $data->getPointBalance()]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }
}
