<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field;

class MapGroup extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    /**
     * @var \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\ListGroup
     */
    protected $listRenderer;
    /**
     * @var \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\GroupCustomer
     */
    protected $customerGroupRenderer;

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender() {
        $this->listRenderer  = null;
        $this->customerGroupRenderer = null;
        $this->addColumn(
            'list',
            [
                'label' => __('List'),
                'renderer' => $this->_getListRenderer()
            ]
        );
        $this->addColumn(
            'customer_group',
            [
                'label' => __('Customer Group'),
                'renderer' => $this->_getGroupRenderer()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add List');
    }

    /**
     * Get Select Html of the groups
     *
     * @return \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\GroupCustomer
     * @throws \Exception
     */
    protected function _getGroupRenderer(){
        if (!$this->customerGroupRenderer) {
            $this->customerGroupRenderer = $this->getLayout()->createBlock(
                '\Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\GroupCustomer',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->customerGroupRenderer;
    }
    /**
     * Get Select Html of the List
     *
     * @return \Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\ListGroup
     * @throws \Exception
     */
    protected function _getListRenderer(){
        if (!$this->listRenderer) {
            $this->listRenderer = $this->getLayout()->createBlock(
                '\Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\ListGroup',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->listRenderer;
    }
    /**
     * Prepare value to render all values
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Exception
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getListRenderer()->calcOptionHash($row->getData('list'))] =
            'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}

