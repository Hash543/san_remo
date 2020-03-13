<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Observer;

class SaveCustomer implements \Magento\Framework\Event\ObserverInterface {

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
         * @var \Magento\Customer\Model\Customer $customer
         */
        $customer = $observer->getEvent()->getData('customer');
        if($customer == null || !$customer->getId()) {
            return;
        }
        $listId = $this->helperDirector->getListIdByCustomerGroupId($customer->getGroupId());
        $oldListId = false;
        if(!$customer->getOrigData('email')) {
            $oldListId = $this->helperDirector->getListIdByCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        } elseif ($customer->getOrigData('group_id')) {
            $oldListId = $this->helperDirector->getListIdByCustomerGroupId($customer->getOrigData('group_id'));
        }
        if($oldListId && $oldListId != $listId && $this->helperDirector->unsubscribeFromList($customer->getEmail(), $oldListId)){
            $this->helperDirector->subscribe($customer->getEmail(), $oldListId);
        } else {
            $this->helperDirector->updateSubscriber($customer, $listId);
        }
    }
}