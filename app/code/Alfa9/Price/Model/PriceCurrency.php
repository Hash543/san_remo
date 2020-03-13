<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Price\Model;

use Psr\Log\LoggerInterface as Logger;
use \Magento\Directory\Model\Currency;
/**
 * Class PriceCurrency model for convert and format price value
 */
class PriceCurrency extends \Magento\Directory\Model\PriceCurrency
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Alfa9\Price\Helper\Data
     */
    protected $helperPrice;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Alfa9\Price\Helper\Data $helperPrice
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Alfa9\Price\Helper\Data $helperPrice,
        Logger $logger
    ) {
        $this->helperPrice = $helperPrice;
        parent::__construct($storeManager, $currencyFactory, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        $precision = $this->helperPrice->getPricePrecision();
        $currentCurrency = $this->getCurrency($scope, $currency);
        $convertedValue = $this->getStore($scope)->getBaseCurrency()->convert($amount, $currentCurrency);
        return round($convertedValue, $precision);
    }

    /**
     * {@inheritdoc}
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $precision = $this->helperPrice->getPricePrecision();
        return $this->createCurrency($scope, $currency)->formatPrecision($amount, $precision, [], $includeContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndFormat(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $precision = $this->helperPrice->getPricePrecision();
        $amount = $this->convert($amount, $scope, $currency);
        return $this->format($amount, $includeContainer, $precision, $scope, $currency);
    }
    /**
     * Get currency considering currency rate configuration.
     *
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @param bool $includeRate
     *
     * @return Currency
     */
    private function createCurrency($scope, $currency, $includeRate = false)
    {
        if ($currency instanceof Currency) {
            $currentCurrency = $currency;
        } elseif (is_string($currency)) {
            $currentCurrency = $this->currencyFactory->create()->load($currency);
            if ($includeRate) {
                $baseCurrency = $this->getStore($scope)->getBaseCurrency();
                $currentCurrency = $baseCurrency->getRate($currentCurrency) ? $currentCurrency : $baseCurrency;
            }
        } else {
            $currentCurrency = $this->getStore($scope)->getCurrentCurrency();
        }

        return $currentCurrency;
    }
    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round($price)
    {
        $precision = $this->helperPrice->getPricePrecision();
        return round($price, $precision);
    }
}
