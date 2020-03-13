<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Observer\Admin\Rule;

use Magento\Framework\Event\ObserverInterface;

class Save implements ObserverInterface
{
    const SALESRULE_REGISTRY = 'amrules_current_salesrule';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->coreRegistry = $registry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesrule = null;

        try {
            $salesrule = $observer->getEntity();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }

        if (!$salesrule) {
            return $this;
        }

        $this->coreRegistry->register(self::SALESRULE_REGISTRY, $salesrule, true);

        return $this;
    }
}