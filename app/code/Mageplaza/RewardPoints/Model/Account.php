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
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\Source\ActionType;

/**
 * Class Account
 * @method \Mageplaza\RewardPoints\Model\ResourceModel\Account getResource()
 * @method getNotificationUpdate()
 * @method getNotificationExpire()
 * @method getCustomerId()
 * @package Mageplaza\RewardPoints\Model
 */
class Account extends AbstractExtensibleModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_account';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_account';

    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperData;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param HelperData $helperData
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HelperData $helperData,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
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
        $this->_init('Mageplaza\RewardPoints\Model\ResourceModel\Account');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $customerId
     *
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        return $this->load($customerId, 'customer_id');
    }

    /**
     * Create Reward Account
     *
     * @param array $data
     *
     * @return $this
     * @throws \Exception
     */
    public function create($data = [])
    {
        $customer = $this->helperData->getAccountHelper()->getCustomerById($this->getCustomerId());

        $subscribeDefault = $this->helperData->getEmailHelper()->getEmailConfig(
            'subscribe_by_default',
            $customer->getStoreId()
        );
        $this->addData(array_merge([
            'notification_expire' => $subscribeDefault,
            'notification_update' => $subscribeDefault
        ], $data));

        $this->save();

        return $this;
    }

    /**
     * @param $balance
     */
    public function addBalance($balance)
    {
        $this->setBalance($this->getBalance() + $balance);
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->getData('point_balance');
    }

    /**
     * @param $amount
     */
    public function setBalance($amount)
    {
        $this->setData('point_balance', $amount);
    }

    /**
     * Get balance with point label
     *
     * @param null $storeId
     *
     * @return mixed|string
     */
    public function getBalanceFormatted($storeId = null)
    {
        $balance = $this->getBalance();

        return $this->helperData->getPointHelper()->format($balance, $storeId);
    }

    /**
     * @param bool $format
     * @param null $storeId
     *
     * @return mixed|string
     */
    public function getTotalEarningPoints($format = false, $storeId = null)
    {
        $earningPoints = $this->getResource()->getTotalPointsByType($this, ActionType::EARNING);

        return $format ? $this->helperData->getPointHelper()->format($earningPoints, $storeId) : $earningPoints;
    }

    /**
     * @param bool $format
     * @param null $storeId
     *
     * @return mixed|string
     */
    public function getTotalSpendingPoints($format = false, $storeId = null)
    {
        $spendingPoints = abs($this->getResource()->getTotalPointsByType($this, ActionType::SPENDING));

        return $format ? $this->helperData->getPointHelper()->format($spendingPoints, $storeId) : $spendingPoints;
    }
}
