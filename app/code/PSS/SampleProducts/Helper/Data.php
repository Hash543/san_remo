<?php
/**
 * @author Israel Yasis
 */
namespace PSS\SampleProducts\Helper;

use Magento\Store\Model\ScopeInterface;
/**
 * Class Data
 * @package PSS\SampleProducts\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * Settings
     */
    const PATH_SAMPLE_PRODUCTS_ENABLED = "sample_products/settings/enable";
    const PATH_SAMPLE_PRODUCTS_SHOW_SLIDER = "sample_products/settings/show_slider";
    const PATH_SAMPLE_PRODUCTS_NUMBER_PRODUCTS_TO_ADD = "sample_products/settings/number_products_add";
    const PATH_SAMPLE_PRODUCTS_LIST = "sample_products/settings/products";
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public static function isSampleProduct(\Magento\Catalog\Model\Product $product) {
        $isSampleProduct = false;
        //$price = (int)$product->getPrice();
        if(preg_match("/^mue/i", strtolower($product->getSku()))) {
            $isSampleProduct = true;
        }
        return $isSampleProduct;
    }
    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return bool
     */
    public static function isSampleProductByItem(\Magento\Quote\Api\Data\CartItemInterface $item) {
        $isSampleProduct = false;
        //$price = (int)$product->getPrice();
        if(preg_match("/^mue/i", strtolower($item->getSku()))) {
            $isSampleProduct = true;
        }
        return $isSampleProduct;
    }
    /**
     * @param $field
     * @param null|int $storeId
     * @return string
     */
    public function getConfigValue($field, $storeId = null) {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return (boolean)$this->getConfigValue(self::PATH_SAMPLE_PRODUCTS_ENABLED);
    }

    /**
     * @return bool
     */
    public function isSliderEnabled() {
        return (boolean)$this->getConfigValue(self::PATH_SAMPLE_PRODUCTS_SHOW_SLIDER);
    }

    /**
     * @return integer
     */
    public function getNumberProductsToBeAdded() {
        return (integer)$this->getConfigValue(self::PATH_SAMPLE_PRODUCTS_NUMBER_PRODUCTS_TO_ADD);
    }

    /**
     * Get the products Ids into an array
     * @return array
     */
    public function getListProducts() {
        $productIds = $this->getConfigValue(self::PATH_SAMPLE_PRODUCTS_LIST);
        if($productIds) {
            $productIds = explode(",", $productIds);
        } else {
            $productIds = [0];
        }
        return $productIds;
    }
}