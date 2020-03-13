<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\App\Area;

/**
 * Class ShippingTableRates
 * @package PSS\ShippingMethod\Model\Carrier
 */
class ShippingTableRates extends \Amasty\ShippingTableRates\Model\Carrier\Table {

    /**
     * @var \Alfa9\StorePickup\Helper\Data
     */
    protected $helperStorePickup;
    /**
     * @var \Amasty\ShippingTableRates\Model\ResourceModel\Label\CollectionFactory
     */
    private $labelCollectionFactory;
    /**
     * @var \Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory
     */
    private $methodCollectionFactory;
    /**
     * @var \Amasty\ShippingTableRates\Helper\Data
     */
    private $helperData;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Amasty\ShippingTableRates\Model\RateFactory
     */
    private $rateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * ShippingTableRates constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Amasty\ShippingTableRates\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory
     * @param \Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory $methodCollectionFactory
     * @param \Amasty\ShippingTableRates\Model\RateFactory $rateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amasty\ShippingTableRates\Helper\Data $helperData
     * @param \Magento\Framework\App\State $state
     * @param \Alfa9\StorePickup\Helper\Data $helperStorePickup
     * @param array $data
     */
    public  function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\ShippingTableRates\Model\ResourceModel\Label\CollectionFactory $labelCollectionFactory,
        \Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory $methodCollectionFactory,
        \Amasty\ShippingTableRates\Model\RateFactory $rateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\ShippingTableRates\Helper\Data $helperData,
        \Magento\Framework\App\State $state,
        \Alfa9\StorePickup\Helper\Data $helperStorePickup,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->state = $state;
        $this->methodCollectionFactory = $methodCollectionFactory;
        $this->rateFactory = $rateFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->helperStorePickup = $helperStorePickup;
        $this->storeManager = $storeManager;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $rateMethodFactory, $objectManager, $labelCollectionFactory, $methodCollectionFactory, $rateFactory, $storeManager, $helperData, $state, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request) {

        if (!$this->getConfigData('active')) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Label\Collection $customLabel */
        $customLabel = $this->labelCollectionFactory->create();
        /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection $methodCollection */
        $methodCollection = $this->methodCollectionFactory->create();

        try {
            $storeId = $this->state->getAreaCode() == Area::AREA_ADMINHTML
                ? $this->getStoreIdFromQuoteItem($request) : $this->storeManager->getStore()->getId();
        }catch (\Exception $exception) {
            $storeId = 0;
        }

        $methodCollection
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($storeId)
            ->addCustomerGroupFilter($this->getCustomerGroupId($request));

        /** @var \Amasty\ShippingTableRates\Model\Rate $modelRate */
        $modelRate = $this->rateFactory->create();
        $rates = $modelRate->findBy($request, $methodCollection);
        $countOfRates = 0;
        $isPackageExpress = $this->helperStorePickup->isPackageExpress($request->getAllItems());
        foreach ($methodCollection as $customMethod) {
            $methodStockExpress = (boolean)$customMethod->getData("stock_express");
            if($isPackageExpress != $methodStockExpress) {
                continue;
            }
            /** @var \Amasty\ShippingTableRates\Model\Label $customLabelData */
            $customLabelData = $customLabel->addFiltersByMethodIdStoreId($customMethod->getId(), $storeId)
                ->getLastItem();
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setData('carrier',$this->_code);
            $method->setData('carrier_title', $this->getConfigData('title'));

            if (isset($rates[$customMethod->getId()]['cost'])) {
                $method->setData('method', $this->_code . $customMethod->getId());
                $label = $this->helperData->escapeHtml($customLabelData->getData('label'));
                if ($label === null) {
                    $methodTitle = __($customMethod->getName());
                } else {
                    $methodTitle = __($label);
                }
                $methodTitle = str_replace('{day}', $rates[$customMethod->getId()]['time'], $methodTitle);
                $method->setData('method_title', $methodTitle);
                $method->setData('cost',$rates[$customMethod->getId()]['cost']);
                $method->setPrice($rates[$customMethod->getId()]['cost']);
                $method->setData('pos',$customMethod->getPos());
                $result->append($method);
                $countOfRates++;
            }
        }
        if (($countOfRates == 0) && ($this->getConfigData('showmethod') == 1)) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create();
            $error->setData('carrier', $this->_code);
            $error->setData('carrier_title',$this->getConfigData('title'));
            $error->setData('error_message', $this->getConfigData('specificerrmsg'));
            $result->append($error);
        }
        return $result;
    }
}