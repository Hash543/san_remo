<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field;

class LastSyncDate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($value = $element->getData('value')) {
            $html = $this->_localeDate->formatDate($value);
        } else {
            $html = __('not yet synced');
        }
        return $html;
    }
}