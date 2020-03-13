<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Model\Quote\Address\Total;

use Magento\Framework\Exception\NoSuchEntityException;
use PSS\Paypal\Constants\PayPalConstants;
/**
 * Class FeePaypal
 * @package PSS\Paypal\Model\Quote\Address\Total
 */
class PayPalFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {
    /**#@+
     * Constants defined for charge when the customer choose paypal as their payment method
     */
    const PERCENT_FEE_PAYPAL = 3.40;
    const AMOUNT_FEE_PAYPAL = 0.35;
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * PayPalFee constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->taxConfig = $taxConfig;
    }


    /**
     * {@inheritdoc}
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (count($shippingAssignment->getItems()) == 0) {
            return $this;
        }
        $totals = $total->getAllTotalAmounts();
        $payment = $quote->getPayment();
        $payPalFeeAmount = 0.000;
        $basePayPalFeeAmount = 0.000;
        $shippingAmount = 0.000;
        $shippingBaseAmount = 0.000;
        if($quote && $quote->getShippingAddress()) {
            $shippingBaseAmount = (float) $quote->getShippingAddress()->getBaseShippingInclTax();
            $shippingAmount = (float)$quote->getShippingAddress()->getShippingInclTax();
        }
        if($payment && $this->checkIfMethodIsPayPal($payment->getMethod())) { // if the selected method is paypal we calculate the fee
            $subtotalInclTax = $total->getData('subtotal_incl_tax') ? (float)$total->getData('subtotal_incl_tax') : 0;
            $baseSubtotalInclTax = $total->getData('base_subtotal_incl_tax') ? (float)$total->getData('base_subtotal_incl_tax') : 0;
            if($subtotalInclTax > 0 && $baseSubtotalInclTax > 0) {
                $discount = $total->getData('discount_amount') ? (float)$total->getData('discount_amount'): 0;
                $baseDiscountAmount = $total->getData('base_discount_amount') ? (float)$total->getData('base_discount_amount'): 0;
                $subtotalDiscountTaxShipping = $subtotalInclTax + $shippingAmount + $discount;
                $baseSubtotalDiscountTaxShipping = $baseSubtotalInclTax + $shippingBaseAmount + $baseDiscountAmount;
            } else {
                $subtotalWithDiscount = $quote->getSubtotalWithDiscount();
                $baseSubtotalWithDiscount = $quote->getBaseSubtotalWithDiscount();
                $tax = 0.000;
                $baseTax = 0.000;
                if (isset($totals['tax'])) {
                    $tax = (float)$totals['tax'];
                    $baseTax = (float)$this->convertToBaseCurrency($tax);
                }
                $subtotalDiscountTaxShipping = $subtotalWithDiscount + $shippingAmount + $tax;
                $baseSubtotalDiscountTaxShipping = $baseSubtotalWithDiscount + $shippingBaseAmount + $baseTax;
            }
            $payPalFeeAmount = (float)(self::AMOUNT_FEE_PAYPAL + ((self::PERCENT_FEE_PAYPAL/100) * $subtotalDiscountTaxShipping));
            $basePayPalFeeAmount = (float)(self::AMOUNT_FEE_PAYPAL + ((self::PERCENT_FEE_PAYPAL/100) * $baseSubtotalDiscountTaxShipping));
        }
        $quote->setData(PayPalConstants::TOTAL_CODE, $payPalFeeAmount);
        $quote->setData(PayPalConstants::BASE_TOTAL_CODE, $basePayPalFeeAmount);
        $total->setData(PayPalConstants::TOTAL_CODE, $payPalFeeAmount);
        $total->setData(PayPalConstants::BASE_TOTAL_CODE, $basePayPalFeeAmount);
        $total->setTotalAmount(PayPalConstants::TOTAL_CODE, $payPalFeeAmount);
        $total->setBaseTotalAmount(PayPalConstants::TOTAL_CODE, $basePayPalFeeAmount);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $value = $total->getData(PayPalConstants::TOTAL_CODE) ? $total->getData(PayPalConstants::TOTAL_CODE) : 0.00 ;
        $baseValue = $total->getData(PayPalConstants::BASE_TOTAL_CODE) ? $total->getData(PayPalConstants::BASE_TOTAL_CODE) : 0.00;

        return [
            'code' => PayPalConstants::TOTAL_CODE,
            'title' => __('PayPal Fee'),
            'base_value' => $baseValue,
            'value' => $value
        ];
    }
    /**
     * @param string $methodName
     * @return bool
     */
    private function checkIfMethodIsPayPal($methodName) {
        if($methodName && is_string($methodName) && (strpos($methodName, 'paypal') !== false)) {
                return true;
        }
        return false;
    }

    /**
     * Convert the price to Base currency
     * @param $price
     * @return float|int
     */
    public function convertToBaseCurrency($price) {
        /** @var \Magento\Store\Model\Store $store */
        try {
            $store = $this->storeManager->getStore();
        }catch (NoSuchEntityException $exception) {
            $store = null;
        }
        if($store) {
            $currentCurrency = $store->getCurrentCurrency()->getCode();
            $baseCurrency = $store->getBaseCurrency()->getCode();
            $rate = $this->currencyFactory->create()->load($currentCurrency)->getAnyRate($baseCurrency);
            $price = $price * $rate;
        }
        return $price;
    }
}