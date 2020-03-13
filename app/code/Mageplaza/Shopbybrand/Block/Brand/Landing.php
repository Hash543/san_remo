<?php
namespace Mageplaza\Shopbybrand\Block\Brand;

use Magento\Cms\Model\BlockFactory;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Mageplaza\Shopbybrand\Helper\Data;

class Landing extends \Magento\Framework\View\Element\Template
{
    protected $_brandFactory;

    protected $_blockFactory;

    protected $_productCollectionFactory;

    protected $_categoryHelper;

    protected $_attributeFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
    */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        BlockFactory $blockFactory,
        BrandFactory $brandFactory,
        CollectionFactory $productCollectionFactory,
        Data $helper,
        array $data = []
    ) {
        $this->_brandFactory = $brandFactory;
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->_collectionFactory = $collecionFactory;
        $this->_categoryHelper = $categoryHelper;
        $this->_blockFactory = $blockFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $brand = $this->getBrand();

        $description = $brand->getMetaDescription();
        if ($description) {
            $this->pageConfig->setDescription($description);
        }
        $keywords = $brand->getMetaKeywords();
        if ($keywords) {
            $this->pageConfig->setKeywords($keywords);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        $brandId = $this->getRequest()->getParam('manufacturer');
        $brands = $this->_brandFactory->create();
        $collection = $brands->getBrandCollection(null, ['main_table.option_id' => $brandId]);
        foreach ($collection as $brandCollection) {
            $brandCollection->getData();
        }
        return $brandCollection;
    }

    /**
     * @return string
     */
    public function getBrandImage()
    {
        $brand = $this->getBrand();
        $brandImageUrl =$this->helper->getBrandImageUrl($brand);
        return $brandImageUrl;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMainContentBlock()
    {
        $brand = $this->getBrand();
        if(!$brand->getPremiumLandingBlock()){
            return '';
        }

        $block = $this->getBrand()->getPremiumLandingBlock();

        $cmsBlock = $this->_blockFactory->create();
        $cmsBlock->load($block, 'identifier');

        $html = '';
        if ($cmsBlock && $cmsBlock->getId()) {
            $html = $this->getLayout()->createBlock('Magento\Cms\Block\Block')
                ->setBlockId($cmsBlock->getId())
                ->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSidebarBlock()
    {
        $brand = $this->getBrand();
        if(!$brand->getPremiumSidebarBlock()){
            return '';
        }

        $block = $this->getBrand()->getPremiumSidebarBlock();

        $cmsBlock = $this->_blockFactory->create();
        $cmsBlock->load($block, 'identifier');

        $html = '';
        if ($cmsBlock && $cmsBlock->getId()) {
            $html = $this->getLayout()->createBlock('Magento\Cms\Block\Block')
                ->setBlockId($cmsBlock->getId())
                ->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getBrandCategories($isActive = true, $level = false) {
        $brand = $this->getBrand();
        $option_id = $brand->getOptionId();  
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToFilter($this->getAttributeCode(), $option_id);
        $catIds=[];         
        foreach($productCollection as $catId){
             $proCats = $catId->getCategoryIds();
             $catIds= array_merge($catIds, $proCats);   
        }
        $categoryIds = array_unique($catIds);
        $categories = $this->_collectionFactory->create()
                                 ->addAttributeToSelect('*')
                                 ->addAttributeToFilter('entity_id', $categoryIds)
                                 ->addAttributeToFilter('parent_id', array('gt' => 1));
        if ($isActive) {
            $categories->addIsActiveFilter();
        }
        return $categories;
    }

    public function getCategoryUrl($_category)
    {
        $brand = $this->getBrand();
        $option_id = $brand->getOptionId();  
        $categoryUrl = $this->_categoryHelper->getCategoryUrl($_category);
        $brandFilteredUrl = $categoryUrl.'?'.$this->getAttributeCode().'='.$option_id;
        return $brandFilteredUrl;
    }

    public function getAttributeCode()
    {
        $brand = $this->getBrand();
        $attribute_id = $brand->getAttributeId();
        $eavModel = $this->_attributeFactory->create()->load($attribute_id);
        $attributeCode = $eavModel->getAttributeCode();
        return $attributeCode;
    }
    

}