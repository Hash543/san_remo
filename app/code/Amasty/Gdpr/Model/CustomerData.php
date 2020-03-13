<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchResultsInterface;

class CustomerData
{
    private $allowedAttributes = [
        'customer'         => [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',

            'email',
            'dob',
            'gender',
            'taxvat'
        ],
        'customer_address' => [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',

            'company',
            'street',
            'city',
            'country_id',
            'region',
            'postcode',
            'telephone',
            'fax'
        ],
        'exclude' => [
            'id',
            'group_id',
            'default_billing',
            'default_shipping',
            'created_at',
            'updated_at',
            'created_in',
            'store_id',
            'website_id',
            'disable_auto_group_change',
            'chop_enable',
            'is_subscribed',
            'confirmation'
        ]
    ];

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    public function __construct(
        CustomerRepository $customerRepository,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Get array of attribute names by entity code
     *
     * @param string $type
     *
     * @return array
     */
    public function getAttributeCodes($type)
    {
        $attributeCodes = [];

        if (isset($this->allowedAttributes[$type])) {
            $attributeCodes = $this->allowedAttributes[$type];
        }

        return $attributeCodes;
    }

    /**
     * @param $customerId
     *
     * @return array
     */
    public function getPersonalData($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->getById($customerId);

        $data = array_merge(
            $this->getCustomerEavData($customer),
            $this->getAddressEavData($customer)
        );

        return $data;
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    protected function getCustomerEavData(Customer $customer)
    {
        return $this->collectAttributeValues($customer, $this->getAttributes('customer'));
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    protected function getAddressEavData(Customer $customer)
    {
        $attributes = $this->getAttributes('customer_address');

        $result = [];

        $i = 0;
        /** @var \Magento\Customer\Model\Data\Address $address */
        foreach ($customer->getAddresses() as $address) {
            $i++;
            $result = array_merge(
                $result,
                $this->collectAttributeValues($address, $attributes, "Address #$i ")
            );
        }

        return $result;
    }

    /**
     * @param AbstractSimpleObject   $entity
     * @param SearchResultsInterface $attributes
     * @param string                 $namePrefix
     *
     * @return array
     */
    protected function collectAttributeValues(
        AbstractSimpleObject $entity,
        SearchResultsInterface $attributes,
        $namePrefix = ''
    ) {
        $result = [];

        $data = $entity->__toArray();

        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                continue;
            }

            $value = $data[$attribute->getAttributeCode()];
            if (!empty($value)) {
                if (is_array($value) && $attribute->getAttributeCode() !== 'street') {
                    $value = reset($value);
                } elseif (is_array($value) && $attribute->getAttributeCode() === 'street') {
                    foreach ($value as $k => $v) {
                        $result [] = [
                            $namePrefix . $attribute->getStoreLabel() . ' ' . ($k+1),
                            $v
                        ];
                    }

                    continue;
                }
                $result [] = [
                    $namePrefix . $attribute->getStoreLabel(),
                    $value
                ];
            }
        }

        return $result;
    }

    /**
     * @param $entityCode
     *
     * @return AttributeSearchResultsInterface
     */
    protected function getAttributes($entityCode)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('attribute_code', $this->allowedAttributes[$entityCode], 'in')
            ->create();

        return $this->attributeRepository->getList($entityCode, $searchCriteria);
    }
}
