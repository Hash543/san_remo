<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
 
class HidePriceCategories implements ArrayInterface{
    protected $categoryFactory;
    protected $categoryCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
    }
    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false) {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
          
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
          
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }
          
        return $collection;
    }
    public function toOptionArray(){
        $arr = $this->_toArray();
        $ret = [];
        foreach ($arr as $key => $value){
            $ret[] = [
            'value' => $key,
            'label' => $value
            ];
        }
        return $ret;
    }
    private function _toArray(){
        $categories = $this->getCategoryCollection(true, false, false, false);
        $catagoryList = [];
         $catagoryList[0] = __('-- Please Select --');
        foreach ($categories as $category){
       $catagoryList[$category->getEntityId()] = __($this->_getParentName($category->getPath()) . $category->getName() .'(' .$category->getEntityId(). ')');
        }
        return $catagoryList;
    }
    private function _getParentName($path = ''){
        $parentName = '';
        $rootCats = array(1,2);
        $catTree = explode("/", $path);
        array_pop($catTree);
        if($catTree && (count($catTree) > count($rootCats))){
            foreach ($catTree as $catId){
                if(!in_array($catId, $rootCats)){
                    $category = $this->categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }
        return $parentName;
    }
}
