<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\RewardPoints\Helper\Data
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH     = 'rewardpoints';
    const EARNING_CONFIGURATION  = '/earning';
    const SPENDING_CONFIGURATION = '/spending';
    const DISPLAY_CONFIGURATION  = '/display';
    const EMAIL_CONFIGURATION    = '/email';
    /**
     * Transaction Action Code
     */
    const ACTION_ADMIN           = 'admin';
    const ACTION_EARNING_ORDER   = 'earning_order';
    const ACTION_EARNING_REFUND  = 'earning_refund';
    const ACTION_SPENDING_ORDER  = 'spending_order';
    const ACTION_SPENDING_REFUND = 'spending_refund';
    /**
     * Transaction Action Type
     */
    const ACTION_TYPE_EARNING  = 1;
    const ACTION_TYPE_SPENDING = 2;
    const ACTION_TYPE_ADMIN    = 4;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_localeDate = $timeZone;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAccountNavigationLabel($storeId = null)
    {
        return $this->getConfigGeneral('account_navigation_label', $storeId);
    }

    /**
     * get max point per customer
     *
     * @param null $storeId
     *
     * @return int
     */
    public function getMaxPointPerCustomer($storeId = null)
    {
        return (int) $this->getConfigGeneral('maximum_point', $storeId);
    }

    /**
     * ======================================= Earning Configuration ===================================================
     * Get config earning
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigEarning($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::EARNING_CONFIGURATION . $code, $storeId);
    }

    /**
     * Get point expired
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSalesPointExpiredAfter($storeId = null)
    {
        return $this->getConfigEarning('sales_earn/point_expired', $storeId);
    }

    /**
     * Is earn point form tax
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointFromTax($storeId = null)
    {
        return (bool) $this->getConfigEarning('earn_from', $storeId);
    }

    /**
     * Is earn point form shipping
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointFromShipping($storeId = null)
    {
        return (bool) $this->getConfigEarning('earn_shipping', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRefundPointsEarn($storeId = null)
    {
        return $this->getConfigEarning('point_refund', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointAfterInvoiceCreated($storeId = null)
    {
        return $this->getConfigEarning('sales_earn/earn_point_after_invoice_created', $storeId);
    }

    /**
     * ======================================= Spending Configuration ==================================================
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigSpending($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::SPENDING_CONFIGURATION . $code, $storeId);
    }

    /**
     * Is spending on shipping fee
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isSpendingOnShippingFee($storeId = null)
    {
        return $this->getConfigSpending('spend_on_ship', $storeId);
    }

    /**
     * Get discount label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDiscountLabel($storeId = null)
    {
        $label = $this->getConfigSpending('discount_label', $storeId);

        return $this->objectManager->create(Phrase::class, ['text' => $label]);
    }

    /**
     * Is pending from tax
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isSpendingFromTax($storeId = null)
    {
        return $this->getConfigSpending('spend_on_tax', $storeId);
    }

    /**
     * Get maximum spending points per order
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMaximumSpendingPointsPerOrder($storeId = null)
    {
        return $this->getConfigSpending('maximum_point_per_order', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRestorePointAfterRefund($storeId = null)
    {
        return $this->getConfigSpending('restore_point_after_refund', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisabledSpending($storeId = null)
    {
        return !($this->isEnabled($storeId) && $this->getAccountHelper()->isCustomerLoggedIn()) &&
               $this->isRewardAccountActive();
    }

    /**
     * ======================================= Display Configuration ===================================================
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigDisplay($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::DISPLAY_CONFIGURATION . $code, $storeId);
    }

    /**
     * Is display point on top link
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisplayPointOnTopLink($storeId = null)
    {
        return $this->isEnabled($storeId)
               && $this->getAccountHelper()->isCustomerLoggedIn()
               && $this->getConfigDisplay('top_page', $storeId);
    }

    /**
     * Is disable point on mini cart
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisablePointOnMiniCart($storeId = null)
    {
        return !($this->isEnabled() && $this->getConfigDisplay(
            'mini_cart',
            $storeId
        ) && $this->isRewardAccountActive());
    }

    /**
     * Get expiration date formatted
     *
     * @param null $days
     * @param null $storeId
     *
     * @return false|string
     */
    public function getExpirationDate($days = null, $storeId = null)
    {
        if (!$days) {
            $days = $this->getSalesPointExpiredAfter($storeId);
        }

        return date('Y-m-d H:i:s', strtotime("+{$days}days"));
    }

    /**
     * Round Price
     *
     * @param $price
     *
     * @return float
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * Convert price
     *
     * @param $value
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($value, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->priceCurrency->convert($value, $scope);
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     *
     * @return string
     * @throws \Exception
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);

        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /*************************************** Transaction **************************************************************
     * @return \Mageplaza\RewardPoints\Model\Transaction
     */
    public function getTransaction()
    {
        return $this->objectManager->create(\Mageplaza\RewardPoints\Model\Transaction::class);
    }

    /*************************************** Account ******************************************************************
     * @return Account
     */
    public function getAccountHelper()
    {
        return $this->objectManager->get(Account::class);
    }

    /**
     * Check Reward Account status
     *
     * @return bool
     */
    public function isRewardAccountActive()
    {
        if ($this->getAccountHelper()->isCustomerLoggedIn()) {
            $customerId = $this->getCustomerId();
            if (!$customerId) {
                return false;
            }
            $account = $this->getAccountHelper()->getByCustomerId($customerId);

            return $account->getIsActive() ? true : false;
        }

        return true;
    }

    /**
     * Get the Customer Id from the Session instead from the cached Session object
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->objectManager->create(\Magento\Customer\Model\Session::class)->getCustomer()->getId();
    }

    /*************************************** Email ********************************************************************
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->objectManager->get(Email::class);
    }

    /**************************************** Point *******************************************************************
     * @return Point
     */
    public function getPointHelper()
    {
        return $this->objectManager->get(Point::class);
    }

    /**************************************** Calculation *******************************************************************
     * @return Calculation
     */
    public function getCalculationHelper()
    {
        return $this->objectManager->get(Calculation::class);
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (is_null($this->quote)) {
            if ($this->isAdmin()) {
                $this->quote = $this->objectManager->get(\Magento\Backend\Model\Session\Quote::class)->getQuote();
            } else {
                $this->quote = $this->objectManager->get(\Magento\Checkout\Model\Session::class)->getQuote();
            }
        }

        return $this->quote;
    }

    /**
     * @param $action
     * @param $customer
     * @param $pointAmount
     * @param $order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addTransaction($action, $customer, $pointAmount, $order)
    {
        if (is_string($customer)) {
            $customer = $this->getAccountHelper()->getCustomerById($customer);
        }
        $this->getTransaction()->createTransaction(
            $action,
            $customer,
            new DataObject([
                'point_amount'  => $pointAmount,
                'order_id'      => $order->getId(),
                'store_id'      => $order->getStoreId(),
                'extra_content' => [
                    'increment_id' => $order->getIncrementId()
                ]
            ])
        );
    }
}
