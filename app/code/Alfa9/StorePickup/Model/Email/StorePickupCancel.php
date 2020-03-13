<?php

namespace Alfa9\StorePickup\Model\Email;

class StorePickupCancel extends NotifyToStore
{
    protected $additional = [];

    protected function getAddTo(\Magento\Sales\Model\Order $order = null)
    {
        return [
            'address' => $order->getCustomerEmail(),
            'name' => $order->getBillingAddress()->getName()
        ];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\DataObject
     */
    protected function prepareTemplate(\Magento\Sales\Model\Order $order)
    {
        $orderData = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];
        $additionalData = $this->getAdditionalData();
        $transport = array_merge($orderData, $additionalData);

        return new \Magento\Framework\DataObject($transport);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmailTemplate(\Magento\Sales\Model\Order $order) {
        return $this->scopeConfig->getValue(self::PATH_XML_EMAIL_STORE_PICKUP_CANCEL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    private function getAdditionalData()
    {
        return is_array($this->additional) ? $this->additional : [];
    }

    /**
     * @param array $data
     */
    public function setAdditionalData(array $data)
    {
        $this->additional = $data;
    }
}
