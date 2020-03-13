<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Helper;

/**
 * Class Data
 * @package Wyomind\MassProductImport\Helper
 */
class Data extends \Wyomind\MassStockUpdate\Helper\Data
{

    /**
     * @var string
     */
    public $module = "MassProductImport";

    /**
     * @var \Magento\Framework\Api\FilterBuilder|null
     */
    private $filteruilder = null;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|null
     */
    private $searchCriteriaBuilder = null;

    /**
     * @var \Magento\Tax\Model\TaxClass\Repository
     */
    protected $taxClassRepository;
    /**
     *
     */
    const TMP_FOLDER = "/var/tmp/massproductimport/";
    /**
     *
     */
    const UPLOAD_DIR = "/var/upload/";
    /**
     *
     */
    const TMP_FILE_PREFIX = "massproductimport_";
    /**
     *
     */
    const TMP_FILE_EXT = "orig";
    /**
     *
     */
    const CSV = 1;
    /**
     *
     */
    const XML = 2;
    /**
     *
     */
    const UPDATE = 1;
    /**
     *
     */
    const IMPORT = 2;
    /**
     *
     */
    const UPDATEIMPORT = 3;
    /**
     *
     */
    const MODULES = [


        10 => "System",
        20 => "Price",
        30 => "AdvancedInventory",
        40 => "Stock",
        45 => "Msi",
        50 => "Attribute",
        60 => "CustomOption",
        70 => "Image",
        80 => "Category",
        90 => "Merchandising",
        100 => "DownloadableProduct",
        110 => "ConfigurableProduct",
        120 => "MixedProduct",
//        130 => "CustomScript",
        11 => "ConfigurableProductsSystem",
        21 => "ConfigurableProductsPrice",
        41 => "ConfigurableProductsStock",
        51 => "ConfigurableProductsAttribute",
        71 => "ConfigurableProductsImage",
        81 => "ConfigurableProductsCategory",
        1000 => "Ignored"
    ];

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory
     * @param \Wyomind\MassStockUpdate\Logger\Logger $logger
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Tax\Model\TaxClass\Repository $taxClassRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory,
        \Wyomind\MassStockUpdate\Logger\Logger $logger,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Tax\Model\TaxClass\Repository $taxClassRepository)
    {

        $this->filteruilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxClassRepository = $taxClassRepository;
        parent::__construct($context, $driverFileFactory, $logger, $coreHelper, $storeManager, $attributeRepository, $objectManager);
    }


    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    function getTaxClasses()
    {
        $this->taxClasses = array();
        $this->searchCriteriaBuilder->addFilter("class_type", "PRODUCT", "eq");
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $taxClasses = $this->taxClassRepository->getList($searchCriteria);
        foreach ($taxClasses->getItems() as $taxClass) {
            $this->taxClasses[$taxClass->getClassId()] = ($taxClass->getClassName());
        }
        $this->taxClasses[0] = "None";
        return $this->taxClasses;
    }

    /**
     * @return array
     */
    public
    function getProductTypeIds()
    {
        return [
            "simple" => (string)__("Simple"),
            "configurable" => (string)__("Configurable"),
            "grouped" => (string)__("Grouped"),
            "bundle" => (string)__("Bundle"),
            "donwloadable" => (string)__("Downloadable"),
            "virtual" => (string)__("Virtual"),
            "giftcard" => (string)__("Gift Card")];

    }

    /**
     * @return array
     */
    public
    function getVisibility()
    {
        return array(
            1 => (string)__("Not Visible Individually"),
            2 => (string)__("Catalog"),
            3 => (string)__("Search"),
            4 => (string)__("Catalog, Search")
        );
    }


}
