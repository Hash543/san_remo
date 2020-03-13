<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Observer;

class DeleteSubscribe implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Alfa9\MDirector\Helper\Data
     */
    private $helperDirector;

    /**
     * Subscriber constructor.
     * @param \Alfa9\MDirector\Helper\Data $helperDirector
     */
    public function __construct(\Alfa9\MDirector\Helper\Data $helperDirector) {
        $this->helperDirector = $helperDirector;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        if (!$this->helperDirector->getIsEnabled()) {
            return;
        }
        /**
         * @var \Magento\Newsletter\Model\Subscriber $subscriber
         */
        $subscriber = $observer->getEvent()->getData('subscriber');
        $this->helperDirector->unsubscribe($subscriber->getEmail());
    }
}