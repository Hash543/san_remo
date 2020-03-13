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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\RewardPoints\Model\AccountFactory;

/**
 * Class Account
 * @package Mageplaza\RewardPoints\Helper
 */
class Account extends AbstractData
{
    /**
     * @var \Mageplaza\RewardPoints\Model\Account[]
     */
    protected $accountById = [];

    /**
     * @var \Mageplaza\RewardPoints\Model\Account[]
     */
    protected $accountByCustomerId = [];

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Account constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Mageplaza\RewardPoints\Model\AccountFactory $accountFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerRegistry $customerRegistry,
        AccountFactory $accountFactory,
        HttpContext $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->customerRegistry = $customerRegistry;
        $this->accountFactory = $accountFactory;
        $this->httpContext = $httpContext;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $accountId
     *
     * @return \Mageplaza\RewardPoints\Model\Account
     */
    public function get($accountId = null)
    {
        if (!$accountId && $this->isCustomerLoggedIn()) {
            $customerId = $this->customerSession->getId();

            return $this->getByCustomerId($customerId);
        }

        if (!isset($this->accountById[$accountId])) {
            $this->accountById[$accountId] = $this->accountFactory->create()->load($accountId);
        }

        return $this->accountById[$accountId];
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    public function getByCustomerId($customerId)
    {
        if (!isset($this->accountByCustomerId[$customerId])) {
            $this->accountByCustomerId[$customerId] = $this->create($customerId);
        }

        return $this->accountByCustomerId[$customerId];
    }

    /**
     * @param $customer
     * @param array $data
     *
     * @return mixed
     */
    public function create($customer, $data = [])
    {
        if (!$customer instanceof CustomerInterface) {
            $customer = $this->getCustomerById($customer);
        }

        /** @var \Mageplaza\RewardPoints\Model\Account $account */
        $account = $this->accountFactory->create();
        $account->loadByCustomerId($customer->getId());

        try {
            if ($account->getId()) {
                $account->addData($data)
                    ->save();
            } else {
                $subscribeDefault = $this->objectManager->get(Email::class)
                    ->getEmailConfig('subscribe_by_default', $customer->getStoreId());

                $account->setCustomerId($customer->getId())
                    ->addData(array_merge([
                        'notification_expire' => $subscribeDefault,
                        'notification_update' => $subscribeDefault
                    ], $data))
                    ->save();
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $account;
    }

    /**
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @param $customerId
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerById($customerId)
    {
        try {
            return $this->customerRegistry->retrieve($customerId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_request->isAjax() ? $this->customerSession->isLoggedIn()
            : (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Get customer group on depesonalize page
     * @return mixed|null
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId();
    }
}
