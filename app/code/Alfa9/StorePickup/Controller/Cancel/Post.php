<?php

namespace Alfa9\StorePickup\Controller\Cancel;

use Magento\Framework\App\Action\Context;

class Post extends \Magento\Framework\App\Action\Action
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
     * @var \Alfa9\StorePickup\Model\Email\StorePickupCancel
     */
    private $storePickupCancel;

    /**
     * Post constructor.
     * @param Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Alfa9\StorePickup\Model\Email\StorePickupCancel $storePickupCancel
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Alfa9\StorePickup\Model\Email\StorePickupCancel $storePickupCancel
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->storePickupCancel = $storePickupCancel;
    }

    public function execute()
    {
        // validate k
        $k = $this->getRequest()->getParam('k');
        if (!$k) return $this->resultRedirectFactory->create()->setPath('/');

        // validate order increment id
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return $this->resultRedirectFactory->create()->setPath('*/*/index', ['k' => $k]);
        }

        $additional = [
            'subject' => $this->getRequest()->getParam('subject'),
            'message' => $this->getRequest()->getParam('message')
        ];

        $this->storePickupCancel->setAdditionalData($additional);

        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId, 'increment_id');

        $this->storePickupCancel->send($order);

        $this->messageManager->addSuccessMessage(__('Store pickup cancelation email sent'));
        return $this->resultRedirectFactory->create()->setPath('/');
    }
}
