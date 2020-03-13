<?php
/**
 * @author Israel
 */
namespace PSS\ShippingMethod\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;
use Alfa9\StorePickup\Model\Carrier\StorePickup;
/**
 * Class ShippingMethodConverter
 * @package PSS\ShippingMethod\Model\Cart
 */
class ShippingMethodConverter {

    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $shippingMethodExtensionFactory;
    /**
     * @var \PSS\ShippingMethod\Model\StoreInfoFactory
     */
    protected $storeInfoFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var StockistRepositoryInterface
     */
    protected $stockistRepository;
    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * ShippingMethodConverter constructor.
     * @param ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param \PSS\ShippingMethod\Model\StoreInfoFactory $storeInfoFactory
     * @param StockistRepositoryInterface $stockistRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        \PSS\ShippingMethod\Model\StoreInfoFactory $storeInfoFactory,
        StockistRepositoryInterface $stockistRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeInfoFactory = $storeInfoFactory;
        $this->stockistRepository = $stockistRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
    }

    /**
     * Send Information to shipping in order to build the select with options
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $subject
     * @param \Amasty\ShippingTableRates\Model\Cart\ShippingMethod $result
     * @return \Amasty\ShippingTableRates\Model\Cart\ShippingMethod
     */
    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\ShippingMethodConverter $subject,
        \Amasty\ShippingTableRates\Model\Cart\ShippingMethod $result
    ) {
        $methodCode = $result->getMethodCode();
        if($methodCode == 'storepickup' || $methodCode == 'reserveandcollect') {
            $storesMethod = $result->getMethodTitle();
            $storesMethod = explode(',', $storesMethod);
            $storeShippingInfo = [];
            foreach ($storesMethod as $storeMethod) {
                $storeMethod = explode('=>', $storeMethod);
                if(count($storeMethod) == 2) {
                    $storeShippingInfo[trim($storeMethod[0])] = $storeMethod[1];
                }
            }
            $sort = $this->sortOrderBuilder->setField('postcode')->setDirection('ASC')->create();
            $filter = $this->searchCriteriaBuilder->addFilter('id_sr', array_keys($storeShippingInfo), 'in')->addSortOrder($sort)->create();


            try {
                $stores = $this->stockistRepository->getList($filter);
                $stores = $stores->getItems();
            }catch (LocalizedException $exception) {
                $stores =  [];
            }
            if(count($stores) > 0) {
                /** @var  \Magento\Quote\Api\Data\ShippingMethodExtensionInterface  $extensibleAttribute */
                $extensibleAttribute =  ($result->getExtensionAttributes())
                    ? $result->getExtensionAttributes()
                    : $this->shippingMethodExtensionFactory->create();
                $prefix = StorePickup::PREFIX_STORE;
                if($methodCode == ReserveAndCollect::SHIPPING_CODE) {
                    $prefix = ReserveAndCollect::PREFIX_STORE;
                }
                $storeAux = [];
                /**
                 * Get the provinces
                 * @var \Alfa9\StoreInfo\Api\Data\StockistInterface $store
                 */
                foreach ($stores as $store) {
                    $storeId = (integer)$store->getIdSr();
                    $province = $store->getCity();
                    if(!isset($storeAux[$province])) {
                        $storeAux[$province] = [];
                        $storeAux[$province]['label'] = $province;
                        $storeAux[$province]['stores'] = [];
                    }
                    $availability = isset($storeShippingInfo[$storeId]) ? $storeShippingInfo[$storeId] : __('48/72 horas');
                    $storeAux[$province]['stores'][] = [
                        'store_id' => $storeId,
                        'store_postal_code' => $store->getPostcode(),
                        'store_name' => $store->getAddress(),
                        'availability' => $availability,
                        'method' => $prefix . $storeId,
                        'label' => $store->getPostcode()." ".$store->getAddress()." ".$availability
                    ];
                }
                //Remove index of the province
                $storeGrouped = [];
                foreach ($storeAux as $store) {
                    $storeGrouped[] = $store;
                }
                /** @var \PSS\ShippingMethod\Model\StoreInfo $storeInfo */
                $storeInfo = $this->storeInfoFactory->create();
                $storeInfo->setStoreInfo(json_encode($storeGrouped));
                $extensibleAttribute->setStores($storeInfo);
                $result->setExtensionAttributes($extensibleAttribute);
            }
        }
        return $result;
    }
}