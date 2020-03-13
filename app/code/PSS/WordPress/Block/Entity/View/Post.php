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
namespace PSS\WordPress\Block\Entity\View;

abstract class Post extends \Magento\Framework\View\Element\Template {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \PSS\WordPress\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;
    /**
     * Post constructor.
     * @param \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \PSS\WordPress\Helper\Data $dataHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \PSS\WordPress\Helper\Data $dataHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
        $this->postCollectionFactory = $postCollectionFactory;
    }
    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Get the Post Related to the Product
     * @return \PSS\WordPress\Model\ResourceModel\Post\Collection|null
     */
    public function getPostList() {
        $postList = null;
        $associationTypeId = $this->dataHelper->getTypeIdFromProductToPost();
        $product = $this->getProduct();
        if ($product !== null && $associationTypeId !== null) {
            $postIds = $this->dataHelper->getPostIdsRelated($associationTypeId, $product->getId());
            if (count($postIds) > 0 ){
                /** @var \PSS\WordPress\Model\ResourceModel\Post\Collection $postCollection */
                $postCollection = $this->postCollectionFactory->create();
                $postCollection->addFieldToFilter('ID', array('in', $postIds));
                $postList = $postCollection;
            }
        }
        return $postList;
    }

    /**
     * Choose the template to be rendered
     * @param \PSS\WordPress\Model\Post $post
     * @return string
     */
    public function renderPost(\PSS\WordPress\Model\Post $post)
    {
        $postBlock = null;
        $postType = null;
        try {
            $postBlock = $this->getLayout()->createBlock('PSS\WordPress\Block\Post')->setPost($post);
            $postType = $post->getPostType();
        } catch (\Exception $e) {
            return null;
        }
        $templatesToTry = [
            'PSS_WordPress::post/list/renderer/' .$postType . '.phtml',
            'PSS_WordPress::post/list/renderer/default.phtml'
        ];

        foreach ($templatesToTry as $templateToTry) {
            if ($this->getTemplateFile($templateToTry)) {
                $postBlock->setTemplate($templateToTry);
                break;
            }
        }
        return $postBlock->toHtml();
    }
}