<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\GiftProduct\Plugin\Model\Quote;

class QuoteItemToOrderItem {
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {

        /** @var  \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $proceed($item, $additional);
        $orderItem->setData('is_gift',$item->getData('is_gift'));
        $orderItem->setData('gift_price',$item->getData('gift_price'));
        return $orderItem;
    }
}