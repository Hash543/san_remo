<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Gdpr\Model\Anonymizer;

class SessionInit implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $observer->getData('customer_session');

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $customerSession->getCustomer();
        $email = $customer->getEmail();
        $emailWithoutDomain = substr($email, 0, strrpos($email, '@'));
        if ($emailWithoutDomain == Anonymizer::ANONYMOUS_SYMBOL) {
            $customerSession->setCustomerId(null);
            $customerSession->destroy([]);
        }
    }
}
