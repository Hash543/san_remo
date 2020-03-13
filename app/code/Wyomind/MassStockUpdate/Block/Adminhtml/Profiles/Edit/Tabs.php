<?php

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('entries_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Profile Configuration'));
    }

}
