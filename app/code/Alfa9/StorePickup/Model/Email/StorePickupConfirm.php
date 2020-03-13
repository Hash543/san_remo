<?php
namespace Alfa9\StorePickup\Model\Email;

use Alfa9\StorePickup\Model\Carrier\StorePickup;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;

class StorePickupConfirm extends NotifyToStore
{
    protected function getAddTo(\Magento\Sales\Model\Order $order = null)
    {
        return [
            'address' => $order->getCustomerEmail(),
            'name' => $order->getBillingAddress()->getName()
        ];
    }

    protected function prepareTemplate(\Magento\Sales\Model\Order $order)
    {
        $shippingMethod = $order->getShippingMethod();
        $subject = '';

        if(strpos($shippingMethod, StorePickup::SHIPPING_CODE) !== false){
            $subject = "Confirmación Click&Collect #{$order->getIncrementId()}";
        }elseif(strpos($shippingMethod, ReserveAndCollect::SHIPPING_CODE) !== false){
            $subject = "Confirmación Reserva #{$order->getIncrementId()}";
        }

        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'subject' => $subject,
        ];
        return new \Magento\Framework\DataObject($transport);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmailTemplate(\Magento\Sales\Model\Order $order) {
        return $this->scopeConfig->getValue(self::PATH_XML_EMAIL_STORE_PICKUP_CONFIRM, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
