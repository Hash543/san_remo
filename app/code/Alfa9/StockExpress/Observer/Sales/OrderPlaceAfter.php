<?php


namespace Alfa9\StockExpress\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{


    protected $_productRepository;
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, //log injection
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
       // parent::__construct($data);
    }


    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $order_id = $order->getIncrementId();  
        foreach($order->getAllItems() as $item){
            $productId = $item->getProductId();
            $product = $this->_productRepository->getById($productId);
            $qtyOrdered = $item->getQtyOrdered();
            $express_stock = $item->getProduct()->getData('express_stock'); 
            if($express_stock > 0){
                $attributeValue = $express_stock - $qtyOrdered;
                if($express_stock < 0) $attributeValue = 0;
                $product->setCustomAttribute('express_stock', $attributeValue);
                $this->_productRepository->save($product);
            }
        }
    }
}