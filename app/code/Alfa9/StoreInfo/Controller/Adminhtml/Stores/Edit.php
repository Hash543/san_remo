<?php

namespace Alfa9\StoreInfo\Controller\Adminhtml\Stores;

use Alfa9\StoreInfo\Controller\Adminhtml\Stores;
use Alfa9\StoreInfo\Controller\RegistryConstants;

class Edit extends Stores
{
    /**
     * Initialize current stockist and set it in the registry.
     *
     * @return int
     */
    public function _initStockist()
    {
        $stockistId = $this->getRequest()->getParam('storeinfo_id');
        $this->coreRegistry->register(RegistryConstants::CURRENT_STOCKIST_ID, $stockistId);

        return $stockistId;
    }

    /**
     * Edit or create stockist
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $stockistId = $this->_initStockist();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Alfa9_StoreInfo::stores');
        $resultPage->getConfig()->getTitle()->prepend(__('StoreInfo'));
        $resultPage->addBreadcrumb(__('StoreInfo'), __('StoreInfo'), $this->getUrl('storeinfo/stores'));

        if ($stockistId === null) {
            $resultPage->addBreadcrumb(__('New Store'), __('New Store'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Store'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Store'), __('Edit Store'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->stockistRepository->getById($stockistId)->getName()
            );
        }
        return $resultPage;
    }
}
