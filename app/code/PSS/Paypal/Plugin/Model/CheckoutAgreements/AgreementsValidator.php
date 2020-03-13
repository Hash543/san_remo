<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Plugin\Model\CheckoutAgreements;

class AgreementsValidator {
    /**
     * @var bool
     */
    private $isSkipValidation = false;
    /**
     * @see \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation
     * @see \Magento\CheckoutAgreements\Model\AgreementsValidator::isValid
     * @plugin around
     * @param \Magento\CheckoutAgreements\Model\AgreementsValidator $subject
     * @param \Closure $proceed
     * @param int[] $agreementIds
     * @return bool
     */
    public function aroundIsValid(
        \Magento\CheckoutAgreements\Model\AgreementsValidator $subject, \Closure $proceed, $agreementIds
    ) {
        if ($this->isSkipValidation)  {
            return true;
        } else {
            return $proceed($agreementIds);
        }
    }
    /**
     * @param bool $isSkipValidation
     * @return AgreementsValidator
     */
    public function setIsSkipValidation($isSkipValidation)
    {
        $this->isSkipValidation = $isSkipValidation;
        return $this;
    }
}