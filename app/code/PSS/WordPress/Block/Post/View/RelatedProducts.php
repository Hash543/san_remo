<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Block\Post\View;

use \Magento\Framework\View\Element\Template\Context as MagentoContext;
use PSS\WordPress\Model\Context as WPContext;

class RelatedProducts extends \PSS\WordPress\Block\Post {

    /**
     * @var \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory
     */
    protected $_associationCollectionFactory;

    /**
     * @var \PSS\WordPress\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * RelatedProducts constructor.
     * @param \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory $associationCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \PSS\WordPress\Helper\Data $dataHelper
     * @param MagentoContext $context
     * @param WPContext $wpContext
     * @param array $data
     */
    public function __construct(
        \PSS\WordPress\Model\ResourceModel\Association\CollectionFactory $associationCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \PSS\WordPress\Helper\Data $dataHelper,
        MagentoContext $context,
        WPContext $wpContext,
        array $data = [])
    {
        parent::__construct($context, $wpContext, $data);
        $this->_associationCollectionFactory = $associationCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_dataHelper = $dataHelper;
        $this->imageBuilder = $imageBuilder;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb|null
     */
    public function getProductsRelated(){
        $products = null;
        $post = $this->getPost();
        $typeId = $this->_dataHelper->getTypeIdFromProductToPost();
        if($post && $post->getId() && $typeId) {
            $postId = $post->getId();
            $productsId = $this->getProductsId($typeId, $postId);
            if(count($productsId) === 0 ){
                return $products;
            }
            $collection = $this->productCollectionFactory->create();
            $collection = $collection->addFieldToFilter('entity_id', array('in', $productsId))->addAttributeToSelect('*')->load();
            if (count($collection) > 0){
                $products = $collection;
            }
        }
        return $products;
    }

    /**
     * @param int $typeId
     * @param int $postId
     * @return array
     */
    public function getProductsId( $typeId, $postId){
        $productsId = [];
        $associationCollection = $this->_associationCollectionFactory->create();
        /** @var \PSS\WordPress\Model\ResourceModel\Association\Collection $associationCollection */
        $associationCollection = $associationCollection->getRelationMagentoObjects($typeId, $postId);
        /** @var \PSS\WordPress\Model\Association $item */
        foreach ($associationCollection as $item) {
            $productsId[] = $item->getData('object_id');

        }
        return $productsId;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = []){
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}