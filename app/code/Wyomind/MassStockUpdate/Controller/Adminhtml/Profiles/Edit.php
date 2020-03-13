<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Edit extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    public $name = "Mass Stock Update";
    public $module = "MassStockUpdate";

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Catalog::catalog");
        $resultPage->addBreadcrumb(__($this->name), __($this->name));
        $resultPage->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

        $this->_logger->notice("-------------------------------------------------");

        $this->_logger->notice(__("Opening profile #%1", $id));

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This profileno longer exists.'));
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
            }
        }

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__('Modify profile : ') . $model->getName()) : __('New profile'));

        $this->_coreRegistry->register('profile', $model);

        return $resultPage;
    }

}
