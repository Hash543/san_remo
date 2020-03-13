<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class Price extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute
{
    /**
     * Separator for the group/tier prices and weee prices fields, eg: field 1 | field 2 |...
     */
    const FIELD_SEPARATOR = "|";
    /**
     * Separator for the group/tier prices and wee prices group of fields, eg:  field 1 | field 2 ~ field 1 | field 2 ~ field 1 | field 2
     */
    const LINE_SEPARATOR = "~";

    /**
     * Tier Price Attribute id
     * @var bool
     */
    protected $_tierPriceAttributeId = false;
    /**
     * List of wee attribute ids
     * @var array
     */
    protected $_weeeAttributeIds = [];
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;
    /**
     * List of the website
     * @var array
     */
    public $website = [];

    /**
     * Price constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Framework\Filter\FilterManagerFactory $filterManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Tax\Model\TaxClass\Repository $taxClassRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Store\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\MassProductImport\Helper\Data $helperData,
        \Magento\Framework\Filter\FilterManagerFactory $filterManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Store\Model\StoreManager $storeManager
    )
    {

        $this->_storeManager = $storeManager;
        parent::__construct($context, $coreHelper, $helperData, $filterManager, $entityAttributeCollection, $filterBuilder, $searchCriteriaBuilder, $storeRepository);
    }

    /**
     * Contruct
     */
    public function _construct()
    {

        $this->tableCeptp = $this->getTable("catalog_product_entity_tier_price");
        $this->tableWt = $this->getTable("weee_tax");
        parent::_construct();
    }

    /**
     * Before collection queries
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     */
    public function beforeCollect($profile, $columns)
    {
        $websites = $this->_storeManager->getWebsites();

        $data = [];
        foreach ($websites as $website) {
            $storegroups = $website->getGroupCollection();
            foreach ($storegroups as $storegroup) {
                $storeviews = $storegroup->getStoreCollection();
                foreach ($storeviews as $storeview) {
                    $data[$storeview->getStoreId()] = $website->getId();
                }
            }
        }
        $this->website = $data;


        $fields = ["backend_model"];
        $conditions = [
            ["eq" => "Magento\Catalog\Model\Product\Attribute\Backend\Tierprice"],
        ];
        $atributes = $this->getAttributesList($fields, $conditions, false);

        if (count($atributes)) {
            $this->_tierPriceAttributeId = $atributes[0]["attribute_id"];
        }

        $fields = ["backend_model"];
        $conditions = [
            ["eq" => "Magento\Weee\Model\Attribute\Backend\Weee\Tax"],
        ];
        $atributes = $this->getAttributesList($fields, $conditions, false);

        foreach ($atributes as $attribute) {
            $this->_weeeAttributeIds[] = $atributes[0]["attribute_id"];
        }
    }

    /**
     * Collect queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($entityType, $attributeId) = $strategy['option'];
        if ($attributeId == $this->_tierPriceAttributeId) {
            $prices = explode(self::LINE_SEPARATOR, $value);
            $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCeptp . " WHERE entity_id = $productId;";

            $storeviews = $strategy["storeviews"];
            $websites = [];
            if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                $tableCpe = $this->getTable("catalog_product_entity");

                $productId = "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)";
                $field = "row_id";

            } else {
                $field = "entity_id";
            }


            foreach ($prices as $price) {

                if (trim($price) != "") {
                    list($groupId, $qty, $val) = explode(self::FIELD_SEPARATOR, $price);
                    $allGroup = 0;
                    if ($groupId == "*") {
                        $allGroup = 1;
                    }
                    $percentageValue = "NULL";
                    if (substr($val, -1, 1) == "%") {
                        $percentageValue = substr($val, 0, strlen($val) - 1);
                        $val = 0;
                    }

                    foreach ($storeviews as $storeview) {

                        if ($storeview == 0 || !in_array($this->website[$storeview], $websites)) {

                            $websiteId = 0;
                            if ($storeview != 0) {
                                $websiteId = $this->website[$storeview];
                            }
                            $websites[] = $websiteId . "_" . $price;
                            $this->queries[$this->queryIndexer][] = "INSERT INTO " . $this->tableCeptp . " ($field,all_groups,customer_group_id,qty,value,percentage_value,website_id) "
                                . " VALUES ($productId,'$allGroup','$groupId','$qty','$val',$percentageValue,'" . $websiteId . "')\n ";
                        }
                    }
                }
            }

        } elseif (in_array($attributeId, $this->_weeeAttributeIds)) {
            $weees = explode(self::LINE_SEPARATOR, $value);
            $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableWt . " WHERE entity_id = $productId and attribute_id='" . $attributeId . "';";

            $storeviews = $strategy["storeviews"];
            $websites = [];
            foreach ($weees as $weee) {
                if (trim($weee) != "") {
                    list($country, $region, $tax) = explode(self::FIELD_SEPARATOR, $weee);

                    if ($region == "*") {
                        $region = 0;
                    }

                    foreach ($storeviews as $storeview) {
                        if ($storeview == 0 || !in_array($this->website[$storeview], $websites)) {
                            $websiteId = 0;
                            if ($storeview != 0) {
                                $websiteId = $this->website[$storeview];
                            }
                            $websites[] = $websiteId;
                            $this->queries[$this->queryIndexer][] = "INSERT INTO " . $this->tableWt . " (entity_id,country,value,state,website_id,attribute_id) "
                                . " VALUES ($productId,'$country','$tax','$region','" . $websiteId . "','" . $attributeId . "')\n ";
                        }
                    }
                }
            }
        } else {
//            if (trim($value) == "") {
//                return;
//            }
            parent::collect($productId, $value, $strategy, $profile);
        }
    }

    /**
     * Get dropdown entries
     * @return array
     */
    public function getDropdown()
    {

        /* ATTIBUTE MAPPING */
        $dropdown = [];
        $fields = ["backend_model", "backend_model", "attribute_code", "attribute_code"];
        $conditions = [
            ["like" =>
                [
                    "%price%"
                ],
            ],
            ["like" =>
                [
                    "%weee%"
                ],
            ],
            ["eq" =>
                [
                    "special_to_date"
                ]
            ],
            ["eq" =>
                [
                    "special_from_date"
                ]
            ],
        ];
        $attributesList = $this->getAttributesList($fields, $conditions, false);

        $i = 0;
        foreach ($attributesList as $attribute) {
            if (!empty($attribute['frontend_label'])) {
                $dropdown['Price'][$i]['style'] = "Price storeviews-dependent";
                if ($attribute['attribute_code'] == "tier_price") {
                    $dropdown['Price'][$i]['label'] = __("Tier Price / Group Price");
                    $dropdown['Price'][$i]['style'] = "Price";
                } else {
                    $dropdown['Price'][$i]['label'] = $attribute['frontend_label'];
                    $dropdown['Price'][$i]['style'] = "Price storeviews-dependent" ;
                }
                $dropdown['Price'][$i]['id'] = "Price/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'];


                if ($attribute["backend_model"] == "weee/attribute_backend_weee_tax") {
                    $dropdown['Price'][$i]['type'] = "List of fixed tax prices separated by " . self::LINE_SEPARATOR;
                    $dropdown['Price'][$i]['value'] = "[Country code 1]" . self::FIELD_SEPARATOR . "[Region Code 1]" . self::FIELD_SEPARATOR . "[Tax price 1]" . self::LINE_SEPARATOR . "[Country code 2]" . self::FIELD_SEPARATOR . "[Region Code 2]" . self::FIELD_SEPARATOR . "[Tax price 2]" . self::LINE_SEPARATOR . "...";
                } elseif ($attribute['attribute_code'] == "tier_price") {
                    $dropdown['Price'][$i]['type'] = "List of tier prices separated by " . self::LINE_SEPARATOR;
                    $dropdown['Price'][$i]['value'] = "[Group id 1]" . self::FIELD_SEPARATOR . "[Qty 1]" . self::FIELD_SEPARATOR . "[Price 1]" . self::LINE_SEPARATOR . "[Group Id 2]" . self::FIELD_SEPARATOR . "[Qty 2]" . self::FIELD_SEPARATOR . "[Price 2]" . self::LINE_SEPARATOR . "...";
                } else {
                    $dropdown['Price'][$i]['type'] = $this->{$attribute["backend_type"]};
                }
                $i++;
            }
        }

        return $dropdown;
    }

    /**
     * Get Indexes to run
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {


        return [9 => "catalog_product_price"];
    }
}
