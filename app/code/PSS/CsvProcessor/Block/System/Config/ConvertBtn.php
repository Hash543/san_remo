<?php

namespace Pss\CsvProcessor\Block\System\Config;

class ConvertBtn extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_template = 'Pss_CsvProcessor::system/config/button/convert.phtml';

    /**
     * Return element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'pss_csv_converter_btn',
                'label' => __('Convert'),
            ]
        );
        return $button->toHtml();
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('pss/csv/convert');
    }
}
