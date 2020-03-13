<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field;

class MapContact extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {

    /**
     * @var \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Contact\Attribute
     */
    protected $contactAttribute;
    /**
     * @var \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Customer\Attribute
     */
    protected $customerAttribute;
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender() {
        $this->addColumn(
            'contact_attribute',
            [
                'label' => __('MDirector Attribute'),
                'renderer' => $this->_getContactAttribute()
            ]
        );
        $this->addColumn(
            'customer_attribute',
            [
                'label' => __('Customer Attribute'),
                'renderer' => $this->_getCustomerAttribute()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Attribute');
    }
    /**
     * Prepare value to render all values
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Exception
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getContactAttribute()->calcOptionHash($row->getData('contact_attribute'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getCustomerAttribute()->calcOptionHash($row->getData('customer_attribute'))] =
            'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
    /**
     * Get Select Html of the groups
     *
     * @return \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Contact\Attribute
     * @throws \Exception
     */
    protected function _getContactAttribute(){
        if (!$this->contactAttribute) {
            $this->contactAttribute = $this->getLayout()->createBlock(
                '\Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Contact\Attribute',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->contactAttribute;
    }
    /**
     * Get Select Html of the groups
     *
     * @return \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Customer\Attribute
     * @throws \Exception
     */
    protected function _getCustomerAttribute(){
        if (!$this->customerAttribute) {
            $this->customerAttribute = $this->getLayout()->createBlock(
                '\Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Customer\Attribute',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->customerAttribute;
    }
}