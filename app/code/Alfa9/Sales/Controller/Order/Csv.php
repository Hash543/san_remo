<?php

namespace Alfa9\Sales\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Csv extends Action
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $manager;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Event\ManagerInterface $manager
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->manager = $manager;
        $this->countryFactory = $countryFactory;
    }

    /**
     * generatecsv/order/csv
     */
    public function execute()
    {
        try {
            $order = $this->orderFactory->create();
            $orderId = $this->getRequest()->getParam('order_id', 15488);
            var_dump($orderId);
            $orderModel = $order->load($orderId);
            $this->manager->dispatch('sales_order_place_after', ['order' => $orderModel, 'debug' => true]);
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            die;
        }
    }
}
