<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use \Alfa9\StoreInfo\Controller\Adminhtml\Stores as StockistController;

class Import extends StockistController
{
    /**
     * Stockists import.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Alfa9_StoreInfo::stores');
        $resultPage->getConfig()->getTitle()->prepend(__('Import'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'), $this->getUrl('storeinfo/stores'));
        
        return $resultPage;
    }
}

