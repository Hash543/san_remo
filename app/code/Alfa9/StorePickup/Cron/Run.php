<?php

namespace Alfa9\StorePickup\Cron;

class Run
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $collection;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * Run constructor.
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Model\ResourceModel\Order\Collection $collection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    )
    {
        $this->order = $order;
        $this->collection = $collection;
        $this->registry = $registry;
        $this->timezone = $timezone;
    }

    public function execute(){
        $currentDate = $this->timezone->date()->format('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($currentDate)));
        $orders = $this->collection->addFieldToFilter('order_state_pickup', $yesterday);
        foreach ($orders as $order){
            $incrementId = $order->getIncrementId();
            $this->order->loadByIncrementId($incrementId);
            $this->registry->register( 'isSecureArea', 'true' );
            $this->order->delete();
            $this->registry->unregister( 'isSecureArea' );
        }
    }
}