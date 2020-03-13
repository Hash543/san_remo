<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Observer;

use PSS\Paypal\Constants\PayPalConstants;
/**
 * Class AddPaymentFeeItem
 * @package PSS\Paypal\Observer
 */
class AddPaymentFeeItem implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getData('cart');
        $quote = $this->checkoutSession->getQuote();
        $totalPaypalFee = $quote ? (float)$quote->getData(PayPalConstants::TOTAL_CODE) : 0.00;
        if($totalPaypalFee > 0.0001) {
            $cart->addCustomItem(__('PayPal Fee'), 1, $totalPaypalFee);
        }
    }
}