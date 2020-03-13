<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Customer\Block\Form;
/**
 * Class Edit
 * @package Alfa9\Customer\Block\Form
 */
class Edit extends \Magento\Customer\Block\Form\Edit {
    /**
     * @return bool
     */
    public function canEditPassword() {
        $requiredAttributes = $this->customerSession->getRequiredAttributesMissing();
        if($requiredAttributes) {
            return false;
        }
        return true;
    }
}