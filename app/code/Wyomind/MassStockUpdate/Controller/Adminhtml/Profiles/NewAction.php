<?php
namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;
 
class NewAction extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{
    
    public function execute()
    {
        return $this->_resultForwardFactory->create()->forward("edit");
    }
}
