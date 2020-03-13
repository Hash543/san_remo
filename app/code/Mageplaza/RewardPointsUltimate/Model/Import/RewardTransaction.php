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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Mageplaza\RewardPoints\Model\ActionFactory;
use Mageplaza\RewardPoints\Model\Source\ActionType;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\RewardCustomerFactory as AccountFactory;
use Mageplaza\RewardPointsUltimate\Model\TransactionFactory;

/**
 * Class RewardTransaction
 * @package Mageplaza\RewardPointsUltimate\Model\Import
 */
class RewardTransaction extends AbstractEntity
{
    /**
     * columns
     */
    const ACCOUNT_EMAIL = 'account_email';
    const ACTION_CODE   = 'action_code';
    const ORDER_ID      = 'order_id';
    const STORE_ID      = 'store_id';
    const POINT_AMOUNT  = 'point_amount';
    const CREATED_AT    = 'created_at';
    const COMMENT       = 'comment';

    /** @inheritdoc */
    protected $masterAttributeCode = 'account_email';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::ACCOUNT_EMAIL, self::POINT_AMOUNT, self::STORE_ID];

    /** @inheritdoc */
    protected $_availableBehaviors = [Import::BEHAVIOR_ADD_UPDATE];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames
        = [
            self::ACCOUNT_EMAIL,
            self::POINT_AMOUNT,
            self::ORDER_ID,
            self::STORE_ID,
            self::CREATED_AT,
            self::COMMENT
        ];

    protected $transactionFactory;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Store
     */
    protected $helperData;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\Transaction
     */
    protected $transaction;

    /**
     * @var array
     */
    protected $accountEmails = [];

    /**
     * RewardTransaction constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param AccountFactory $accountFactory
     * @param TransactionFactory $transactionFactory
     * @param DateTime $dateTime
     * @param ActionFactory $actionFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        AccountFactory $accountFactory,
        TransactionFactory $transactionFactory,
        DateTime $dateTime,
        ActionFactory $actionFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->accountFactory = $accountFactory;
        $this->transactionFactory = $transactionFactory;
        $this->dateTime = $dateTime;
        $this->actionFactory = $actionFactory;
        $this->helperData = $helperData;
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);
    }

    /**
     * @return \Mageplaza\RewardPointsUltimate\Model\Transaction
     */
    public function getTransaction()
    {
        if (!$this->transaction) {
            $this->transaction = $this->transactionFactory->create();
        }

        return $this->transaction;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_reward_transaction';
    }

    /**
     * @param $rowData
     * @param $rowNum
     *
     * @return bool
     */
    public function validateEmail($rowData, $rowNum)
    {
        $email = $rowData[self::ACCOUNT_EMAIL];
        if (!isset($this->accountEmails[$email])) {
            $accountCollection = $this->accountFactory->create()->getCollection();
            $accountCollection->getSelect()->join(
                ['customer' => $accountCollection->getTable('customer_entity')],
                'main_table.customer_id = customer.entity_id',
                ['email', 'entity_id']
            );
            $accountCollection->addFilterToMap('email', 'customer.email');
            $accountCollection->addFieldToFilter('email', $email);
            $account = $accountCollection->getFirstItem();
            if (!$account->getRewardId()) {
                $this->addRowError(__('reward account email doesn\'t exist'), $rowNum);

                return false;
            }

            $this->accountEmails[$account->getEmail()] = $account;
        }

        return true;
    }

    /**
     * @param $rowData
     * @param $rowNum
     *
     * @return bool
     */
    public function validateAction($rowData, $rowNum)
    {
        try {
            $actions = $this->actionFactory->getOptionHash();
            $action = $rowData[self::ACTION_CODE];
            if (!isset($actions[$action])) {
                $this->addRowError(__('Action code doesn\'t exist'), $rowNum);

                return false;
            }
        } catch (\Exception $e) {
            $this->addRowError($e->getMessage(), $rowNum);

            return false;
        }

        return true;
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $flag = true;

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        $stores = $this->helperData->getStores();
        foreach ($this->_permanentAttributes as $value) {
            if (empty($rowData[$value])) {
                $this->addRowError(__('%1 is empty', $value), $rowNum);
                $flag = false;
            } else {
                if ($value == self::ACCOUNT_EMAIL) {
                    $flag = $this->validateEmail($rowData, $rowNum);
                }

                if ($value == self::ACTION_CODE) {
                    $flag = $this->validateAction($rowData, $rowNum);
                }

                if ($value == self::STORE_ID && !isset($stores[$rowData[self::STORE_ID]])) {
                    $this->addRowError(__('store_id doesn\'t exist'), $rowNum);
                    $flag = false;
                }
            }
        }

        return $flag ? !$this->getErrorAggregator()->isRowInvalid($rowNum) : false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function _importData()
    {
        $this->saveEntity();

        return true;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function saveEntity()
    {
        $entityList = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                foreach ($this->_permanentAttributes as $field) {
                    if ($field == self::ACCOUNT_EMAIL) {
                        continue;
                    }
                    $entityList[$rowNum][$field] = $rowData[$field];
                }

                $currentAccount = $this->accountEmails[$rowData['account_email']];
                $entityList[$rowNum]['reward_id'] = $currentAccount->getRewardId();
                $entityList[$rowNum]['customer_id'] = $currentAccount->getEntityId();
                $entityList[$rowNum]['action_type'] = ActionType::BOTH;
                $entityList[$rowNum]['status'] = Status::COMPLETED;
                $entityList[$rowNum]['action_code'] = Data::ACTION_IMPORT_TRANSACTION;
                $entityList[$rowNum]['expiration_date'] = null;
                $orderId = '0';
                if (isset($rowData[self::ORDER_ID])) {
                    $orderId = $rowData[self::ORDER_ID];
                }

                $entityList[$rowNum][self::ORDER_ID] = $orderId;

                $date = $this->dateTime->gmtDate();
                if (isset($rowData[self::CREATED_AT]) && !empty($rowData[self::CREATED_AT])) {
                    $date = $this->dateTime->gmtDate(null, $rowData[self::CREATED_AT]);
                }
                $entityList[$rowNum][self::CREATED_AT] = $date;
                $extraContent = [];
                if (isset($rowData[self::COMMENT])) {
                    $extraContent['comment'] = $rowData[self::COMMENT];
                }
                $entityList[$rowNum]['extra_content'] = Data::jsonEncode($extraContent);
            }
        }

        $connection = $this->_connection;
        $connection->beginTransaction();
        try {
            $connection->insertMultiple(
                $this->transactionFactory->create()->getResource()->getMainTable(),
                $entityList
            );
            $connection->commit();
        } catch (\Exception $e) {
            $errorAggregator = $this->getErrorAggregator();
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
            $connection->rollBack();
        }

        return $this;
    }
}
