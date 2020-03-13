<?php
/**
 * @author Israel Yasis
 */
namespace Pss\Redsys\Observer;

/**
 * Class AfterPlaceOrder
 * @package Pss\Redsys\Observer
 */
class AfterPlaceOrder implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * AfterPlaceOrder constructor.
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;
    }
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Sales\Model\Order\Email\Sender\OrderSender */
        $orderIds = $observer->getEvent()->getData('order_ids');
        if(is_array($orderIds) && count($orderIds) > 0) {
            foreach ($orderIds as $orderId) {
                try {
                    /** @var \Magento\Sales\Model\Order $order */
                    $order = $this->orderRepository->get($orderId);
                    if($order && $order->getEntityId() && $order->getPayment()->getMethod()) {
                        $paymentMethod = $order->getPayment()->getMethod();
                        if($paymentMethod == 'redsys') {
                            $this->orderSender->send($order);
                        }
                    }
                }catch (\Exception $exception) {
                    continue;
                }

            }
        }
    }
}