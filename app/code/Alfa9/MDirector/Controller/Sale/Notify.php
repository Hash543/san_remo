<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Controller\Sale;

class Notify extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Alfa9\MDirector\Helper\Data
     */
    private $helperData;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Alfa9\MDirector\Model\Api\Sale
     */
    private $apiSale;
    /**
     * Notify constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Alfa9\MDirector\Helper\Data $helperData
     * @param \Alfa9\MDirector\Model\Api\Sale $apiSale
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfa9\MDirector\Helper\Data $helperData,
        \Alfa9\MDirector\Model\Api\Sale $apiSale,
        \Magento\Framework\App\Action\Context $context) {
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->apiSale = $apiSale;
        parent::__construct($context);
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order');
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $order = null;
        }
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        if($order) {
            $qty = 0;
            /**
             * @var \Magento\Sales\Api\Data\OrderItemInterface $item
             */
            foreach ($order->getAllVisibleItems() as $item) {
                $qty += $item->getQtyOrdered();
            }

            $data = array(
                'dataId'                => $this->helperData->getDataId(),
                'PRODUCTO'              => '#' . $order->getIncrementId(),
                'IMPORTE'               => number_format($order->getGrandTotal(), 2),
                'CANTIDAD'              => $qty,
                'SEND-NOTIFICATIONS'    => 0
            );
            try {
                $this->apiSale->notify($data);
            }catch (\Exception $exception) {
                //Todo: Add log here
            }
        }
    }
}