<?php
/**
 * @author Israel Yasis
 */
namespace PSS\SampleProducts\Observer\Quote;

use \PSS\SampleProducts\Helper\Data as SampleProductsHelper;

/**
 * Class RemoveItem
 * @package PSS\SampleProducts\Observer\Quote
 */
class RemoveItem implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * RemoveItem constructor.
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * Check if the Cart has still Products
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getData('quote_item');
        $hasProduct = false;
        if($quoteItem && $quoteItem->getId()) {
            $quote = $quoteItem->getQuote();
            if($quote && $quote->getId()) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if(!SampleProductsHelper::isSampleProduct($item->getProduct())) {
                        $hasProduct = true;
                        break;
                    }
                }
            }
        }
        /** Clean the Cart in case there are no products */
        if(!$hasProduct) {
            $cart = $this->cart->truncate();
            $cart->saveQuote();
        }
    }
}