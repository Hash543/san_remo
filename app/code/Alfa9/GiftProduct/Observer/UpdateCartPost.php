<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\GiftProduct\Observer;

use Magento\Framework\Event\Observer;

class UpdateCartPost implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Alfa9\GiftProduct\Helper\Data
     */
    protected $giftHelper;
    /**
     * UpdateCartPost constructor.
     * @param \Alfa9\GiftProduct\Helper\Data $giftHelper
     */
    public function __construct(\Alfa9\GiftProduct\Helper\Data $giftHelper)
    {
        $this->giftHelper = $giftHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getData('cart');
        /** @var  \Magento\Framework\DataObject $cartInfo */
        $cartInfo = $observer->getData('info');
        $giftPrice = $this->giftHelper->getProductPriceGift();
        foreach ($cartInfo->getData() as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if($item && $item->getId()) {
                if(isset($itemInfo['is_gift']) && ($itemInfo['is_gift'] == 'on' || $itemInfo['is_gift'] == 1)) {
                    $item->setData('is_gift', 1);
                    $item->setData('gift_price', $giftPrice);
                } else {
                    $item->setData('is_gift', 0);
                    $item->setData('gift_price', 0.0);
                }
            }

        }
    }
}