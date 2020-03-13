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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Mageplaza\RewardPoints\Helper\Email as HelperEmail;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\RewardCustomerFactory as AccountFactory;

/**
 * Class RewardAccount
 * @package Mageplaza\RewardPointsUltimate\Model\Import
 */
class RewardAccount extends AbstractEntity
{
    /**
     * columns
     */
    const CUSTOMER_EMAIL = 'customer_email';
    const POINT_BALANCE  = 'point_balance';
    const STORE_ID       = 'store_id';
    const WEBSITE_ID     = 'website_id';

    /** @inheritdoc */
    protected $masterAttributeCode = 'customer_email';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes
        = [
            self::CUSTOMER_EMAIL,
            self::STORE_ID,
            self::WEBSITE_ID
        ];

    /** @inheritdoc */
    protected $_availableBehaviors
        = [
            Import::BEHAVIOR_APPEND,
            Import::BEHAVIOR_DELETE
        ];

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
            self::CUSTOMER_EMAIL,
            self::POINT_BALANCE,
            self::STORE_ID,
            self::WEBSITE_ID
        ];

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AccountManagementInterface
     */
    protected $customerManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagementInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var HelperEmail
     */
    protected $helperEmail;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * RewardAccount constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Data $helperData
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param GroupManagementInterface $groupManagementInterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param AccountFactory $accountFactory
     * @param HelperEmail $helperEmail
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        Data $helperData,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        GroupManagementInterface $groupManagementInterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AccountFactory $accountFactory,
        HelperEmail $helperEmail,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->customerManagement = $accountManagement;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->groupManagementInterface = $groupManagementInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->accountFactory = $accountFactory;
        $this->helperEmail = $helperEmail;
        $this->customerFactory = $customerFactory;
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_reward_account';
    }

    /**
     * @param $rowData
     * @param $rowNum
     *
     * @return bool
     */
    public function validateEmail($rowData, $rowNum)
    {
        if (!filter_var($rowData[self::CUSTOMER_EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->addRowError(__('Invalid email'), $rowNum);

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
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::CUSTOMER_EMAIL])) {
                $this->addRowError(__('customer_email is empty'), $rowNum);
                $flag = false;
            } else {
                $flag = $this->validateEmail($rowData, $rowNum);
            }

            return $flag;
        }

        $stores = $this->helperData->getStores();
        $websites = $this->helperData->getWebsites();
        foreach ($this->_permanentAttributes as $value) {
            if (empty($rowData[$value])) {
                $this->addRowError(__('%1 is empty', $value), $rowNum);
                $flag = false;
            } else {
                if ($value == self::CUSTOMER_EMAIL) {
                    $flag = $this->validateEmail($rowData, $rowNum);
                }

                if ($value == self::POINT_BALANCE && $rowData[self::POINT_BALANCE] < 0) {
                    $this->addRowError(__('Point balance must equals or greater than zero'), $rowNum);
                    $flag = false;
                }

                if ($value == self::STORE_ID && !isset($stores[$rowData[self::STORE_ID]])) {
                    $this->addRowError(__('store_id doesn\'t exist'), $rowNum);
                    $flag = false;
                }
                if ($value == self::WEBSITE_ID && !isset($websites[$rowData[self::WEBSITE_ID]])) {
                    $this->addRowError(__('website_id doesn\'t exist'), $rowNum);
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
        if ($this->getBehavior() == Import::BEHAVIOR_DELETE) {
            $this->deleteEntity();
        } else {
            $this->saveEntity();
        }

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
                    $entityList[$rowNum][$field] = $rowData[$field];
                }
                $entityList[$rowNum][self::POINT_BALANCE] = 0;
                if (isset($rowData[self::POINT_BALANCE])) {
                    $entityList[$rowNum][self::POINT_BALANCE] = $rowData[self::POINT_BALANCE];
                }
            }
        }

        $this->saveEntityFinish($entityList);

        return $this;
    }

    /**
     * @param $data
     *
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer($data)
    {
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            [
                'email'              => $data['customer_email'],
                'website_id'         => $data['website_id'],
                'firstname'          => 'firstname',
                'lastname'           => 'lastname',
                'group'              => $this->groupManagementInterface->getDefaultGroup($data['store_id']),
                'sendemail_store_id' => $data['store_id']
            ],
            CustomerInterface::class
        );

        return $this->customerManagement->createAccount($customer);
    }

    /**
     * @param array $entityData
     *
     * @return $this
     * @throws \Exception
     */
    public function saveEntityFinish(array $entityData)
    {
        $connection = $this->_connection;
        $connection->beginTransaction();
        try {
            foreach ($entityData as $key => $data) {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($data['website_id']);
                $customer->loadByEmail($data['customer_email']);
                if (!$customer->getEntityId()) {
                    $customer = $this->createCustomer($data);
                }

                $account = $this->accountFactory->create();
                if ($account->load($customer->getId(), 'customer_id')->getId()) {
                    $account->setPointBalance($account->getPointBalance() + abs($data[self::POINT_BALANCE]));
                } else {
                    $subscribeDefault = $this->helperEmail->getEmailConfig(
                        'subscribe_by_default',
                        $customer->getStoreId()
                    );
                    $data['customer_id'] = $customer->getId();
                    $data['notification_expire'] = $subscribeDefault;
                    $data['notification_update'] = $subscribeDefault;
                    $account->setData($data);
                }

                $account->save();
            }

            $connection->commit();
        } catch (\Exception $e) {
            $this->addException($e);

            $connection->rollBack();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteEntity()
    {
        $listEmails = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $email = $rowData[self::CUSTOMER_EMAIL];
                    $listEmails[] = $email;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        $accountCollection = $this->accountFactory->create()->getCollection();
        $accountCollection->getSelect()->join(
            ['customer' => $accountCollection->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['email']
        );
        $accountCollection->addFilterToMap('email', 'customer.email');
        $accountCollection->addFieldToFilter('email', ['in' => $listEmails]);

        try {
            foreach ($accountCollection as $account) {
                $account->delete();
            }
        } catch (\Exception $e) {
            $this->addException($e);
        }

        return $this;
    }

    /**
     * @param $e
     *
     * @return $this
     */
    public function addException($e)
    {
        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->addError(
            AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
            ProcessingError::ERROR_LEVEL_CRITICAL,
            null,
            null,
            $e->getMessage()
        );

        return $this;
    }
}
