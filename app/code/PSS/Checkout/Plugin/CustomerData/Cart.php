<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Checkout\Plugin\CustomerData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Cart
 * @package PSS\Checkout\Plugin\CustomerData
 */
class Cart {
    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    private $cartTotalRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepositoryInterface;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;
    /**
     * Cart constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $cartTotalRepository
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Cart\CartTotalRepository $cartTotalRepository,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->checkoutSession = $checkoutSession;
        $this->ruleRepositoryInterface = $ruleRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Add tax data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();
        $result['discountLabel'] = 0;
        if($quote && $quote->getId() && $quote->getAppliedRuleIds()) {

            try {
                $totals = $this->cartTotalRepository->get($this->checkoutSession->getQuoteId());
                $storeId = $this->storeManager->getStore()->getId();
            } catch (NoSuchEntityException $exception) {
                $totals = null;
                $storeId = 0;
            }
            if($totals && $totals->getDiscountAmount() < 0.001) {
                $appliedRules = $quote->getAppliedRuleIds();
                $result['discountAmount'] = (double)$totals->getDiscountAmount();
                $result['discountPriceHtml'] = $this->checkoutHelper->formatPrice($totals->getDiscountAmount());
                $result['couponCode'] = $quote->getCouponCode();
                $result['discountLabel'] = $this->getRuleLabel($appliedRules, $storeId);
            }
        }
        return $result;
    }

    /**
     * @param string $appliedRules
     * @param integer $storeId
     * @return string
     */
    private function getRuleLabel($appliedRules, $storeId = 0) {
        $labelDiscount = '';
        if($appliedRules !== null) {
            $rulesIds = explode(',', $appliedRules);
            $names = [];
            foreach ($rulesIds as $rulesId) {
                try {
                    $rule = $this->ruleRepositoryInterface->getById($rulesId);
                    $storeLabel = $storeLabelFallback = null;
                    if($rule->getStoreLabels() !== null) {
                        /* @var $label \Magento\SalesRule\Model\Data\RuleLabel */
                        foreach ($rule->getStoreLabels() as $label) {
                            if ($label->getStoreId() === 0) {
                                $storeLabelFallback = $label->getStoreLabel();
                            }

                            if ($label->getStoreId() == $storeId) {
                                $storeLabel = $label->getStoreLabel();
                                break;
                            }
                        }
                    }
                    if($storeLabel) {
                        $name = $storeLabel;
                    } else if($storeLabelFallback){
                        $name = $storeLabelFallback;
                    } else {
                        $name = $rule->getName();
                    }
                    $names[] = $name;
                } catch (LocalizedException $exception) {
                    continue;
                }
            }
            if(count($names) > 0) {
                $labelDiscount = implode(', ', $names);
            }
        }
        return $labelDiscount;
    }
}