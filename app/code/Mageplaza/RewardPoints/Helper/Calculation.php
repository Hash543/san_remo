<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPoints\Model\Source\Direction;
use Mageplaza\RewardPoints\Model\TransactionFactory;

/**
 * Class Calculation
 * @package Mageplaza\RewardPoints\Helper
 */
class Calculation extends Data
{
    /**
     * @var array Rates
     */
    protected $rateByDirection = [];

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var \Magento\Framework\Math\Calculator[]
     */
    protected $_calculators = [];

    /**
     * @var CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var array
     */
    protected $_deltaPoint = [];

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var null
     */
    protected $lastItemMathRule = null;

    /**
     * Calculation constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param CalculatorFactory $calculatorFactory
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        CalculatorFactory $calculatorFactory,
        TransactionFactory $transactionFactory
    ) {
        $this->_calculatorFactory = $calculatorFactory;
        $this->transactionFactory = $transactionFactory;

        parent::__construct($context, $objectManager, $storeManager, $priceCurrency, $timeZone);
    }

    /**
     * @param $point
     * @param $type
     *
     * @return float|int
     */
    public function deltaRoundPoint($point, $type)
    {
        $finalPoint = floor($point);
        if (!isset($this->_deltaPoint[$type])) {
            $this->_deltaPoint[$type] = 0.0;
        }

        $this->_deltaPoint[$type] += $point - $finalPoint;

        if ($this->_deltaPoint[$type] >= 1) {
            $finalPoint += 1;
            $this->_deltaPoint[$type] -= 1;
        }

        return $finalPoint;
    }

    /**
     * @param $type
     *
     * @return float|int
     */
    public function getDeltaRoundPoint($type)
    {
        if (isset($this->_deltaPoint[$type])) {
            return $this->_deltaPoint[$type];
        }

        return 0;
    }

    /**
     * @param string $type
     *
     * @return array|float
     */
    public function resetDeltaRoundPoint($type = '')
    {
        if ($type) {
            return $this->_deltaPoint[$type] = 0.0;
        }

        return $this->_deltaPoint = [];
    }

    /**
     * @param $instanceCollection
     * @param $fields
     * @param bool $isAddRewardShipping
     *
     * @return array
     */
    public function getOldRewardData($instanceCollection, $fields, &$isAddRewardShipping = false)
    {
        $rewardData = [];
        foreach ($instanceCollection as $collection) {
            if ($isAddRewardShipping && ($collection->getMpRewardDiscount() || $collection->getMpRewardEarn())) {
                $isAddRewardShipping = false;
            }

            foreach ($collection->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }

                foreach ($fields as $field) {
                    $itemId = $orderItem->getId();
                    if (!isset($rewardData[$itemId]) || !isset($rewardData[$itemId][$field])) {
                        $rewardData[$itemId][$field] = 0;
                    }
                    $rewardData[$itemId][$field] += $item->getData($field);
                }
            }
        }

