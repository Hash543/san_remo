<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Plugin\WithoutConsent\Grid;

use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\WithoutConsent\Grid\Collection as WithoutConsentCollection;

class Collection
{
    /**
     * @var CollectionFactory
     */
    private $withConsentCollectionFactory;

    public function __construct(
        CollectionFactory $withConsentCollectionFactory
    ) {
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
    }

    /**
     * @param WithoutConsentCollection $subject
     * @param WithoutConsentCollection $result
     *
     * @return mixed
     */
    public function after_initSelect($subject, $result)
    {
        /** @var \Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection $withConsentCollection */
        $withConsentCollection = $this->withConsentCollectionFactory->create();
        $customerIds = $withConsentCollection->getConsentCustomerIds();

        $result->getSelect()->columns(
            [
                'name'        => 'CONCAT_WS(\' \',
                            main_table.prefix,
                            main_table.firstname,
                            main_table.middlename,
                            main_table.lastname,
                            main_table.suffix)',
                'customer_id' => 'entity_id'
            ]
        );

        $result->getSelect()->joinLeft(
            ['customer_address' => $result->getTable('customer_address_entity')],
            'customer_address.parent_id = main_table.default_billing',
            [
                'country_id'
            ]
        );

        if (!empty($customerIds)) {
            $result->addFieldToFilter('main_table.entity_id', ['nin' => $customerIds]);
        }

        $result->getSelect()->group('main_table.entity_id');

        $result->addFilterToMap(
            'entity_id',
            'main_table.entity_id'
        );

        return $result;
    }
}
