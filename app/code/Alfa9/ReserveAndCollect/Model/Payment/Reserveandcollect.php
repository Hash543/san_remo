<?php

namespace Alfa9\ReserveAndCollect\Model\Payment;

class Reserveandcollect extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_RESERVE_AND_COLLECT = 'reserveandcollect';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_RESERVE_AND_COLLECT;
    protected $_isOffline = true;

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    /*public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        // TODO: Add logic to query product stock on stores.

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $code = explode('_', $shippingMethod);
        if(!is_array($code)) return false;

        return $code[0] == 'storepickup';
    }*/
}
