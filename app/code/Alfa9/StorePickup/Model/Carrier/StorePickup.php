<?php

namespace Alfa9\StorePickup\Model\Carrier;

class StorePickup extends ShippingMethod {
    /**
     * Prefix of the stores
     */
    const PREFIX_STORE = 'storepickup';
    const SHIPPING_CODE = 'storepickup';
    const SHIPPING_METHOD = self::SHIPPING_CODE.'_'.self::SHIPPING_CODE;
    protected $_code = self::SHIPPING_CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * {@inheritdoc}
     */
    public function getStoreList($quoteItems = []) {
        return $this->pickupHelper->getAllStoreList($quoteItems);
    }
    
}
