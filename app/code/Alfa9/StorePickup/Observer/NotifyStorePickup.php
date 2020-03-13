<?php

namespace Alfa9\StorePickup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Alfa9\StorePickup\Model\Carrier\StorePickup;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;
use Magento\Framework\Exception\MailException;

class NotifyStorePickup implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Alfa9\StorePickup\Model\Email\NotifyToStore
     */
    private $notifyToStore;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * NotifyStorePickup constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Alfa9\StorePickup\Model\Email\NotifyToStore $notifyToStore
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfa9\StorePickup\Model\Email\NotifyToStore $notifyToStore,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->notifyToStore = $notifyToStore;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getData('order_ids');
        foreach ($orderIds as $orderId) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $shippingMethod = $order->getShippingMethod();
            if(strpos($shippingMethod, StorePickup::SHIPPING_CODE) !== false ||
                strpos($shippingMethod, ReserveAndCollect::SHIPPING_CODE) !== false) {
                try {
                    $this->notifyToStore->send($order);
                }catch (MailException $exception) {
                    $this->logger->error(__("Unable to Notify Store: %1", $exception->getMessage()));
                }
            }
        }
    }
}
