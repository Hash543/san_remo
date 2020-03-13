<?php

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab;

class Advanced extends \Magento\Backend\Block\Widget\Form\Generic
        implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    public $module = "MassStockUpdate";
    protected $_dataHelper = null;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry,
            \Wyomind\MassStockUpdate\Helper\Data $dataHelper,
            \Magento\Framework\Data\FormFactory $formFactory, array $data = []
    )
    {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {


        $model = $this->_coreRegistry->registry('profile');

        $form = $this->_formFactory->create();
        $class = "\Wyomind\\" . $this->module . "\Helper\Data";
        foreach ($class::MODULES as $module) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);
            if ($resource->hasFields()) {
                $fieldset = $form->addFieldset($this->module . '_' . strtolower($module) . '_option', ['legend' => __(ucfirst($module) . ' Settings')]);
                $fieldset = $resource->getFields($fieldset, $this, $this);
            }
        }





        $form->setValues($model->getData());
        $this->setForm($form);


        return parent::_prepareForm();
    }

    public function getProfileId()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getId();
    }

    public function getTabLabel()
    {
        return __('Advanced Settings');
    }

    public function getTabTitle()
    {
        return __('Advanced Settings');
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
