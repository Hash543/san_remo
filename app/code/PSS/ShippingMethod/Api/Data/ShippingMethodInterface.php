<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Api\Data;
/**
 * Interface ShippingMethodInterface
 * @package PSS\ShippingMethod\Api\Data
 */
interface ShippingMethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface {

    const STORE_INFO = 'store_info';
    /**
     * Retrieve rate info message
     *
     * @return string
     */
    public function getStoreInfo();

    /**
     * Set rate info message
     *
     * @param string $storeInfo
     * @return $this
     */
    public function setStoreInfo($storeInfo);
}