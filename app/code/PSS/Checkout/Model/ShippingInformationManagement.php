<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;
use Alfa9\StorePickup\Model\Carrier\StorePickup;
/**
 * Class ShippingInformationManagement
 * @package PSS\Checkout\Model
 */
class ShippingInformationManagement extends \Magento\Checkout\Model\ShippingInformationManagement {
    /**
     * @var \Alfa9\StoreInfo\Api\StockistRepositoryInterface
     */
    protected $stockistRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $regionCollectionFactory;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * ShippingInformationManagement constructor.
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteAddressValidator $addressValidator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param \Alfa9\StoreInfo\Api\StockistRepositoryInterface $stockistRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\Data\CartExtensionFactory|null $cartExtensionFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory|null $shippingAssignmentFactory
     * @param \Magento\Quote\Model\ShippingFactory|null $shippingFactory
     */
    public function __construct(
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteAddressValidator $addressValidator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Alfa9\StoreInfo\Api\StockistRepositoryInterface $stockistRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory = null,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory = null,
        \Magento\Quote\Model\ShippingFactory $shippingFactory = null
    ) {
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockistRepository = $stockistRepository;
        $this->eventManager =  $eventManager;
        parent::__construct($paymentMethodManagement, $paymentDetailsFactory, $cartTotalsRepository,
            $quoteRepository, $addressValidator, $logger, $addressRepository, $scopeConfig,
            $totalsCollector, $cartExtensionFactory, $shippingAssignmentFactory, $shippingFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();
        $carrierCode = $addressInformation->getShippingCarrierCode();
        /** Add the address of the store instead of the the customer  */
        if($carrierCode == 'storepickup' || $carrierCode == 'reserveandcollect') {
            $methodCode = $addressInformation->getShippingMethodCode();
            if ($carrierCode == 'reserveandcollect') {
                $storeId = (integer)str_replace(ReserveAndCollect::PREFIX_STORE, '',$methodCode);
            } else {
                $storeId = (integer)str_replace(StorePickup::PREFIX_STORE, '',$methodCode);
            }
            $filter = $this->searchCriteriaBuilder->addFilter('id_sr', $storeId, 'eq')->create();
            try {
                $store = $this->stockistRepository->getList($filter);
                $store = current($store->getItems());
            }  catch (LocalizedException $exception) {
                $store = null;
            }
            /** @var \Alfa9\StoreInfo\Api\Data\StockistInterface $store */
            if($store && $store->getIdSr()) {
                $shippingAddress
                    ->setCustomerAddressId(null)
                    ->setPostcode($store->getPostcode())
                    ->setStreet($store->getAddress())
                    ->setCity($store->getCity())
                    ->setRegion($store->getRegion())
                    ->setCountryId($store->getCountry());

                /**
                 * Load information about the region
                 */
                $regionCollection = $this->regionCollectionFactory->create();
                $regionCollection->addFieldToFilter('default_name', ['eq' => $store->getRegion()])
                    ->addFieldToFilter('country_id', ['eq' => $store->getCountry()])
                    ->load();
                $region = $regionCollection->getFirstItem();
                /** @var \Magento\Directory\Model\Region $region */
                if(!$region || !$region->getId()) { //If there is no Region, we load a default Region
                    $regionCollection = $this->regionCollectionFactory->create();
                    $regionCollection->addFieldToFilter('default_name', ['eq' => 'ES'])
                        ->addFieldToFilter('country_id', ['eq' => 'Barcelona'])
                        ->load();
                    $region = $regionCollection->getFirstItem();
                }
                if($region && $region->getId()) {
                    $shippingAddress->setRegionId($region->getRegionId())->setRegionCode($region->getCode());
                }
            }
        }
        $result = parent::saveAddressInformation($cartId, $addressInformation);
        $this->eventManager->dispatch('after_save_shipping_information', ['cart_id' => $cartId]);
        return $result;
    }
}