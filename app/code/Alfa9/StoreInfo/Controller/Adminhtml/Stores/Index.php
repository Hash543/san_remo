<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use \Alfa9\StoreInfo\Controller\Adminhtml\Stores as StockistController;

class Index extends StockistController
{
    /**
     * Stockists list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Alfa9_StoreInfo::stores');
        $resultPage->getConfig()->getTitle()->prepend(__('StoreInfo'));
        $resultPage->addBreadcrumb(__('StoreInfo'), __('StoreInfo'));
        return $resultPage;
    }
}
