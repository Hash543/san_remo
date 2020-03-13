<?php

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab;

class Cron extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_coreHelper;
    protected $_module="massstockupdate";

    public function __construct(
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory, array $data = []
    )
    {
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {

        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    public function getCronInterval(){


        return $this->_coreHelper->getStoreConfig($this->_module."/settings/cron_interval");
    }
    public function getCronSettings()
    {

        $model = $this->_coreRegistry->registry('profile');
        return $model->getCronSettings();
    }

    public function getTabLabel()
    {
        return __('Scheduled tasks');
    }

    public function getTabTitle()
    {
        return __('Scheduled tasks');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}
