<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Amasty\Rules\Api\RuleProviderInterface;
use Amasty\Rules\Model\ResourceModel\Rule as RuleResource;

class RuleProvider implements RuleProviderInterface
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        RuleResource $ruleResource,
        RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param int $ruleId
     * @return \Amasty\Rules\Model\Rule
     */
    public function getAmruleByRuleId($ruleId)
    {
        $rule = $this->ruleFactory->create();
        $this->ruleResource->load($rule, $ruleId, 'salesrule_id');

        return $rule;
    }
}
