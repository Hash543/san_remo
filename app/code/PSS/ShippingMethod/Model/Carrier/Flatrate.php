<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
/**
 * Class Flatrate
 * @package Alfa9\Shipping\Model\Carrier
 */
class Flatrate  extends \Magento\OfflineShipping\Model\Carrier\Flatrate {
    /**
     * @var \Alfa9\StorePickup\Helper\Data
     */
    protected $helperStorePickup;
    /**
     * Flatrate constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Alfa9\StorePickup\Helper\Data $helperStorePickup
     * @param ItemPriceCalculator $itemPriceCalculator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        ItemPriceCalculator $itemPriceCalculator,
        \Alfa9\StorePickup\Helper\Data $helperStorePickup,
        array $data = []
    ) {
        $this->helperStorePickup = $helperStorePickup;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $rateMethodFactory, $itemPriceCalculator, $data);
    }

    /**
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        /** If all the items are available in 2 hours hide this method */
        if($this->helperStorePickup->isPackageExpress($request->getAllItems())) {
            return false;
        }
        $subtotal = $request->getPackageValue();
        $destinationRegion = $request->getDestRegionCode();
        $subtotalFreeShipping = $this->getConfigData('price_free_shipping');
        if($destinationRegion == 'Baleares') {
            $subtotalFreeShipping = $this->getConfigData('price_free_shipping_baleares');
        }
        if($this->getConfigData('active_free_shipping') && $subtotal >= $subtotalFreeShipping) {
            /** @var Result $result */
            $result = $this->_rateResultFactory->create();
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier('flatrate');
            $method->setCarrierTitle($this->getConfigData('title_free_shipping'));

            $method->setMethod('flatrate');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice(0.0);
            $method->setCost(0.0);
            return $result->append($method);
        } else {
            return parent::collectRates($request);
        }
    }
}