<?php

namespace PSS\WishlistSimple\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class WishlistAddProduct implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    public function __construct(
        \Magento\Catalog\Model\Product $product
    )
    {
        $this->product = $product;
    }

    public function execute(EventObserver $observer)
    {
        $productId = $observer->getItem()->getOptionByCode('simple_product');
        if ($productId){
            $productId->getValue();
            $product = $this->product->load($productId);

            if (!$product->getId()) {
                return;
            }

            return $product->getSku();
        }
    }
}