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
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsUltimate\Api\Data\TransactionSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Api\TransactionRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\TransactionFactory;

/**
 * Class TransactionRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\TransactionFactory
     */
    protected $transactionFactory;

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
     * TransactionRepository constructor.
     *
     * @param Data $helperData
     * @param \Mageplaza\RewardPointsUltimate\Model\TransactionFactory $transactionFactory
     * @param CustomerFactory $customerFactory
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        Data $helperData,
        TransactionFactory $transactionFactory,
        CustomerFactory $customerFactory,
        SearchResultFactory $searchResultFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->customerFactory = $customerFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData = $helperData;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionByAccountId($id)
    {
        return $this->searchResultFactory->create()->addFieldToFilter('reward_id', $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionByOrderId($id)
    {
        return $this->searchResultFactory->create()->addFieldToFilter('order_id', $id);
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
    public function expire($id)
    {
        try {
            $this->getTransactionById($id)->expire();
        } catch (\Exception $e) {
            throw new CouldNotSaveException((__('Could not expire the transaction', $e->getMessage())));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function cancel($id)
    {
        try {
            $transaction = $this->getTransactionById($id);
            if ($this->isActionImport($transaction)) {
                throw new InputException(__('Can\'t cancel transaction import'));
            }
            $transaction->cancel();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not cancel the transaction', $e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function save(\Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface $data)
    {
        if (!$data->getCustomerId()) {
            throw new InputException(__('Customer id required'));
        }

        if (!$data->getPointAmount()) {
            throw new InputException(__('Point amount required'));
        }

        if (intval($data->getPointAmount()) <= 0) {
            throw new InputException(__('Point amount must be greater than zero'));
        }

        $customer = $this->customerFactory->create()->load($data->getCustomerId());
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Customer doesn\'t exist'));
        }

        try {
            $data = [
                'point_amount' => $data->getPointAmount(),
                'comment'      => $data->getComment(),
                'expire_after' => $data->getExpireAfter()
            ];
            $transaction = $this->transactionFactory->create();
            $transaction->createTransaction(Data::ACTION_ADMIN, $customer, new DataObject($data));
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getTransactionById($id)
    {
        if (!$id) {
            throw new InputException(__('Transaction id required'));
        }

        $transaction = $this->transactionFactory->create()->load($id);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction id doesn\'t exist'));
        }

        return $transaction;
    }

    /**
     * @param $transaction
     *
     * @return bool
     */
    public function isActionImport($transaction)
    {
        return $transaction->getActionCode() == Data::ACTION_IMPORT_TRANSACTION ? true : false;
    }
}
