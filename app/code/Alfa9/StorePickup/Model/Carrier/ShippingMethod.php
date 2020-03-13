<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\StorePickup\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;

/**
 * Class ShippingMethod
 * @package PSS\ShippingMethod\Model\Carrier
 */
abstract class ShippingMethod extends AbstractCarrier implements CarrierInterface {
    /**
     * Prefix of the stores
     */
    const PREFIX_STORE = '';
    const SHIPPING_CODE = '';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var \Alfa9\StorePickup\Helper\Data
     */
    protected $pickupHelper;
    /**
     * ClickAndCollect constructor.
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Alfa9\StorePickup\Helper\Data $pickupHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Alfa9\StorePickup\Helper\Data $pickupHelper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->pickupHelper = $pickupHelper;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $quoteItems = $request->getAllItems();
        $storeList = $this->getStoreList($quoteItems);
        /** @var Result $result */
        $result = $this->rateResultFactory->create();
        $storeIds = [];

        $customerCity = strtolower($request->getDestCity());
        $customerRegion = strtolower($request->getDestRegionCode());
        $customerCountry = strtolower($request->getDestCountryId());


        foreach ($storeList as $store) {
            $storeCity = isset($store['city']) ? strtolower($store['city']) : '';
            $storeRegion = isset($store['region']) ? strtolower($store['region']) : '';
            $storeCountry = isset($store['country']) ? strtolower($store['country']) : '';

            if($storeCity == $customerCity && $storeCountry == $customerCountry  &&  $storeRegion == $customerRegion){
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setData('carrier',$this->_code);
                $method->setData('carrier_title', __($this->getConfigData('title')));
                $method->setData('method', $store['method']);
                $method->setData('method_title', $store['availability'].' - '.$store['address']);
                $method->setPrice($this->getConfigData('price'));
                $method->setData('cost', $this->getConfigData('price'));
                $result->append($method);
                $storeIds[] = $store['store_id'].'=>'.$store['availability'];
            }

        }
        /** this part is to group the method */
        if(count($storeIds) > 0) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();
            $method->setData('carrier', $this->_code);
            $method->setData('carrier_title',__($this->getConfigData('title')));
            $method->setData('method_title', implode(', ', $storeIds)); //I am using the title in order to pass
            $method->setData('method', $this->_code);
            $method->setPrice(-1);
            $method->setData('cost', -1);
            $result->append($method);
        }
        return $result;
    }

    /**
     * @param array $quoteItems
     * @return array
     */
    public abstract function getStoreList($quoteItems = []);
    /**
     * getAllowedMethods
     *
     * @return array
     */
    public function getAllowedMethods() {
        return [$this->_code => $this->getConfigData('name')];
    }
}