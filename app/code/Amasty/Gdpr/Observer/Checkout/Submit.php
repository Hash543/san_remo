<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Observer\Checkout;

use Amasty\Gdpr\Model\Checkbox;
use Amasty\Gdpr\Model\Consent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Submit implements ObserverInterface
{
    /**
     * @var Consent
     */
    private $consent;

    /**
     * @var Checkbox
     */
    private $checkbox;

    public function __construct(
        Consent $consent,
        Checkbox $checkbox
    ) {
        $this->consent = $consent;
        $this->checkbox = $checkbox;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->checkbox->isVisible(Checkbox::AREA_CHECKOUT)) {
            return;
        }

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getData('order');

        $this->consent->acceptLastVersion($order->getCustomerId(), Consent::FROM_CHECKOUT);
    }
}
