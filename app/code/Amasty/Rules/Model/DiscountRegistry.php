<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class DiscountRegistry
{
    /**
     * @var array
     */
    private $discount = [];

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Psr\Log\LoggerInterface $logger

    ) {
        $this->storeManager = $storeManager;
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return $this
     */
    public function setDiscount($discountData, $rule)
    {
        $this->discount[$rule->getId()] = isset($this->discount[$rule->getId()])
            ? $this->discount[$rule->getId()] + $discountData->getBaseAmount() : $discountData->getBaseAmount();

        return $this;
    }

    /**
     * Return amount of discount for each rule
     * @return array
     */
    public function getRulesWithAmount()
    {
        $totalAmount = [];

        try {
            foreach ($this->getDiscount() as $ruleId => $ruleAmount) {
                /** @var \Magento\SalesRule\Api\Data\RuleInterface $rule */
                $rule = $this->ruleRepository->getById($ruleId);

                $totalAmount[] = [
                    'rule_name' => $this->getRuleStoreLabel($rule) ?: $rule->getName(),
                    'rule_amount' =>
                        '-' . $this->storeManager->getStore()->getCurrentCurrency()->format($ruleAmount, [], false)
                ];
            }
        } catch (NoSuchEntityException $entityException) {
            $this->logger->critical($entityException);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }

        return $totalAmount;
    }

    /**
     * @param \Magento\SalesRule\Api\Data\RuleInterface $rule
     * @return null|string
     */
    private function getRuleStoreLabel($rule)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $storeLabel = $storeLabelDefault = null;

        /* @var $label \Magento\SalesRule\Model\Data\RuleLabel */
        foreach ($rule->getStoreLabels() as $label) {
            if ($label->getStoreId() === 0) {
                $storeLabelDefault = $label->getStoreLabel();
            }

            if ($label->getStoreId() == $storeId) {
                $storeLabel = $label->getStoreLabel();
                break;
            }
        }

        $storeLabel = $storeLabel ?: $storeLabelDefault;

        return $storeLabel;
    }

    /**
     * @return array
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function updateQuoteData($quote)
    {
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        return $this;
    }

    /**
     * @return $this
     */
    public function flushDiscount()
    {
        $this->discount = [];

        return $this;
    }
}
