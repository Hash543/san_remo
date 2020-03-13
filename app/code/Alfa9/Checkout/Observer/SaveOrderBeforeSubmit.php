<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrderBeforeSubmit implements ObserverInterface
{

    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $attributes = [
        'comment_order',
        'delivery_date'
    ];

    /**
     * Should review why the field set is not working
     * @deprecated
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($this->attributes as $attribute) {
            $order->setData($attribute, $quote->getData($attribute));
        }
        return $this;
    }
}