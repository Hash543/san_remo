<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Amasty\Rules\Api\Data\RuleInterface;

class Rule extends \Magento\Framework\Model\AbstractModel implements RuleInterface
{
    const RULE_NAME = 'amrules_rule';
    const KEY_PROMO_CATS = 'promo_cats';
    const KEY_PROMO_SKUS = 'promo_skus';
    const KEY_APPLY_DISCOUNT_TO = 'apply_discount_to';
    const KEY_EACHM = 'eachm';
    const KEY_PRICESELECTOR = 'priceselector';
    const KEY_MAX_DISCOUNT = 'max_discount';
    const KEY_NQTY = 'nqty';
    const KEY_SKIP_RULE = 'skip_rule';

    /**
     * @var \Amasty\Rules\Model\CheckEnterprise
     */
    private $checkEnterprise;

    /**
     * @var \Amasty\Rules\Model\ResourceModel\Rule
     */
    public $resource;

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->checkEnterprise = $this->getData('isEnterprise');
        $this->resource = $this->getData('resource');
        parent::_construct();
        $this->_init('Amasty\Rules\Model\ResourceModel\Rule');
        $this->setIdFieldName('entity_id');
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     *
     * @return mixed
     */
    public function loadBySalesrule(\Magento\Rule\Model\AbstractModel $rule)
    {
        if ($amrulesRule = $rule->getData(self::RULE_NAME)) {
            return $amrulesRule;
        }

        $ruleId = $this->checkEnterprise->getRuleId($rule);
        $this->resource->load($this, $ruleId, 'salesrule_id');
        $rule->setData(self::RULE_NAME, $this);

        return $this;
    }

    /**
     * @return CheckEnterprise
     */
    public function getCheckEnterprise()
    {
        return $this->checkEnterprise;
    }

    /**
     * @return string|null
     */
    public function getPromoCats()
    {
        return $this->_getData(self::KEY_PROMO_CATS);
    }

    /**
     * @param string $promoCats
     * @return $this
     */
    public function setPromoCats($promoCats)
    {
        $this->setData(self::KEY_PROMO_CATS, $promoCats);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPromoSkus()
    {
        return $this->_getData(self::KEY_PROMO_SKUS);
    }

    /**
     * @param string $promoSkus
     * @return $this
     */
    public function setPromoSkus($promoSkus)
    {
        $this->setData(self::KEY_PROMO_SKUS, $promoSkus);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApplyDiscountTo()
    {
        return $this->_getData(self::KEY_APPLY_DISCOUNT_TO);
    }

    /**
     * @param string $applyDiscountTo
     * @return $this
     */
    public function setApplyDiscountTo($applyDiscountTo)
    {
        $this->setData(self::KEY_APPLY_DISCOUNT_TO, $applyDiscountTo);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEachm()
    {
        return $this->_getData(self::KEY_EACHM);
    }

    /**
     * @param string $eachm
     * @return $this
     */
    public function setEachm($eachm)
    {
        $this->setData(self::KEY_EACHM, $eachm);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPriceselector()
    {
        return $this->_getData(self::KEY_PRICESELECTOR);
    }

    /**
     * @param int $priceselector
     * @return $this
     */
    public function setPriceselector($priceselector)
    {
        $this->setData(self::KEY_PRICESELECTOR, $priceselector);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNqty()
    {
        return $this->_getData(self::KEY_NQTY);
    }

    /**
     * @param string $nqty
     * @return $this
     */
    public function setNqty($nqty)
    {
        $this->setData(self::KEY_NQTY, $nqty);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMaxDiscount()
    {
        return $this->_getData(self::KEY_MAX_DISCOUNT);
    }

    /**
     * @param string $maxDiscount
     * @return $this
     */
    public function setMaxDiscount($maxDiscount)
    {
        $this->setData(self::KEY_MAX_DISCOUNT, $maxDiscount);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSkipRule()
    {
        return $this->_getData(self::KEY_SKIP_RULE);
    }

    /**
     * @param int $skipRule
     * @return $this
     */
    public function setSkipRule($skipRule)
    {
        $this->setData(self::KEY_SKIP_RULE, $skipRule);
        return $this;
    }
}
