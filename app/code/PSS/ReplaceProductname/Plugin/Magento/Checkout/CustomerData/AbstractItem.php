<?php
namespace PSS\ReplaceProductname\Plugin\Magento\Checkout\CustomerData;

class AbstractItem
{
    public function aroundGetItemData(\Magento\Checkout\CustomerData\AbstractItem $subject, \Closure $proceed, $item) 
    {
    	$result = $proceed($item);
    	$product = $item->getProduct();
    	$product_name = $product->getName();
	    $optionId = $product->getManufacturer();
	    $brand_name = "";
	    if($optionId){
	        $attr = $product->getResource()->getAttribute('manufacturer');
	        if ($attr->usesSource()) {
	           $brand_name = $attr->getSource()->getOptionText($optionId);
	           $brand_name = str_replace("&#039;","'",$brand_name);
	        }
	    }
	    if($brand_name){ 
	        $product_name = str_ireplace($brand_name,"",$product_name);
	    }
     	$result['product_name'] = $product_name;
     	return $result;
    }
}