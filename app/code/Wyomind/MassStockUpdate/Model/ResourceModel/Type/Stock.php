<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Type;

/**
 * Class Stock
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Type
 */
class Stock extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource
{

    /**
     * @var
     */
    protected $_backorders;
    /**
     * @var
     */
    protected $_minQty;
    /**
     * @var
     */
    public $fields;
    /**
     * @var
     */
    protected $_setStockStatus;
    /**
     * @var bool
     */
    public $qtyField = false;
    /**
     * @var \Magento\Framework\Module\ModuleList|null
     */
    protected $_moduleList = null;
    /**
     * @var
     */
    protected $_publicServices;

    /**
     * Stock constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\MassStockUpdate\Helper\Data $helperData
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Inventory\Model\SourceRepositoryFactory $sourceRepositoryFactory
     * @param null $connectionName
     */
    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context,
                                \Wyomind\Core\Helper\Data $coreHelper, \Wyomind\MassStockUpdate\Helper\Data $helperData,
                                \Magento\Framework\Module\ModuleList $moduleList,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
                                $connectionName = null)
    {
        $this->_moduleList = $moduleList;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $coreHelper, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     *
     */
    public function _construct()
    {

        if ($this->_moduleList->getOne("Ess_M2ePro")) {
            $this->_publicServices = $this->_objectManager->create('Ess\M2ePro\PublicServices\Product\SqlChange');
        }

        $this->table = $this->getTable("cataloginventory_stock_item");
        $this->_backorders = $this->_coreHelper->getStoreConfig("cataloginventory/item_options/backorders");
        $this->_minQty = $this->_coreHelper->getStoreConfig("cataloginventory/item_options/min_qty");
        parent::_construct();
    }

    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [5 => "cataloginventory_stock"];
    }

    /**
     *
     */
    public function reset()
    {
        $this->fields = array();
        $this->qtyField = false;
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {

        $field = $strategy['option'][0];


        if ($field == "qty") {
            $this->qtyField = (int)$value;
        }
        $this->fields[$field] = "'" . $value . "'";

        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|void
     */
    public function prepareQueries($productId, $profile)
    {

        if ($profile->getAutoSetInstock()) {

            if (is_numeric($this->qtyField) || is_string($this->qtyField)) {
                $field = $this->qtyField;
            } else {
                $field = "qty";
            }
            $this->fields["is_in_stock"] = " IF ($field > $this->_minQty OR (backorders>0 AND use_config_backorders=0) "
                . " OR (use_config_backorders=1 AND $this->_backorders>0),1,0)";
        }

        $data = $this->fields;
        $data["product_id"] = $productId;
        $data["stock_id"] = "1";


        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
        if ($this->_moduleList->getOne("Ess_M2ePro")) {
            $this->_publicServices->markQtyWasChanged($productId);
        }
        parent::prepareQueries($productId, $profile);
    }

    /**
     * @return string|void
     */
    public function afterCollect()
    {
        if ($this->_moduleList->getOne("Ess_M2ePro")) {
            $this->_publicServices->applyChanges();
        }
        parent::afterCollect();
    }

    /**
     * @return array
     */
    public function getDropdown()
    {
        $dropdown = [];
        /* STOCK MAPPING */
        $i = 0;
        if ($this->isAdvancedInventoryEnabled()) {
            $dropdown['Stocks'][$i]['label'] = __("Multi stock enabled");
            $dropdown['Stocks'][$i]["id"] = "AdvancedInventory/multistock_enabled";
            $dropdown['Stocks'][$i]['style'] = "stock";
            $dropdown['Stocks'][$i]['type'] = __("Enable multi stock management with Advanced Inventory");
            $dropdown['Stocks'][$i]['value'] = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
            $dropdown['Stocks'][$i]['options'] = $this->_helperData->getBoolean();
            $i++;
        }

//        if (!$this->_helperData->isMsiEnabled()) {
        $fields = $this->getStockFields();
        foreach ($fields as $field) {
            $dropdown['Stocks'][$i]['label'] = $field["comment"];
            $dropdown['Stocks'][$i]["id"] = "Stock/" . $field["field"];
            $dropdown['Stocks'][$i]['style'] = "stock";
            if ($field["field"] == "backorders") {
                $dropdown['Stocks'][$i]['options'] = $this->_helperData->getBackorders();

            } else if (strstr($field["type"], "smallint")) {
                $type = $this->smallint;
                $values = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
                $dropdown['Stocks'][$i]['options'] = $this->_helperData->getBoolean();

            } elseif (strstr($field["type"], "decimal")) {
                $type = $this->decimal;
                $values = "";
            }
            $dropdown['Stocks'][$i]['type'] = $type;
            $dropdown['Stocks'][$i]['value'] = $values;
            $i++;
        }
//        }


        return $dropdown;
    }

    /**
     * @return array
     */
    public function getStockFields()
    {
        $read = $this->getConnection();
        $table = $this->getTable(\Magento\CatalogInventory\Model\Stock\Item::ENTITY);

        $sql = "SHOW FULL COLUMNS FROM $table";

        $r = $read->fetchAll($sql);
        $fields = [];
        $exclude = ["item_id", "product_id", "stock_id"];

        foreach ($r as $data) {
            if (!in_array($data['Field'], $exclude)) {
                $fields[] = [
                    'field' => $data['Field'],
                    'comment' => $data['Comment'],
                    'type' => $data['Type']
                ];
            }
        }

        return $fields;
    }


    /**
     * @return bool
     */
    public function isAdvancedInventoryEnabled()
    {
        $advancedInventory = $this->_moduleList->getOne("Wyomind_AdvancedInventory");
        return $advancedInventory != null;
    }

    /**
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool|null
     */
    public function getFields($fieldset = null, $form = null, $class = null)
    {

        if ($fieldset == null) {
            return true;
        }
        $fieldset->addField(
            'auto_set_instock', 'select', [
                'name' => 'auto_set_instock',
                'label' => __('Automatic stock status update'),
                'options' => [
                    1 => __('yes'),
                    0 => __('no')
                ],
                'note' => __("Stock status will be automatically updated (in stock / out of stock)")
            ]
        );
        return $fieldset;
    }

    /**
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|bool
     */
    public function addModuleIf($profile)
    {
        if ($profile->getAutoSetInstock()) {
            return array("Stock");
        }
    }




}
