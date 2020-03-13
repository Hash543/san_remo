<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Checkout\Plugin\Checkout;

class LayoutProcessor
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * Layout constructor.
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $jsLayout)
    {
        $jsLayout = $this->addCustomValidations($jsLayout);
        return $jsLayout;
    }

    /**
     * Add Custom Validations to the checkout, there is possible issue with Amasty because Vat_id is not working well
     * @param array $jsLayout
     * @return array
     */
    private function addCustomValidations($jsLayout){

        if(isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                     ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] as $key => &$field) {
                if($key == 'vat_id') {
                    $validations = [];
                    if(isset($field['validation'])) {
                        $validations = $field['validation'];
                    }
                    $field['validation'] = array_merge($validations, ['validate-nif-nie' => true]);
                }
            }
        }
        if(isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) {
            $configuration =$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) && $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                             ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children'] as $key => &$field) {
                        if($key == 'vat_id') {
                            $validations = [];
                            if(isset($field['validation'])) {
                                $validations = $field['validation'];
                            }
                            $field['validation'] = array_merge($validations, ['validate-nif-nie' => true]);
                        }
                    }
                }
            }
        }
        return $jsLayout;
    }
}