<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

class SalesRule
{
    /**
     * @var \Amasty\Rules\Model\Rule
     */
    private $rule;

    public function __construct(
        \Amasty\Rules\Model\RuleFactory $ruleFactory
    ) {
        $this->rule = $ruleFactory->create();
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterLoad(\Magento\SalesRule\Model\Rule $subject, $result)
    {
        $this->rule->loadBySalesrule($subject);

        return $result;
    }
}
