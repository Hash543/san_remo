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

namespace Mageplaza\RewardPoints\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Helper\Email;
use Mageplaza\RewardPoints\Model\Action\ActionInterface;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Transaction
 * @method int getRewardId()
 * @method int getOrderId()
 * @method int getActionCode()
 * @method int getCustomerId()
 * @method int getPointRemaining()
 * @method int getPointUsed()
 * @method int getPointAmount()
 * @method $this setPointAmount($amount)
 * @method int getPointAmountUpdated()
 * @method $this setPointAmountUpdated($amount)
 * @method int getStoreId()
 * @method int getStatus()
 * @method $this setStatus($status)
 * @method string getCreatedAt()
 * @method string getExpirationDate()
 * @package Mageplaza\RewardPoints\Model
 */
class Transaction extends AbstractExtensibleModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_transaction';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_transaction';

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var helperData
     */
    protected $helperData;

    /**
     * @var ActionInterface[]
     */
    protected $actionByCode = [];

    /**
     * Transaction constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param HelperData $helperData
     * @param ActionFactory $actionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        HelperData $helperData,
        ActionFactory $actionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->actionFactory = $actionFactory;
        $this->helperData = $helperData;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mageplaza\RewardPoints\Model\ResourceModel\Transaction');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $code
     * @param $customer
     * @param $actionObject
     *
     * @return $this
     * @throws LocalizedException
     */
    public function createTransaction($code, $customer, $actionObject)
    {
        /** @var Action $action */
        $action = $this->getActionModel($code, ['customer' => $customer, 'actionObject' => $actionObject]);
        $transactionData = $action->prepareTransaction();
        if (!is_array($transactionData)) {
            throw new LocalizedException(__('Invalid transaction Data'));
        }

        $transactionData['action_code'] = $code;
        $this->setData($transactionData);

        /** @var \Mageplaza\RewardPoints\Model\Account $account */
        $account = $this->getAccount($customer->getId());
        if (!$account->getId()) {
            throw new LocalizedException(__('Reward account does not exist'));
        }

        if ($account->getBalance() + $this->getPointAmount() < 0) {
            throw new LocalizedException(__('Account balance is not enough to take points back.'));
        }

        $this->setData('reward_id', $account->getId());
        if ($this->getPointAmount() > 0) {
            $this->setData('point_remaining', $this->getPointAmount());
        }

        $sendEmailUpdate = 0;
        if ($this->getStatus() == Status::COMPLETED) {
            $maxBalance = $this->helperData->getMaxPointPerCustomer($this->getStoreId());
            if ($maxBalance > 0 && $this->getPointAmount() > 0 && ($account->getBalance() + $this->getPointAmount() > $maxBalance)) {
                $availableAmount = $maxBalance - $account->getBalance();
                if ($availableAmount < 0) {
                    return $this;
                }

                $this->setPointAmount($availableAmount);
                $account->setBalance($maxBalance);
            } else {
                $account->addBalance($this->getPointAmount());
            }
            $sendEmailUpdate = 1;
        }

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while creating transaction. Please try again later.'));
        }

        if ($sendEmailUpdate) {
            $this->sendUpdateBalanceEmail();
        }

        if ($this->getPointAmount() < 0) {
            $actionType = $this->getData('action_type');
            if ($actionType == Data::ACTION_TYPE_EARNING) {
                $this->getResource()->updatePointRemaining($this);
            } elseif ($actionType == Data::ACTION_TYPE_SPENDING) {
                $this->getResource()->updatePointUsed($this);
            }
        }

        $this->_eventManager->dispatch($this->_eventPrefix . '_created', $this->_getEventData());
        $this->_eventManager->dispatch($this->_eventPrefix . '_created_' . $code, $this->_getEventData());

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function complete()
    {
        if (!$this->canComplete()) {
            throw new LocalizedException(__('Invalid transaction data to complete.'));
        }

        $account = $this->getAccount();
        $maxBalance = $this->helperData->getMaxPointPerCustomer($this->getStoreId());
        if ($maxBalance > 0 && $this->getPointAmount() > 0 && ($account->getBalance() + $this->getPointAmount() > $maxBalance)) {
            throw new LocalizedException(__(
                'Cannot complete this transaction. Maximum points allowed in account balance is %1',
                $maxBalance
            ));
        }

        $account->addBalance($this->getPointRemaining());
        $this->setStatus(Status::COMPLETED);

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while completing transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException | \Exception
     */
    public function cancel()
    {
        if (!$this->canCancel()) {
            throw new LocalizedException(__('Invalid transaction data to cancel.'));
        }

        $account = $this->getAccount();
        if ($account->getBalance() < $this->getPointRemaining()) {
            throw new LocalizedException(__('Account balance is not enough to cancel.'));
        }

        $account->addBalance(-$this->getPointRemaining());
        $this->setStatus(Status::CANCELED);

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while canceling transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        /**
         * When canceling transaction, we need to move point_used to other transaction because of invalid point_used in
         * this transaction
         */
        if ($this->getPointUsed() > 0) {
            $this->setPointAmountUpdated(-$this->getPointUsed());
            $this->getResource()->updatePointUsed($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function expire()
    {
        if (!$this->canExpire()) {
            return $this;
        }

        $account = $this->getAccount();
        $account->addBalance(-1.0 * ($this->getPointRemaining() - $this->getPointUsed()));
        $this->setStatus(Status::EXPIRED);

        try {
            $this->getResource()->saveRewardTransaction($this, $account);
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while expiring transaction. Please try again later.'));
        }

        $this->sendUpdateBalanceEmail();

        return $this;
    }

    /**
     * @return bool
     */
    public function canComplete()
    {
        return $this->getId()
               && $this->getPointAmount() > 0
               && $this->getStatus() < Status::COMPLETED;
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return $this->getId()
               && $this->getPointAmount() > 0
               && $this->getStatus() < Status::CANCELED;
    }

    /**
     * @return bool
     */
    public function canExpire()
    {
        return $this->getId()
               && ($this->getPointRemaining() > $this->getPointUsed())
               && ($this->getStatus() <= Status::COMPLETED)
               && $this->getExpirationDate()
               && (strtotime($this->getExpirationDate()) <= time());
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        $statusArray = Status::getOptionArray();
        if (array_key_exists($this->getStatus(), $statusArray)) {
            return $statusArray[$this->getStatus()];
        }

        return '';
    }

    /**
     * @param $code
     * @param array $data
     *
     * @return ActionInterface
     */
    protected function getActionModel($code, $data = [])
    {
        if (!isset($this->actionByCode[$code])) {
            $this->actionByCode[$code] = $this->actionFactory->create($code, $data);
        }

        return $this->actionByCode[$code];
    }

    /**
     * @param $code
     *
     * @return ActionInterface
     */
    public function getActionLabel($code)
    {
        return $this->getActionModel($code)->getActionLabel($this);
    }

    /**
     * @return $this
     */
    public function addTitle()
    {
        $this->setData('title', $this->getTitle());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        $action = $this->getActionModel($this->getActionCode());

        return $action->getTitle($this);
    }

    /**
     * Send update balance email
     *
     * @return $this
     */
    public function sendUpdateBalanceEmail()
    {
        $this->helperData->getEmailHelper()->sendEmailTemplate(
            $this->getCustomerId(),
            Email::XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE,
            $this->getEmailParams()
        );

        return $this;
    }

    /**
     * Send before expire email
     *
     * @return $this
     */
    public function sendExpiredTransactionEmail()
    {
        $this->helperData->getEmailHelper()->sendEmailTemplate(
            $this->getCustomerId(),
            Email::XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE,
            $this->getEmailParams()
        );

        try {
            $this->setData('expire_email_sent', 1)
                ->save();
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailParams()
    {
        $params = [
            'customer_name'           => $this->helperData->getAccountHelper()->getCustomerById($this->getCustomerId())->getName(),
            'point_amount'            => $this->getPointAmount(),
            'point_amount_formatted'  => $this->helperData->getPointHelper()->format(
                $this->getPointAmount(),
                $this->getStoreId()
            ),
            'status'                  => $this->getStatusLabel(),
            'point_balance'           => $this->getAccount()->getBalance(),
            'point_balance_formatted' => $this->getAccount()->getBalanceFormatted($this->getStoreId()),
            'expiration_date'         => $this->getExpirationDate() ? $this->helperData->formatDate(
                $this->getExpirationDate(),
                \IntlDateFormatter::MEDIUM,
                true
            ) : '',
            'comment'                 => $this->getTitle()
        ];

        return $params;
    }

    /**
     * @param null $customerId
     *
     * @return \Mageplaza\RewardPoints\Model\Account|mixed
     */
    protected function getAccount($customerId = null)
    {
        $accountHelper = $this->helperData->getAccountHelper();

        return $customerId ? $accountHelper->create($customerId) : $accountHelper->get($this->getRewardId());
    }

    /**
     * @return \Mageplaza\RewardPoints\Model\ResourceModel\Transaction
     */
    public function getResource()
    {
        return parent::getResource();
    }

    /**
     * @param $customerId
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getTransactionInFrontend($customerId)
    {
        $collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'DESC');

        return $collection;
    }
}
