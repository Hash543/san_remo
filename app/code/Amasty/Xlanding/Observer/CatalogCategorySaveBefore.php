<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Observer;

use Amasty\Xlanding\Api\Data\PageInterface;
use Amasty\Xlanding\Api\PageRepositoryInterface;
use Amasty\Xlanding\Model\ResourceModel\Page as PageResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CatalogCategorySaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    const DO_NOT_SYNC = 'am_xlanding_do_not_sync_category';
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PageResource $pageResource,
        CategoryCollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->messageManager = $messageManager;
        $this->pageResource = $pageResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getDataObject();
        if ($category->hasDataChanges() && !$category->hasData(self::DO_NOT_SYNC)) {
            if ($category->getData(PageInterface::IS_CATEGORY_DYNAMIC)) {
                try {
                    $pageId = $category->getData(PageInterface::DYNAMIC_CATEGORY_PAGE_ID);
                    if (!$pageId) {
                        $this->makeCategoryStatic($category);
                    }
                } catch (\Exception $e) {
                    $this->makeCategoryStatic($category);
                }
            }

            $this->syncLandingPages($category);
        }
        $origDynamicData = $category->getOrigData(PageInterface::IS_CATEGORY_DYNAMIC);
        $dynamicData = $category->getData(PageInterface::IS_CATEGORY_DYNAMIC);
        $origDynamicPageId = $category->getOrigData(PageInterface::DYNAMIC_CATEGORY_PAGE_ID);
        $dynamicPageId = $category->getData(PageInterface::DYNAMIC_CATEGORY_PAGE_ID);
        if ($origDynamicData != $dynamicData || $origDynamicPageId != $dynamicPageId) {
            $category->setAffectedProductIds([0]);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    private function makeCategoryStatic(\Magento\Catalog\Model\Category $category)
    {
        if (!$this->moduleManager->isEnabled('Amasty_VisualMerch')) {
            return $category->setData(PageInterface::IS_CATEGORY_DYNAMIC, false);
        }
        return $category;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    private function syncLandingPages(\Magento\Catalog\Model\Category $category)
    {
        $pageId = $category->getData(PageInterface::DYNAMIC_CATEGORY_PAGE_ID);
        if ($pageId && $category->getData(PageInterface::IS_CATEGORY_DYNAMIC)) {

            $categoryToStaticCollection = $this->categoryCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['nin' => $category->getId()])
                ->addAttributeToFilter(PageInterface::DYNAMIC_CATEGORY_PAGE_ID, $pageId)
                ->addAttributeToFilter(PageInterface::IS_CATEGORY_DYNAMIC, true);
            foreach ($categoryToStaticCollection as $proceedCategory) {
                $proceedCategory->setData(PageInterface::IS_CATEGORY_DYNAMIC, false);
                $proceedCategory->setData(self::DO_NOT_SYNC, true);
            }

            $categoryToStaticCollection->save();
            $this->pageResource->syncDynamicPages($category->getId(), $pageId);
        }
    }
}
