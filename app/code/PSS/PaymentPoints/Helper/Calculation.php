<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Helper;

use Mageplaza\RewardPoints\Model\Rate;

class Calculation extends \Mageplaza\RewardPoints\Helper\Calculation {
    /**
     * {@inheritdoc}
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

        return $this->getMaxSpendingPoints(ceil($total * $spendingRate->getPoints() / $spendingRate->getMoney()));
    }


    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSpendingRateByQuote($quote)
    {
        $storeId = $quote->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $ratePrice = $this->getCustomerConversion();
        if($ratePrice > 0 ){
            /** @var Rate $rate */
            $rate = $this->objectManager->create(Rate::class);
            $rate->setId(99999);
            $rate->setData('points', 1)
                 ->setData('money', $ratePrice);
            return $rate;
        } else {
            return $this->getSpendingRate($websiteId, $quote->getCustomerGroupId());
        }
    }

    /**
     * Get the Customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer() {
        /** @var \Magento\Customer\Model\Session $session */
        $session = $this->objectManager->create(\Magento\Customer\Model\Session::class);
        if($customer = $session->getCustomer()) {
            return $customer->getDataModel();
        }
        return null;
    }

    /**
     * @return float
     */
    private function getCustomerConversion() {
        $rate = 0.0;
        $customer = $this->getCustomer();
        $currencySymbol = $this->priceCurrency->getCurrencySymbol();
        //Todo: In case of the Store loyalty Store the conversion is Just 1
        if($currencySymbol == \PSS\Loyalty\Helper\Data::CURRENCY_CODE) {
            return 1;
        } else if($customer && $customer->getId()) {
            if($conversion = $customer->getCustomAttribute('conversion_euro_point')) {
                $rate = (float)$conversion->getValue();
            }
        }
        return $rate;
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

        $rewardAccount = $this->getAccountHelper()->getByCustomerId($this->getCustomerId());
        $rate = $this->getSpendingRateByQuote($quote);
        if ($maxSpending = $this->getMaxSpendingPointsByRate($quote, $rate)) {
            $spendingConfig['rules'][] = [
                'id'              => 'rate',
                'label'           => __(
                    'Dispones de %1, ¿deseas pagar parte de tu compra con tus puntos?',
                    $rewardAccount->getBalanceFormatted()
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
     * {@inheritdoc}
     */
    public function getItemTotalForDiscount(
        $item,
        $isSpending = true,
        $isCalculateMpDiscount = true,
        $isCalculateInvitedDiscount = false
    ) {
        /** base_mp_discount_amount is the discount amount of Mageplaza extensions */
        // $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getBaseMpDiscountAmount() + $item->getMpRewardBaseDiscount();

        /*if ($isCalculateMpDiscount) {
            $total -= $item->getMpRewardBaseDiscount();
        }*/

        /*if ($isCalculateInvitedDiscount) {
            $total = $total - $item->getMpRewardInvitedBaseDiscount();
        }*/
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $isCalculateTax = $isSpending ? $this->isSpendingFromTax($item->getStoreId()) : $this->isEarnPointFromTax($item->getStoreId());
        if ($isCalculateTax) {
            $total = $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();
        } else {
            $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
        }

        return $total;
    }
}