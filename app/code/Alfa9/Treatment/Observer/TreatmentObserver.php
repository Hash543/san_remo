<?php

namespace Alfa9\Treatment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TreatmentObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $order;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $product;
    /**
     * @var \Alfa9\Treatment\Model\TreatmentFactory
     */
    private $treatmentFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Catalog\Model\ProductFactory $product,
        \Alfa9\Treatment\Model\TreatmentFactory $treatmentFactory
    )
    {
        $this->order = $order;
        $this->product = $product;
        $this->treatmentFactory = $treatmentFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getData('order_ids');
        foreach ($orderIds as $orderId)
        {
            $order = $this->order->create()->load($orderId);
            $orderItems = $order->getAllVisibleItems();
            $currentDate = $order->getCreatedAt();
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($orderItems as $orderItem)
            {
                $productId = $orderItem->getProductId();
                if ($orderItem->getProductType() === 'configurable')
                {
                    $childrens = $orderItem->getChildrenItems();
                    foreach ($childrens as $children){
                        $productId = $children->getProductId();
                    }
                }
                $product = $this->product->create()->load($productId);
                $treat = $product->getTreatmentDays();
                if(isset($treat) && $treat > 0)
                {
                    $nextDate = date('Y-m-d',strtotime($currentDate."+{$treat} day"));
                    $treatment = $this->treatmentFactory->create();
                    $treatment->setCustomerId($order->getCustomerId());
                    $treatment->setCustomerEmail($order->getCustomerEmail());
                    $treatment->setProductSku($product->getSku());
                    $treatment->setDeliveryDays($nextDate);
                    $treatment->save();
                }
            }
        }
    }
}
