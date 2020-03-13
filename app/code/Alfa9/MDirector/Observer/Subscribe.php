<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Observer;

class Subscribe implements \Magento\Framework\Event\ObserverInterface {

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
        if($subscriber->isStatusChanged()){
            if($subscriber->getStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED){
                if($this->helperDirector->sendMDirectorWelcomeEmail()){
                    //$subscriber->setImportMode(true);
                    //Todo: Magento 2 does not have the set Import Mode
                }
                $this->helperDirector->subscribe($subscriber->getEmail(), $subscriber->getCustomerId());
            } elseif ($subscriber->getStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED) {
                $this->helperDirector->unsubscribe($subscriber->getEmail());
            }
        }
    }
}