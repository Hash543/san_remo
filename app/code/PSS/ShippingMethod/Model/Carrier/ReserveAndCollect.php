<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Model\Carrier;
/**
 * Class ReserveAndCollect
 * @package PSS\ShippingMethod\Model\Carrier
 */
class ReserveAndCollect extends \Alfa9\StorePickup\Model\Carrier\ShippingMethod {

    /**
     * Prefix of the stores
     */
    const PREFIX_STORE = 'reserveandcollect';
    const SHIPPING_CODE = 'reserveandcollect';
    const SHIPPING_METHOD = self::SHIPPING_CODE.'_'.self::SHIPPING_CODE;
    /**
     * @var string
     */
    protected $_code = self::SHIPPING_CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * {@inheritdoc}
     */
    public function getStoreList($quoteItems = []) {
        return $this->pickupHelper->getStoreListExpress($quoteItems);
    }
}