<?php

namespace PSS\DobFix\Block\Widget;

class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/dob.phtml');
    }

    /**
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        $extraParams = [
            "'validate-date-au':'dd/mm/yyyy'"
        ];

        if ($this->isRequired()) {
            $extraParams[] = 'required:true';
        }

        $extraParams = implode(', ', $extraParams);

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($extraParams)) . '"';
    }
}