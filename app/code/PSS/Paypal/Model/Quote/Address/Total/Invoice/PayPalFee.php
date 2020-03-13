<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Model\Quote\Address\Total\Invoice;

use PSS\Paypal\Constants\PayPalConstants;
/**
 * Class FeePaypal
 * @package PSS\Paypal\Model\Quote\Address\Total
 */
class PayPalFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal {

    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice) {
        parent::collect($invoice);
        $order = $invoice->getOrder();
        $payPalFeeAmount = 0.000;
        $basePayPalFeeAmount = 0.000;
        if($order) {
            $payPalFeeAmount = $order->getData(PayPalConstants::TOTAL_CODE) ? (float) $order->getData(PayPalConstants::TOTAL_CODE) : 0.000;
            $basePayPalFeeAmount = $order->getData(PayPalConstants::BASE_TOTAL_CODE) ? (float)$order->getData(PayPalConstants::BASE_TOTAL_CODE) : 0.000;
        }
        $invoice->setData(PayPalConstants::TOTAL_CODE, $payPalFeeAmount);
        $invoice->setData(PayPalConstants::BASE_TOTAL_CODE, $basePayPalFeeAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $payPalFeeAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePayPalFeeAmount);
        return $this;
    }

}