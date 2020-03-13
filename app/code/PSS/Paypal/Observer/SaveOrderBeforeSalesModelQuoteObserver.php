<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Observer;

/**
 * Class SaveOrderBeforeSalesModelQuoteObserver
 * @package PSS\Paypal\Observer
 */
class SaveOrderBeforeSalesModelQuoteObserver implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    private $objectCopyService;
    /**
     * SaveOrderBeforeSalesModelQuoteObserver constructor.
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     */
    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        if($order && $quote) {
            $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        }
    }
}