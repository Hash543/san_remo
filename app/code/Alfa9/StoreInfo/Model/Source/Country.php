<?php

namespace Alfa9\StoreInfo\Model\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Country extends AbstractSource implements ArrayInterface
{
    /**
     * @var \Alfa9\StoreInfo\Model\Stores
     */
    public $countryCollectionFactory;

    /**
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param array $options
     */
    public function __construct(
        CountryCollectionFactory $countryCollectionFactory,
        array $options = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($options);
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (count($this->options) == 0) {
            $this->options = $this->countryCollectionFactory->create()->toOptionArray(' ');
        }
        return $this->options;
    }
}
