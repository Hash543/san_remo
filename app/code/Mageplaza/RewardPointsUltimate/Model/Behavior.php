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

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Behavior
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Behavior extends AbstractModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_behavior';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_behavior';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection
     */
    protected $transactionCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * Behavior constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param TimezoneInterface $date
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        TimezoneInterface $date,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->_storeManager = $storeManager;
        $this->date = $date;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $resource
     * @param $field
     */
    public function bindRuleToEntity($resource, $field)
    {
        $data = $this->getData($field);
        if ($data) {
            if (!is_array($data)) {
                $data = explode(',', (string) $data);
            }
            $resource->bindRuleToEntity($this->getRuleId(), $data, substr($field, 0, -4));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'website_ids');
        $this->bindRuleToEntity($this->getResource(), 'customer_group_ids');
        parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        if (!$this->hasWebsiteIds()) {
            $websiteIds = $this->_getResource()->getWebsiteIds($this->getId());
            $this->setData('website_ids', (array) $websiteIds);
        }

        parent::_afterLoad();
    }

    /**
     * @param $action
     * @param bool $isFilterCustomerGroup
     * @param string $customerGroup
     *
     * @return int
     */
    public function getPointByAction($action, $isFilterCustomerGroup = false, $customerGroup = '')
    {
        $behavior = $this->getBehaviorRuleByAction($action, $isFilterCustomerGroup, $customerGroup);
        if ($behavior->getRuleId()) {
            return $behavior->getPointAmount();
        }

        return 0;
    }

    /**
     * @param $action
     * @param bool $isFilterCustomerGroup
     * @param string $customerGroup
     *
     * @return \Magento\Framework\DataObject
     */
    public function getBehaviorRuleByAction($action, $isFilterCustomerGroup = false, $customerGroup = '')
    {
        $now = $this->date->date()->format('Y-m-d');
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!$websiteId) {
            $websiteId = $this->getCustomerWebsiteId();
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('point_action', $action)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('from_date', [['null' => true], ['lteq' => $now]])
            ->addFieldToFilter('to_date', [['null' => true], ['gteq' => $now]])
            ->addFieldToFilter('website_ids', $websiteId);

        if ($isFilterCustomerGroup) {
            if (!$customerGroup) {
                $contextGroup = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
                $customerGroup = $contextGroup ? $contextGroup : 0;
            }
            $collection->addFieldToFilter('customer_group_ids', $customerGroup);
        }
        $collection->setOrder('sort_order', 'ASC');

        return $collection->getFirstItem();
    }

    /**
     * @param $action
     * @param $customerId
     *
     * @return mixed
     */
    public function checkMaxPoint($action, $customerId)
    {
        return $this->getResource()->checkMaxPoint($action, $this, $customerId);
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    public function checkCustomerHasBirthday($customerId)
    {
        return $this->getResource()->checkCustomerHasBirthday($customerId);
    }
}
