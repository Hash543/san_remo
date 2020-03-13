<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

class ValueProvider
{
    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    /**
     * @var \Amasty\Rules\Model\Rule
     */
    private $rule;

    public function __construct(
        \Amasty\Rules\Model\RuleFactory $ruleFactory,
        \Amasty\Rules\Helper\Data $rulesDataHelper
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
        $this->rule = $ruleFactory->create();
    }

    public function aroundGetMetadataValues(
        SalesRuleValueProvider $subject,
        \Closure $proceed,
        Rule $rule
    ) {
        $result = $proceed($rule);
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        foreach ($actions as &$action) {
            if ($action['value'] == \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION) {
                $action['label'] = __("Buy N products, and get next products with discount");
                break;
            }
        }
        $actions = array_merge($actions, $this->rulesDataHelper->getDiscountTypes());
        $ruleId = $this->rule->getCheckEnterprise()->getRuleId($rule);
        $this->rule->resource->load($this->rule, $ruleId, 'salesrule_id');
        $ampromoRule = $this->rule;
        $result['actions']['children']['amrulesrule[use_for]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('use_for');
        $result['actions']['children']['amrulesrule[apply_discount_to]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('apply_discount_to');
        $result['actions']['children']['amrulesrule[eachm]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('eachm');
        $result['actions']['children']['amrulesrule[priceselector]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('priceselector');
        $result['actions']['children']['promo_items']['children']['amrulesrule[promo_skus]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('promo_skus');
        $result['actions']['children']['promo_items']['children']['amrulesrule[promo_cats]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('promo_cats');
        $result['actions']['children']['amrulesrule[nqty]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('nqty');
        $result['actions']['children']['amrulesrule[skip_rule]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('skip_rule');
        $result['actions']['children']['amrulesrule[max_discount]']['arguments']['data']['config']['value']
            = $ampromoRule->getData('max_discount');

        return $result;
    }
}
