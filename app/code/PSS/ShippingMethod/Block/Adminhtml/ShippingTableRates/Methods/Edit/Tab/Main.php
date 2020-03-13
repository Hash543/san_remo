<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Block\Adminhtml\ShippingTableRates\Methods\Edit\Tab;
/**
 * Class Main
 * @package PSS\ShippingMethod\Block\Adminhtml\ShippingTableRates\Methods\Edit\Tab
 */
class Main extends \Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Main {
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm() {
        /** @var \Amasty\ShippingTableRates\Model\Method $model */
        $model = $this->_coreRegistry->registry('current_amasty_table_method');
        $tab = parent::_prepareForm();
        $form = $tab->getForm();
        $fieldSetStockExpress = $form->addFieldset('stock_express_fieldset', ['legend' => __('Stock Express')]);
        $fieldSetStockExpress->addField(
            'stock_express',
            'select',
            [
                'name' => 'stock_express',
                'label' => __('Apply Stock Express'),
                'title' => __('Apply Stock Express'),
                'note' => __('If the option is selected "Yes", the method will be visible if all its items have the stock express'),
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ]
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return $this;
    }
}