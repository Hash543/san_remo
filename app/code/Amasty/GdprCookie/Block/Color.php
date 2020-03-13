<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Block;

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setData('readonly', 1);
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type ="text/x-magento-init"> 
            {
                "*": {
                    "Amasty_GdprCookie/js/color": {
                        "htmlId":"' . $element->getHtmlId() . '",
                        "elData":"' . $value . '"
                    }
                }
            }
        </script>';

        return $html;
    }
}
