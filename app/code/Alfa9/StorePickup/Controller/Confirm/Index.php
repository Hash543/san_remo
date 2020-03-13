<?php

namespace Alfa9\StorePickup\Controller\Confirm;

use Alfa9\StorePickup\Model\Carrier\StorePickup;
use Magento\Framework\App\Action\Context;

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
     * @var \Alfa9\StorePickup\Model\Email\StorePickupConfirm
     */
    private $storePickupConfirm;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Alfa9\StorePickup\Model\Email\StorePickupConfirm $storePickupConfirm,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->storePickupConfirm = $storePickupConfirm;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        $hash = $this->getRequest()->getParam('k');
        $decodedHash = base64_decode($hash);
        $splitData = explode(':', $decodedHash);


        // Validation #1 Increment Id Validation
        if (count($splitData) != 2) return $this->resultRedirectFactory->create()->setPath('/');
        $incrementId = $splitData[1];

        // Validation #2 Order's Payment method
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId,'increment_id');
        $paymentMethod = $order->getPayment()->getMethod();
        $shippingMethod = $order->getShippingMethod();
        if ($paymentMethod == 'reserveandcollect' || strpos($shippingMethod, StorePickup::SHIPPING_CODE) !== false) {
            $this->storePickupConfirm->send($order);
            $currentDate = $this->timezone->date()->format('Y-m-d H:i:s');
            $order->setOrderStatePickup($currentDate);
            $this->orderResource->save($order);
        }
        $this->messageManager->addSuccessMessage(__('Store pickup confirmation email sent'));
        return $this->resultRedirectFactory->create()->setPath('/');
    }
}
