<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ReplaceProductname\Plugin\Magento\Checkout\CustomerData;

/**
 * Class ConfigurableItem
 * @package PSS\ReplaceProductname\Plugin\Magento\Checkout\CustomerData
 */
class ConfigurableItem {
    /**
     * @param \Magento\Checkout\CustomerData\AbstractItem $subject
     * @param \Closure $proceed
     * @param $item
     * @return array
     */
    public function aroundGetItemData(\Magento\Checkout\CustomerData\AbstractItem $subject, \Closure $proceed, \Magento\Quote\Model\Quote\Item $item) {
        $result = $proceed($item);
        $product = $item->getProduct();
        if($product->getTypeId() == 'configurable' && isset($result['product_name'])) {
            if ($option = $item->getOptionByCode('simple_product')) {
                $childProduct = $option->getProduct();
                $result['product_name'] = $childProduct->getName();
            }
        }
        return $result;
    }

}