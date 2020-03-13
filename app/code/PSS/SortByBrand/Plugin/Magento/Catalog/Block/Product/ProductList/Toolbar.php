<?php
namespace PSS\SortByBrand\Plugin\Magento\Catalog\Block\Product\ProductList;

class Toolbar
{
    public function afterSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result,
        $collection
    ) {
        $this->_collection = $collection;
        $currentOrder = $subject->getCurrentOrder();
        $currentDirection = $subject->getCurrentDirection();
         if ($currentOrder) {
            switch ($currentOrder) {
	            case 'manufacturer':
	            		$this->_collection->getSelect()->columns(array('manufacturer_value' => new \Zend_Db_Expr('(SELECT value FROM eav_attribute_option_value WHERE manufacturer_option_value_t1.option_id=eav_attribute_option_value.option_id)')));
	                    $this->_collection->getSelect()->order('manufacturer_value '. $currentDirection);
	            break;

	            default:        
	                $this->_collection
	                    ->setOrder($currentOrder, $currentDirection);
	            break;
            }
        }
        return $result;
    }
}
