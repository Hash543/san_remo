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
namespace PSS\WordPress\Block\Cms\View;

use Magento\Framework\Exception\LocalizedException;

class Post extends \PSS\WordPress\Block\PostEntity {

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * Post constructor.
     * @param \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \PSS\WordPress\Helper\Data $dataHelper
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \PSS\WordPress\Helper\Data $dataHelper,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->page = $page;
        parent::__construct($postCollectionFactory, $dataHelper, $context, $registry, $data);
    }

    /**
     * Retrieve Page instance
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getPage()
    {
        if (!$this->hasData('page')) {
            $pageId = null;
            $storeId = null;
            try {
                $pageId = $this->getPageId();
                $storeId = $this->_storeManager->getStore()->getId();
            }catch (LocalizedException $exception) {

            }
            if ($pageId && $storeId) {
                /** @var \Magento\Cms\Model\Page $page */
                $page = $this->pageFactory->create();
                $page->setStoreId($storeId)->load($pageId, 'identifier');
            } else {
                $page = $this->page;
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }
    /**
     * {@inheritdoc}
     */
    public function getAssociationTypeId() {
        return $this->dataHelper->getTypeIdFromCmsPageToPost();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId(){
        $page = $this->getPage();
        if($page) {
            return $page->getId();
        }
        return null;
    }

}