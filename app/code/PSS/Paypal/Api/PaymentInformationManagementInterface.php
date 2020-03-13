<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
/**
 * Interface PaymentInformationManagementInterface
 * @package PSS\Paypal\Api
 */
interface PaymentInformationManagementInterface {
    /**
     * Set payment information for a specified cart.
     *
     * @param int                   $cartId
     * @param PaymentInterface      $paymentMethod
     * @param AddressInterface|null $billingAddress
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function savePaymentInformationAndGetTotals(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    );
}