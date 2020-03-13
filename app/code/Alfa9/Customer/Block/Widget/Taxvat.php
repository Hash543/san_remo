<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Customer\Block\Widget;

class Taxvat extends \Magento\Customer\Block\Widget\Taxvat {

    /**
     * @override
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Alfa9_Customer::widget/taxvat.phtml');
    }
}