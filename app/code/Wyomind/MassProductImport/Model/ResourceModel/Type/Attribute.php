<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class Attribute
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class Attribute extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    /**
     * List of the attribute labels
     * @var array
     */
    public $_attributeLabels = [];
    /**
     * Store the option ids temporary
     * @var array
     */
    private $_OptionIdRegistry = [];
    /**
     * Store the urlRewrites processed
     * @var array
     */
    public $urlRewriteStoreViews = [];
    /**
     * Separator symbol for multiple values (multiselect attributes)
     */
    const LABEL_SEPARATOR = ",";

    /**List of visibility status
     * @todo get this dynamically
     * @var array
     */


    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Filter\FilterManagerFactory
     */
    protected $_filterManager;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $_storeRepository;


    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Framework\Filter\FilterManagerFactory $filterManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param null $connectionName
     */
    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context,
                                \Wyomind\Core\Helper\Data $coreHelper,
                                \Wyomind\MassProductImport\Helper\Data $helperData,
                                \Magento\Framework\Filter\FilterManagerFactory $filterManager,
                                \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
                                \Magento\Framework\Api\FilterBuilder $filterBuilder,
                                \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
                                \Magento\Store\Model\StoreRepository $storeRepository,
                                $connectionName = null)
    {
        $this->helperData = $helperData;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterManager = $filterManager;
        $this->_storeRepository = $storeRepository;
        parent::__construct($context, $coreHelper, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     *  Construc method
     */
    public function _construct()
    {

        $this->tableEaov = $this->getTable('eav_attribute_option_value');
        $this->tableEaos = $this->getTable('eav_attribute_option_swatch');
        $this->tableEao = $this->getTable('eav_attribute_option');


        $read = $this->getConnection();

        $select = "   SELECT eao.option_id,value,store_id,attribute_id FROM " . $this->tableEao . " AS eao
                        INNER JOIN " . $this->tableEaov . " AS eaov ON eao.option_id = eaov.option_id
                        WHERE store_id=0";

        $dropdownLabels = $read->fetchAll($select);

// collect all optiosn for swatch attributes
        foreach ($dropdownLabels as $attributeLabel) {
            $attributeId = $attributeLabel["attribute_id"];
            $optionId = $attributeLabel["option_id"];
            $this->_attributeLabels[$attributeId][$optionId] = $attributeLabel["value"];;
        }

//        $select = "   SELECT eao.option_id,value,store_id,attribute_id FROM " . $this->tableEao . " AS eao
//                        INNER JOIN " . $this->tableEaos . " AS eaov ON eao.option_id = eaov.option_id
//                        WHERE store_id=0";

//        $swatchLabels = $read->fetchAll($select);
//
//        foreach ($swatchLabels as $attributeLabel) {
//            $attributeId = $attributeLabel["attribute_id"];
//
//            $value = trim(strtolower($attributeLabel["value"]));
//            $optionId = $attributeLabel["option_id"];
//            $this->_attributeLabels[$attributeId][$value] = $value;
//        }

        parent::_construct();
    }

    /**
     * Before collecting the queries
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     * @throws \Magento\Framework\Exception\InputException
     */
    public function beforeCollect($profile, $columns)
    {
        // attribute id in the mapping
        $ids = [];

        if (isset($columns["Attribute"])) {
            foreach ($columns["Attribute"] as $column) {
                $ids[] = $column[1];
            }
// collect dropdown and swatch attribute
            $fields = array("frontend_input", "attribute_code");
            $conditions = array(
                array("in" =>
                    array(
                        "select", "multiselect"
                    )
                ),
                array("nlike" => "%country%"),
            );
            $atributes = $this->getAttributesList($fields, $conditions, true);
            foreach ($atributes as $attribute) {
                if (strpos($attribute['source_model'], "Boolean") !== false) {
                    continue;
                }
                $swatch = false;
                $swatch_type = false;
                if (isset($attribute["additional_data"])) {
                    $additional_data = json_decode($attribute["additional_data"]);
                    if (isset($additional_data->swatch_input_type)) {
                        $swatch = true;
                        if ($additional_data->swatch_input_type == 'visual') {
                            $swatch_type = 1;
                        } else if ($additional_data->swatch_input_type == 'text') {
                            $swatch_type = 0;
                        }
                    }
                }
                $this->selectAttributes[$attribute["attribute_id"]] = array(
                    "attribute_code" => $attribute["attribute_code"],
                    "swatch" => $swatch,
                    "swatch_type" => $swatch_type
                );
            }
        }


// collect all option for dropdown attribute
        if (count($ids)) {

        }
// tax class
        $this->taxClasses = $this->helperData->getTaxClasses();
        $this->visibility = $this->helperData->getVisibility();

        parent::beforeCollect($profile, $columns);
    }

    /**
     * Collect all the queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {


        list($entityType, $attributeId) = $strategy['option'];
        $attribute_code = null;
        if (isset($strategy['option'][2])) {
            $attribute_code = $strategy['option'][2];
        }

        $table = $this->getTable("catalog_product_entity_" . $entityType);

        switch ($attribute_code) {

            case "url_key":
                $value = "'" . $this->_filterManager->create()->translitUrl($value) . "'";

                break;
            case "visibility":

                $value = strtolower($value);

                if (isset($this->visibility[$value]) || in_array($value, array_map('strtolower', $this->visibility))) {
                    if (in_array($value, array_map('strtolower', $this->visibility))) {
                        $value = array_search($value, array_map('strtolower', $this->visibility));
                    }

                }
                //$strategy['storeviews'] = array(0);
                break;
            case "tax_class_id":
                if ($value == '') {
                    return;
                }
                $value = strtolower($value);

                if (isset($this->taxClasses[$value]) || in_array($value, array_map('strtolower', $this->taxClasses))) {
                    if (in_array($value, array_map('strtolower', $this->taxClasses))) {
                        $value = array_search($value, array_map('strtolower', $this->taxClasses));
                    }

                }

                $strategy['storeviews'] = array(0);
                break;
            case "status":

                $value = $this->getValue($value);
                if ($value == 0) {
                    $value = 2;
                }
                // $strategy['storeviews'] = array(0);
                break;

            default:


                $val = [];
                if ($entityType == "int" && !isset($this->selectAttributes[$attributeId])) {
                    $value = (int)$this->getValue($value);
                }
                $value = trim($value);
                if ($value == "") {
                    foreach ($strategy['storeviews'] as $storeview) {
                        if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                            $tableCpe = $this->getTable("catalog_product_entity");
                            $data = array(
                                "row_id" => "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)",
                                "store_id" => $storeview,
                                "attribute_id" => "$attributeId"
                            );

                        } else {
                            $data = array(
                                "entity_id" => "$productId",
                                "store_id" => $storeview,
                                "attribute_id" => "$attributeId",
                            );
                        }


                        $this->queries[$this->queryIndexer][] = $this->_delete($table, $data);
                    }
                    return;
                }
// if attribute is dropdown, swatch, multiselect 
                if (isset($this->selectAttributes[$attributeId])) {

                    $values = explode(self::LABEL_SEPARATOR, $value);
                    foreach ($values as $origValue) {

                        $value = $this->_helperData->getValue($origValue);

                        $parameter = $this->_helperData->prepareFields(["color" => $value], $origValue, "color");
                        $color = $parameter["color"];


                        if ($value != "") {
// if the option_id exists for this label
                            $optionId = false;
                            if (isset($this->_attributeLabels[$attributeId])) {
                                $optionId = array_search($value, $this->_attributeLabels[$attributeId]);
                            }
                            if ($optionId) {
                                $val[] = $optionId;
                            } //else the option_id and label is inserted
                            else {
                                // if option_id not yet added

                                if (!isset($this->_OptionIdRegistry[md5($attributeId . $value)])) {
                                    $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEao` (`attribute_id`) VALUES ( '$attributeId');";
                                    $this->queries[$this->queryIndexer][] = "SELECT @option_id:=LAST_INSERT_ID();";
                                    // insert new value for dropdown
                                    $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEaov` (`option_id`,`value`) VALUES ( @option_id,'" . str_replace("'", "''", $value) . "');";
                                    if ($this->selectAttributes[$attributeId]["swatch"]) {
                                        // insert new value for swatch

                                        $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEaos` (`option_id`,`type`,`value`) VALUES ( @option_id," . $this->selectAttributes[$attributeId]["swatch_type"] . "," . $color . ");";
                                    }
                                    $this->_OptionIdRegistry[md5($attributeId . $value)] = true;
                                }
                                $val[] = "(SELECT eao.option_id FROM `$this->tableEao`  eao INNER JOIN `$this->tableEaov`  eaov ON eao.option_id=eaov.option_id WHERE attribute_id='$attributeId' AND value='" . str_replace("'", "''", $value) . "' LIMIT 1)";
                            }
                        }
                    }
// basic attribute 
                } else {

                    $val[] = "'" . str_replace("'", "''", $value) . "'";
                }
                if (!count($val)) {
                    return;
                }
                $value = "CONCAT(" . implode(",',',", $val) . ")";
                break;
        }


        foreach ($strategy['storeviews'] as $storeview) {
            if ($this->_coreHelper->moduleIsEnabled("Magento_Enterprise")) {
                $tableCpe = $this->getTable("catalog_product_entity");
                $data = array(
                    "row_id" => "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)",
                    "store_id" => $storeview,
                    "attribute_id" => "$attributeId",
                    "value" => $value
                );
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
            } else {
                $data = array(
                    "entity_id" => "$productId",
                    "store_id" => $storeview,
                    "attribute_id" => "$attributeId",
                    "value" => $value
                );
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
            }
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * Dropdown entries
     * @return array
     */
    public function getDropdown()
    {

        /* ATTIBUTE MAPPING */
        $dropdown = [];
        $attributesList = $this->getAttributesList();

        $i = 0;
        foreach ($attributesList as $attribute) {
//         is_global

            if (!empty($attribute['frontend_label'])) {
                $storeviewsDependent = "";
                if ($attribute['is_global'] != 1) {
                    $storeviewsDependent = "storeviews-dependent";
                }
                $dropdown['Attributes'][$i]['label'] = $attribute['frontend_label'];
                $dropdown['Attributes'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'];
                $dropdown['Attributes'][$i]['style'] = "Attribute $storeviewsDependent";
                if ($attribute["frontend_input"] == "select") {
                    $dropdown['Attributes'][$i]['type'] = "Option value name (case sensitive)";
                    if (isset($this->_attributeLabels[$attribute['attribute_id']])) {
                        $dropdown['Attributes'][$i]['options'] = ($this->_attributeLabels[$attribute['attribute_id']]);
                        $dropdown['Attributes'][$i]['newable'] = true;
                    }
                } elseif ($attribute["frontend_input"] == "multiselect") {
                    $dropdown['Attributes'][$i]['type'] = "Option value names (case sensitive) separated by " . self::LABEL_SEPARATOR;
                    $dropdown['Attributes'][$i]['options'] = [];
                    if (isset($this->_attributeLabels[$attribute['attribute_id']])) {
                        $dropdown['Attributes'][$i]['options'] = ($this->_attributeLabels[$attribute['attribute_id']]);
                        $dropdown['Attributes'][$i]['multiple'] = true;
                        $dropdown['Attributes'][$i]['newable'] = true;
                    }
                } else {
                    $dropdown['Attributes'][$i]['type'] = $this->{$attribute['backend_type']};
                }
                $i++;
            }
        }
        if ($this->_coreHelper->moduleIsEnabled("Ves_Brand")) {

            $dropdown['Attributes'][$i]['label'] = "Venus - Brand Name";
            $dropdown['Attributes'][$i]["id"] = "Ignored/ves_brand_id";
            $dropdown['Attributes'][$i]['style'] = "Attribute";
            $dropdown['Attributes'][$i]['type'] = "Brand Id";
        }
        return $dropdown;
    }

    /**
     * index to process
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {

        $indexes = array(0 => "catalog_product_attribute");
        $storeviews = array();

        foreach ($mapping as $i => $map) {
            if (!$map->enabled) {
                continue;
            }
            $strategy = explode("/", $map->id);


            if (isset($strategy[3]) && $strategy[3] == "url_key") {

                $storeviews = array_merge($storeviews, $mapping[$i]->storeviews);
                $indexes[10] = "catalog_url";
            }
        }
        $this->urlRewriteStoreViews = array_unique($storeviews);
        if (count($storeviews) == 1 && $storeviews[0] == 0) {
            $this->urlRewriteStoreViews = array();
            $stores = $this->_storeRepository->getList();
            foreach ($stores as $store) {
                $this->urlRewriteStoreViews[] = $store["store_id"];
            }
        }

        $indexes[100] = "catalogsearch_fulltext";

        return $indexes;
    }


}

