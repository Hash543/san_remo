<?php

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Index extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    public $name = "Mass Stock Update";

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system_convert");
        $resultPage->getConfig()->getTitle()->prepend(__($this->name . ' > Profiles'));
        $resultPage->addBreadcrumb(__($this->name), __($this->name));
        return $resultPage;
    }

}
