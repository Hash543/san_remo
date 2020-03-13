<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Constants;
/**
 * Class PayPalConstants
 * @package PSS\Paypal\Constants
 */
abstract class PayPalConstants {
    /**#@+
     * Constants defined for charge when the customer choose paypal as their payment method
     */
    const TOTAL_CODE = 'paypal_fee_amount';
    const BASE_TOTAL_CODE = 'base_paypal_fee_amount';
}