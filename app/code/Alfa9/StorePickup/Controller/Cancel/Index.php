<?php

namespace Alfa9\StorePickup\Controller\Cancel;

use Alfa9\StorePickup\Model\Carrier\StorePickup;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    private $orderResource;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->registry = $registry;
    }

    public function execute()
    {
        $hash = $this->getRequest()->getParam('k');
        $decodedHash = base64_decode($hash);
        $splitData = explode(':', $decodedHash);

        // Validation #1 Increment Id Validation
        if (count($splitData) != 2) return $this->resultRedirectFactory->create()->setPath('/');
        $incrementId = $splitData[1];

        $this->registry->register('order_id', $incrementId);

        // Validation #2 Order's Payment method
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId,'increment_id');
        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod != 'reserveandcollect') {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Cancel reservation.'));
        return $resultPage;
    }
}