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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as SaleRuleCombineFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as SaleRuleProductCombineFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class Rules
 * @package Mageplaza\RewardPointsPro\Model
 */
abstract class Rules extends AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $conditionCombine;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $saleRuleCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $saleRuleProductCombineFactory;

    /**
     * @var \Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor
     */
    protected $_ruleProductProcessor;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Mageplaza\RewardPointsPro\Helper\Data
     */
    protected $helperData;

    /**
     * Store already validated addresses and validation results
     * @var array
     */
    protected $_validatedAddresses = [];

    /**
     * Rules constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param SaleRuleCombineFactory $saleRuleCombineFactory
     * @param SaleRuleProductCombineFactory $saleRuleProductCombineFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $helperData
     * @param Indexer\Rule\RuleProductProcessor $ruleProductProcessor
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ProductCollection $productCollectionFactory,
        StoreManagerInterface $storeManager,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        SaleRuleCombineFactory $saleRuleCombineFactory,
        SaleRuleProductCombineFactory $saleRuleProductCombineFactory,
        ProductFactory $productFactory,
        Session $customerSession,
        Data $helperData,
        RuleProductProcessor $ruleProductProcessor,
        Iterator $resourceIterator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->conditionCombine = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->saleRuleCombineFactory = $saleRuleCombineFactory;
        $this->_ruleProductProcessor = $ruleProductProcessor;
        $this->saleRuleProductCombineFactory = $saleRuleProductCombineFactory;
        $this->_customerSession = $customerSession;
        $this->helperData = $helperData;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get conditions field set id
     *
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     * @since 100.1.0
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * Get conditions instance
     * @return mixed
     */
    public function getConditionsInstance()
    {
        return $this->saleRuleCombineFactory->create();
    }

    /**
     * Get actions instance
     * @return mixed
     */
    public function getActionsInstance()
    {
        return $this->saleRuleProductCombineFactory->create();
    }

    /**
     * @param $resource
     * @param $field
     */
    public function bindRuleToEntity($resource, $field)
    {
        if ($data = $this->getData($field)) {
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
            $customerGroupIds = $this->_getResource()->getWebsiteIds($this->getId());
            $this->setData('website_ids', (array) $customerGroupIds);
        }

        parent::_afterLoad();
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     *
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]);
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     *
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;

        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]) ?: false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     *
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }

        return $address;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function validateRule($item)
    {
        if (!$this->canProcessRule($item->getAddress())) {
            return false;
        }

        if (!$this->getActions()->validate($item)) {
            $childItems = $item->getChildren();
            $isContinue = true;
            if (!empty($childItems)) {
                foreach ($childItems as $childItem) {
                    if ($this->getActions()->validate($childItem)) {
                        $isContinue = false;
                    }
                }
            }
            if ($isContinue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return bool
     */
    public function canProcessRule($address)
    {
        if ($this->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $this->getIsValidForAddress($address);
        }

        $this->afterLoad();

        /**
         * quote does not meet rule's conditions
         */
        if (!$this->validate($address)) {
            $this->setIsValidForAddress($address, false);

            return false;
        }

        /**
         * passed all validations, remember to be valid
         */
        $this->setIsValidForAddress($address, true);

        return true;
    }
}
