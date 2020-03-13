<?php

namespace PSS\AddStepToCheckout\Controller\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;

/**
 * Class UpdatePost
 * @package PSS\AddStepToCheckout\Controller\Cart
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart\UpdatePost {

    /**
     * {@inheritdoc}
     */
    public function execute() {
        parent::execute();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->cart->getCustomerSession()->getCustomerId()) {
            $resultRedirect->setPath('steps/steps/singup');
        } else {
            $resultRedirect->setPath('checkout/index/index');
        }
        return $resultRedirect;
    }
}
