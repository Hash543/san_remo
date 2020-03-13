<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\GiftProduct\Model\Quote\Address\Total;

class Subtotal extends \Magento\Quote\Model\Quote\Address\Total\Subtotal {

    /**
     * Increase the Price of the item if it's a gift, it's not a good practice override this class
     * But I don't find a place to add a extra price to a product
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @param float $finalPrice
     * @param float $originalPrice
     * @return $this
     * @throws \Exception
     * @override
     */
    protected function _calculateRowTotal($item, $finalPrice, $originalPrice)
    {   if($item->getData('is_gift')) {
            $finalPrice = $finalPrice + $item->getData('gift_price');
        }
        if (!$originalPrice) {
            $originalPrice = $finalPrice;
        }
        $item->setPrice($finalPrice)->setBaseOriginalPrice($originalPrice);
        $item->calcRowTotal();
        return $this;
    }
}