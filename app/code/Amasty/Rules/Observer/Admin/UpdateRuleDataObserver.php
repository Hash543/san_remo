<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;

class UpdateRuleDataObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Rules\Model\Rule
     */
    private $rule;

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
        \Amasty\Rules\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->rule = $ruleFactory->create();
        $this->coreRegistry = $registry;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $amrulesData = null;
        $salesrule = $this->coreRegistry->registry(\Amasty\Rules\Observer\Admin\Rule\Save::SALESRULE_REGISTRY);

        if (!$salesrule) {
            return;
        }

        try {
            $amrulesData = $observer->getRequest()->getParam('amrulesrule');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }

        $ruleId = $this->rule->getCheckEnterprise()->isEnterprise()
            ? $salesrule->getRowId()
            : $salesrule->getId();

        if ($ruleId && $amrulesData) {
            try {
                $this->rule->resource->load($this->rule, $ruleId, 'salesrule_id');
                $this->rule
                    ->addData($amrulesData)
                    ->setData('salesrule_id', $ruleId);
                $this->rule->resource->save($this->rule);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
