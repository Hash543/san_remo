<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Wyomind\MassStockUpdate\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    

   
    
    const SETTINGS_LOG = "massstockupdate/settings/log";
    const SETTINGS_NB_PREVIEW = "massstockupdate/settings/nb_preview";
    
    protected $_coreHelper = null;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper
    ) {
        parent::__construct($context);
        $this->_coreHelper = $coreHelper;
    }
        
   

    public function getSettingsLog()
    {
        return $this->_coreHelper->getDefaultConfig($this::SETTINGS_LOG);
    }
    
    public function getSettingsNbPreview()
    {
        return $this->_coreHelper->getDefaultConfig($this::SETTINGS_NB_PREVIEW);
    }
}
