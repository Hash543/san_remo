<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model;

use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\SalesRule\Api\Data\RuleExtension;
use Amasty\Rules\Model\RuleProvider;
use Magento\Framework\Api\SearchResultsInterface;

class RuleRepository
{
    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var RuleExtensionFactory
     */
    private $ruleExtensionFactory;

    /**
     * @var RuleProvider
     */
    private $ruleProvider;

    /**
     * @var RuleInterface
     */
    private $currentRule;

    public function __construct(
        Rule $ruleResource,
        RuleExtensionFactory $ruleExtensionFactory,
        RuleProvider $ruleProvider
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleExtensionFactory = $ruleExtensionFactory;
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * @param RuleRepositoryInterface $subject
     * @param RuleInterface $entity
     * @return RuleInterface
     */
    public function afterGetById(
        RuleRepositoryInterface $subject,
        RuleInterface $entity
    ) {
        $entity->setExtensionAttributes($this->getAttributes($entity));

        return $entity;
    }

    /**
     * @param RuleRepositoryInterface $subject
     * @param RuleInterface $rule
     */
    public function beforeSave(
        RuleRepositoryInterface $subject,
        RuleInterface $rule
    ) {
        $this->currentRule = $rule;
    }

    /**
     * @param RuleRepositoryInterface $subject
     * @param RuleInterface $entity
     * @return RuleInterface
     */
    public function afterSave(
        RuleRepositoryInterface $subject,
        RuleInterface $entity
    ) {
        $extensionAttributes = $this->currentRule->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmrules()) {
            $salesruleId = $entity->getRuleId();
            $amruleData = $extensionAttributes->getAmrules();
            $amruleData->setSalesruleId($salesruleId);
            $amrule = $this->ruleProvider->getAmruleByRuleId($salesruleId);
            $amruleId = $amrule->getId();
            if ($amruleId) {
                $amruleData->setId($amrule->getId());
            }

            $this->ruleResource->save($amruleData);
            $entity->setExtensionAttributes($extensionAttributes);
        }

        return $entity;
    }

    /**
     * @param RuleRepositoryInterface $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     */
    public function afterGetList(
        RuleRepositoryInterface $subject,
        SearchResultsInterface $result
    ) {
        foreach ($result->getItems() as $item) {
            $item->setExtensionAttributes($this->getAttributes($item));
        }

        return $result;
    }

    /**
     * @param $item
     * @return RuleExtension
     */
    private function getAttributes($item)
    {
        $amrulesData = $this->ruleProvider->getAmruleByRuleId($item->getRuleId());
        $extensionAttributes = $item->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->ruleExtensionFactory->create();
        }
        $extensionAttributes->setAmrules($amrulesData);

        return $extensionAttributes;
    }
}
