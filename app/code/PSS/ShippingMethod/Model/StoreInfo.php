<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class StoreInfo extends AbstractExtensibleModel implements \PSS\ShippingMethod\Api\Data\ShippingMethodInterface {
    /**
     * {@inheritdoc}
     */
    public function getStoreInfo() {
        return $this->getData(self::STORE_INFO);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreInfo($storeInfo) {
        $this->setData(self::STORE_INFO, $storeInfo);
        return $this;
    }
}