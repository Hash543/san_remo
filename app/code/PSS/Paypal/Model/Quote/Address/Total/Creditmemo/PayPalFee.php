<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Model\Quote\Address\Total\Creditmemo;

use PSS\Paypal\Constants\PayPalConstants;
/**
 * Class FeePaypal
 * @package PSS\Paypal\Model\Quote\Address\Total
 */
class PayPalFee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal {

    /**
     * @inheritdoc
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo) {
        parent::collect($creditmemo);
        $order = $creditmemo->getOrder();
        $payPalFeeAmount = 0.000;
        $basePayPalFeeAmount = 0.000;
        if($order) {
            $payPalFeeAmount = $order->getData(PayPalConstants::TOTAL_CODE) ? (float) $order->getData(PayPalConstants::TOTAL_CODE) : 0.000;
            $basePayPalFeeAmount = $order->getData(PayPalConstants::BASE_TOTAL_CODE) ? (float)$order->getData(PayPalConstants::BASE_TOTAL_CODE) : 0.000;
        }
        $creditmemo->setData(PayPalConstants::TOTAL_CODE, $payPalFeeAmount);
        $creditmemo->setData(PayPalConstants::BASE_TOTAL_CODE, $basePayPalFeeAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $payPalFeeAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basePayPalFeeAmount);
        return $this;
    }

}