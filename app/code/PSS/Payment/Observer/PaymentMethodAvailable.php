<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Payment\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
/**
 * Class PaymentMethodAvailable
 * @package PSS\Payment\Observer
 */
class PaymentMethodAvailable implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \Amasty\ShippingTableRates\Model\MethodFactory
     */
    protected $methodFactory;
    /**
     * PaymentMethodAvailable constructor.
     * @param \Amasty\ShippingTableRates\Model\MethodFactory $methodFactory
     */
    public function __construct(
        \Amasty\ShippingTableRates\Model\MethodFactory $methodFactory
    ) {
        $this->methodFactory = $methodFactory;
    }

    /**
     *  the Payment depends of their shipping method selected
     * @param Observer $observer
     * @return $this|null
     */
    public function execute(Observer $observer) {

        /** @var \Magento\Framework\DataObject $result */
        $result = $observer->getEvent()->getData('result');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        /** @var \Magento\Payment\Model\Method\AbstractMethod $paymentMethod */
        $paymentMethod = $observer->getEvent()->getData('method_instance');
        if($result->getData('is_available') && $quote) {
            $canPayWithSecondPayment = $this->payShippingWithSecondPayment($quote);
            try {
                $paymentMethodCode = $paymentMethod->getCode();
            } catch (LocalizedException $exception) {
                $paymentMethodCode = '';
            }
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if($paymentMethodCode == 'free' || $shippingMethod == null) {
                return $this;
            } else if(!$canPayWithSecondPayment) {
                $result->setData('is_available', false); //Todo: Hide all the other methods in case of the second method is disable
            }
            $this->checkMethod24724Hours($result, $shippingMethod, $paymentMethodCode);
            $this->checkMethodReserveAndCollect($result, $shippingMethod, $paymentMethodCode);
            $this->checkMethodStorePickup($result, $shippingMethod, $paymentMethodCode);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $result
     * @param string $shippingMethod
     * @param string $paymentMethodCode
     */
    private function checkMethod24724Hours(\Magento\Framework\DataObject &$result, $shippingMethod = '', $paymentMethodCode = '') {
        if (strpos($shippingMethod, 'amstrates') !== false) {
            if($paymentMethodCode == 'reserveandcollect') {
                $result->setData('is_available', false);
            } else {
                if($this->isStockExpress($shippingMethod)) {
                    if(!($paymentMethodCode == 'paypal_express' || $paymentMethodCode == 'redsys')) {
                        $result->setData('is_available', false);
                    }
                }
            }
        }
    }
    /**
     * @param \Magento\Framework\DataObject $result
     * @param string $shippingMethod
     * @param string $paymentMethodCode
     */
    private function checkMethodReserveAndCollect(\Magento\Framework\DataObject &$result, $shippingMethod = '', $paymentMethodCode = '') {
        if (strpos($shippingMethod, 'reserveandcollect') !== false
            && $paymentMethodCode != 'reserveandcollect') {
            $result->setData('is_available', false);
        }
    }
    /**
     * @param \Magento\Framework\DataObject $result
     * @param string $shippingMethod
     * @param string $paymentMethodCode
     */
    private function checkMethodStorePickup(\Magento\Framework\DataObject &$result, $shippingMethod = '', $paymentMethodCode = '') {
        if (strpos($shippingMethod, 'storepickup') !== false
            && !($paymentMethodCode == 'paypal_express' || $paymentMethodCode == 'redsys')) {
            $result->setData('is_available', false);
        }
    }
    /**
     * @param string $shippingMethod
     * @return bool
     */
    private function isStockExpress($shippingMethod) {
        $id = str_replace("amstrates_amstrates", "", $shippingMethod);
        $id = (integer)$id;
        $stockExpress = false;
        if($id) {
            /** @var \Amasty\ShippingTableRates\Model\Method $method */
            $method = $this->methodFactory->create();
            $method->load($id);
            if($method && $method->getId()) {
                $stockExpress = (boolean)$method->getData('stock_express');
            }
        }
        return $stockExpress;
    }

    /**
     * In the site Loyalty we pay with the second payment if the shipping has costs
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    private function payShippingWithSecondPayment($quote) {
        if( $quote->getQuoteCurrencyCode() == \PSS\Loyalty\Helper\Data::CURRENCY_CODE) {

            $shippingAmount = (float)$quote->getShippingAddress()->getBaseShippingAmount();
            $payPalFeeAmount = (float)$quote->getData('base_paypal_fee_amount');
            $grandTotal = (float) $quote->getBaseGrandTotal();
            $shipping = $shippingAmount + $payPalFeeAmount;
            $epsilon = 0.00001;
            if(abs($grandTotal - $shipping) < $epsilon) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }
}