        return $rewardData;
    }

    /********************************************** Earning Calculation ******************************************
     *
     * @param Item $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateEarningPoints(Item $item)
    {
        $earningRate = $this->getEarningRateByQuote($item->getQuote());
        $earningPoints = 0;
        if ($earningRate->isValid()) {
            $itemPrice = $this->getItemTotalForDiscount($item, false);

            $earningPoints = $itemPrice / $earningRate->getMoney() * $earningRate->getPoints();
            $earningPoints = $this->deltaRoundPoint($earningPoints, 'customer');
            $item->setMpRewardEarn($item->getMpRewardEarn() + $earningPoints);
        }
        $oldMpReward = $item->getMpRewardEarn();
        $this->_eventManager->dispatch('mpreward_calculate_item_earning_points', [
            'total'   => $this->getData('total'),
            'item'    => $item,
            'subject' => $this
        ]);

        /** Calculate catalog earning after */
        if ($item->getMpRewardEarn() > $oldMpReward) {
            $earningPoints += (int) $item->getMpRewardEarn() - $oldMpReward;
        }

        return $earningPoints;
    }

    /**
     * @param $item
     */
    public function setLastItemMatchRule($item)
    {
        $this->lastItemMathRule = $item;
    }

    /**
     * @return mixed
     */
    public function getLastItemMatchRule()
    {
        return $this->lastItemMathRule;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateShippingEarningPoints(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $this->_eventManager->dispatch('mpreward_calculate_shipping_earning_points_before', [
            'quote'              => $quote,
            'shippingAssignment' => $shippingAssignment,
            'total'              => $total
        ]);

        $storeId = $quote->getStoreId();
        if (!$this->isEarnPointFromShipping($storeId)) {
            return $quote->getMpRewardShippingEarn();
        }

        $earningRate = $this->getEarningRateByQuote($quote);
        if ($earningRate->isValid()) {
            /**
             *  $total->getBaseMpShippingDiscountAmount() is amount discount of mageplaza extensions
             *    $shippingAmount = $total->getBaseShippingAmount() - $total->getBaseShippingDiscountAmount() - $total->getBaseMpShippingDiscountAmount();
             */
            $shippingAmount = $total->getBaseShippingAmount() - $total->getBaseShippingDiscountAmount() - $quote->getMpRewardShippingDiscount();
            if ($this->isEarnPointFromTax($storeId)) {
                $shippingAmount += $total->getBaseShippingTaxAmount();
            }

            $earningPoints = $shippingAmount / $earningRate->getMoney() * $earningRate->getPoints();
            $quote->setMpRewardShippingEarn($quote->getMpRewardShippingEarn() + $earningPoints);
        }

        $this->_eventManager->dispatch('mpreward_calculate_shipping_earning_points_after', [
            'quote'              => $quote,
            'shippingAssignment' => $shippingAssignment,
            'total'              => $total
        ]);

        return $quote->getMpRewardShippingEarn();
    }

    /**************************************** Spending Slider **********************************************************
     *
     * @param $quote
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSpendingConfiguration($quote)
    {
        $spendingConfig = [
            'pointSpent'  => $quote->getMpRewardSpent(),
            'ruleApplied' => $quote->getMpRewardApplied(),
            'rules'       => []
        ];

        $rate = $this->getSpendingRateByQuote($quote);
        if ($maxSpending = $this->getMaxSpendingPointsByRate($quote, $rate)) {
            $spendingConfig['rules'][] = [
                'id'              => 'rate',
                'label'           => __(
                    'Each of %1 gets %2 discount',
                    $this->getPointHelper()->format($rate->getPoints(), $quote->getStoreId()),
                    $this->convertPrice($rate->getMoney(), true, false, $quote->getStoreId())
                ),
                'min'             => 0,
                'max'             => $maxSpending,
                'step'            => 1,
                'isDisplaySlider' => true
            ];
        }

        return $spendingConfig;
    }

    /**
     * Is allow spending point on order
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function isAllowSpending($quote)
    {
        $customerId = $quote->getCustomerId();
        if (!$customerId) {
            return false;
        }

        $account = $this->getAccountHelper()->getByCustomerId($customerId);
        $balance = $account->getBalance();
        $minSpend = $this->getConfigSpending('minimum_balance', $quote->getStoreId());

        return $balance > 0 && $balance >= $minSpend;
    }

    /**
     * @param $quote
     * @param bool $isCanCalculateRewardDiscount
     * @param bool $isCalculateInvitedDiscount
     *
     * @return null
     */
    public function getSpendingTotal($quote, $isCanCalculateRewardDiscount = true, $isCalculateInvitedDiscount = false)
    {
        $storeId = $quote->getStoreId();
        $total = 0;

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var \Magento\Quote\Model\Quote\Item $child */
                foreach ($item->getChildren() as $child) {
                    $total += $this->getItemTotalForDiscount(
                        $child,
                        true,
                        $isCanCalculateRewardDiscount,
                        $isCalculateInvitedDiscount
                    );
                }
            } else {
                $total += $this->getItemTotalForDiscount(
                    $item,
                    true,
                    $isCanCalculateRewardDiscount,
                    $isCalculateInvitedDiscount
                );
            }
        }
        if ($this->isSpendingOnShippingFee($storeId)) {
            $total += $this->getShippingTotalForDiscount(
                $quote,
                $isCanCalculateRewardDiscount,
                $isCalculateInvitedDiscount
            );
        }

        return $total;
    }

    /**
     * @param $item
     * @param bool $isSpending
     * @param bool $isCalculateMpDiscount
     * @param bool $isCalculateInvitedDiscount
     *
     * @return mixed
     */
    public function getItemTotalForDiscount(
        $item,
        $isSpending = true,
        $isCalculateMpDiscount = true,
        $isCalculateInvitedDiscount = false
    ) {
        /** base_mp_discount_amount is the discount amount of Mageplaza extensions */
        // $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getBaseMpDiscountAmount() + $item->getMpRewardBaseDiscount();
        $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
        if ($isCalculateMpDiscount) {
            $total -= $item->getMpRewardBaseDiscount();
        }

        if ($isCalculateInvitedDiscount) {
            $total = $total - $item->getMpRewardInvitedBaseDiscount();
        }
        $isCalculateTax = $isSpending ? $this->isSpendingFromTax($item->getStoreId()) : $this->isEarnPointFromTax($item->getStoreId());
        if ($isCalculateTax) {
            $total += $item->getBaseTaxAmount();
        }

        return $total;
    }

    /**
     * @param $quote
     * @param bool $isCanCalculateRewardDiscount
     * @param bool $isCalculateInvitedDiscount
     * @param bool $isSpending
     *
     * @return float|int
     */
    public function getShippingTotalForDiscount(
        $quote,
        $isCanCalculateRewardDiscount = true,
        $isCalculateInvitedDiscount = false,
        $isSpending = true
    ) {
        $total = 0;
        if (!$quote->getIsVirtual()) {
            $quoteAddress = $this->getQuote()->getShippingAddress();
            // $total        = $quoteAddress->getBaseShippingAmount() - $quoteAddress->getBaseShippingDiscountAmount() - $quote->getBaseMpShippingDiscountAmount() + $quote->getMpRewardShippingBaseDiscount();
            $total = $quoteAddress->getBaseShippingAmount() - $quoteAddress->getBaseShippingDiscountAmount();
            $isCalculateTax = $isSpending ? $this->isSpendingFromTax($quote->getStoreId()) : $this->isEarnPointFromTax($quote->getStoreId());

            if ($isCalculateTax) {
                $total += $quoteAddress->getBaseShippingTaxAmount();
            }

            if ($isCanCalculateRewardDiscount) {
                $total -= $quote->getMpRewardShippingBaseDiscount();
            }

            if ($isCalculateInvitedDiscount) {
                $total -= $quote->getMpRewardShippingInvitedBaseDiscount();
            }
        }

        return $total;
    }

    /**
     * @param $quote
     * @param null $rate
     *
     * @return int|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMaxSpendingPointsByRate($quote, $rate = null)
    {
        $spendingRate = $rate ?: $this->getSpendingRateByQuote($quote);
        if (!$spendingRate->isValid()) {
            return 0;
        }

        $total = $this->getSpendingTotal($quote, false);
        if ($quote->getMpRewardInvitedBaseDiscount()) {
            $total -= $quote->getMpRewardInvitedBaseDiscount();
        }

        return $this->getMaxSpendingPoints(floor($total * $spendingRate->getPoints() / $spendingRate->getMoney()));
    }

    /**
     * @param $maxPointsByRule
     *
     * @return mixed
     */
    public function getMaxSpendingPoints($maxPointsByRule)
    {
        $quote = $this->getQuote();
        $account = $this->getAccountHelper()->getByCustomerId($quote->getCustomerId());
        $maxSpending = $account->getBalance();
        $maxSpendingPerOrder = (int) $this->getConfigSpending('maximum_point_per_order', $quote->getStoreId());
        if ($maxSpendingPerOrder) {
            $maxSpending = min($maxSpending, $maxSpendingPerOrder);
        }

        return min($maxPointsByRule, $maxSpending);
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     *
     * @return float
     */
    public function roundPrice($price, $type = 'regular')
    {
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $this->_calculatorFactory->create();
            }
            $price = $this->_calculators[$type]->deltaRound($price);
        }

        return $price;
    }

    /********************************* Retrieve Reward Points Rate ****************************************************
     *
     * @param int $direction
     * @param null $websiteId
     * @param null $customerGroupId
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRate($direction, $websiteId = null, $customerGroupId = null)
    {
        $websiteId = $websiteId ?: $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $customerGroupId ?: $this->getQuote()->getCustomerGroupId();

        $cacheKey = 'rate_' . $direction . '_' . $websiteId . '_' . $customerGroupId;
        if (!$this->getData($cacheKey)) {
            $collection = $this->objectManager->create(\Mageplaza\RewardPoints\Model\ResourceModel\Rate\Collection::class);
            $collection->addFieldToFilter('direction', $direction)
                ->addFieldToFilter('website_ids', ['finset' => $websiteId])
                ->addFieldToFilter('customer_group_ids', ['finset' => $customerGroupId])
                ->addFieldToFilter('points', ['gt' => 0])
                ->addFieldToFilter('money', ['gt' => 0])
                ->setOrder('priority', 'ASC');

            $this->setData($cacheKey, $collection->getFirstItem());
        }

        return $this->getData($cacheKey);
    }

    /**
     * @param null $websiteId
     * @param null $customerGroupId
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEarningRate($websiteId = null, $customerGroupId = null)
    {
        return $this->getRate(Direction::MONEY_TO_POINT, $websiteId, $customerGroupId);
    }

    /**
     * @param null $websiteId
     * @param null $customerGroupId
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSpendingRate($websiteId = null, $customerGroupId = null)
    {
        return $this->getRate(Direction::POINT_TO_MONEY, $websiteId, $customerGroupId);
    }

    /**
     * @param Quote $quote
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEarningRateByQuote($quote)
    {
        $storeId = $quote->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        return $this->getEarningRate($websiteId, $quote->getCustomerGroupId());
    }

    /**
     * @param Quote $quote
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSpendingRateByQuote($quote)
    {
        $storeId = $quote->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        return $this->getSpendingRate($websiteId, $quote->getCustomerGroupId());
    }

    /**
     * @param $order
     * @param $action
     * @param string $field
     *
     * @return int
     */
    public function calculatePointOrderCompleteByAction($order, $action, $field = 'mp_reward_earn')
    {
        $pointAmount = 0;
        if (!$order->getMpRewardEarnAfterInvoice() && $order->getState() == Order::STATE_COMPLETE && $order->getData($field) > 0) {
            $transaction = $this->transactionFactory->create()->getCollection()
                ->addFieldToFilter('action_code', $action)
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();
            if ($transaction->getId()) {
                return $pointAmount;
            }

            if ($order->hasCreditmemos()) {
                foreach ($order->getItems() as $item) {
                    $qty = $item->getQtyOrdered() - $item->getQtyRefunded();
                    if ($item->getData($field) > 0 && $qty > 0) {
                        $pointAmount += $item->getData($field) * $qty / $item->getQtyOrdered();
                    }
                }
            } else {
                $pointAmount = $order->getData($field);
            }
        }

        return $pointAmount;
    }

    /**
     * @param $items
     * @param $quote
     * @param array $quoteFields
     * @param array $itemFields
     */
    public function resetRewardData($items, $quote, $quoteFields = [], $itemFields = [])
    {
        $this->resetFields($quote, $quoteFields);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var \Magento\Quote\Model\Quote\Item $child */
                foreach ($item->getChildren() as $child) {
                    $this->resetFields($child, $itemFields);
                }
            } else {
                $this->resetFields($item, $itemFields);
            }
        }
    }

    /**
     * @param $object ($quote || item)
     * @param $fields
     */
    public function resetFields($object, $fields)
    {
        foreach ($fields as $field) {
            $object->setData($field, 0);
        }
    }

    /**
     * @param $quote
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addLocalizedException($quote)
    {
        $quote->setMpRewardSpent(0)->setMpRewardApplied(null)->save();
        //  $this->setisReloadSpent(true);
        if ($this->_getRequest()->isXmlHttpRequest()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while spending points. Please reload page!')
            );
        }
    }
}
