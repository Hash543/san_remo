<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Observer\Customer;

use Amasty\Gdpr\Model\Consent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Registration implements ObserverInterface
{
    /**
     * @var Consent
     */
    private $consent;

    public function __construct(
        Consent $consent
    ) {
        $this->consent = $consent;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Controller\Account\CreatePost $controller */
        $controller = $observer->getData('account_controller');

        if (!$controller->getRequest()->getParam('amgdpr_agree')) {
            return;
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getData('customer');

        $this->consent->acceptLastVersion($customer->getId(), Consent::FROM_REGISTRATION);
    }
}
