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
class Msi extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource
{
    /**
     * @var \Magento\Inventory\Model\SourceItemRepositoryFactory
     */
    protected $sourceRepositoryFactory;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory
     */
    protected $stockSourceLink;
    /**
     * Backorders config
     * @var
     */
    protected $backorders;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * Min qty config
     * @var
     */
    protected $minQty;

    /**
     * Msi constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Wyomind\MassStockUpdate\Helper\Data $helperData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper, \Wyomind\MassStockUpdate\Helper\Data $helperData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,

        $connectionName = null)
    {
        $this->objectManager = $objectManager;

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($context, $coreHelper, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     * Collect the necessary table names
     */
    public function _construct()
    {
        if ($this->_helperData->isMsiEnabled()) {
            $this->sourceRepositoryFactory = $this->objectManager->create("\Magento\Inventory\Model\SourceRepository");
            $this->stockSourceLink = $this->objectManager->create("\Magento\Inventory\Model\ResourceModel\StockSourceLink\Collection");
            foreach ($this->stockSourceLink as $stock) {
                $this->stocks[$stock->getStockId()][] = $stock->getSourceCode();
            }
        }

        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableIlsnc = $this->getTable("inventory_low_stock_notification_configuration");
        $this->tableIsi = $this->getTable("inventory_source_item");
        $this->tableIsi = $this->getTable("inventory_source_item");

        $this->backorders = $this->_coreHelper->getStoreConfig("cataloginventory/item_options/backorders");
        $this->minQty = $this->_coreHelper->getStoreConfig("cataloginventory/item_options/min_qty");
    }


    /**
     * Collect all fields and values
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($field, $sourceCode) = $strategy["option"];

        $this->fields[md5($productId)][$sourceCode]["source_code"] = $this->_helperData->sanitizeField($sourceCode);
        $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId)";
        $this->fields[md5($productId)][$sourceCode]["sku"] = $sku;
        switch ($field) {
            case "quantity":
                $value = $this->_helperData->sanitizeField($value);
                $this->fields[md5($productId)][$sourceCode][$field] = $value;


                $this->qties[md5($productId)][$sourceCode] = $value;
                $this->substractedStocks[md5($productId)][$sourceCode] = "-IFNULL((SELECT `quantity` FROM `" . $this->tableIsi . "` WHERE `source_code`=" . $sourceCode . " AND  `sku`=" . $sku . " ),0)";


                break;
            case "status":
                $this->fields[md5($productId)][$sourceCode][$field] = $this->getValue($value);
                break;
            case "notify_stock_qty":
                $this->fields[md5($productId)][$sourceCode][$field] = $this->_helperData->sanitizeField($value);
                break;
            case "notify_stock_qty_use_default":
                $value = $this->getValue($value);
                if ($value) {
                    $this->fields[md5($productId)][$sourceCode]["notify_stock_qty"] = "NULL";
                }

                break;
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * Prepare the mysql queries
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|void
     */

    public
    function prepareQueries($productId, $profile)
    {
        if (isset($this->fields[md5($productId)])) {
            foreach ($this->fields[md5($productId)] as $source) {
                $data = array();
                if (isset($source["quantity"])) {
                    $data["quantity"] = $source["quantity"];
                }

                if (isset($source["status"])) {
                    $data["status"] = $source["status"];
                }


                if (!empty($data)) {
                    $data = array_merge($data, array("source_code" => $source["source_code"], "sku" => $source["sku"]));
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableIsi, $data);
                }

                if (isset($source["notify_stock_qty"])) {
                    $notify_stock_qty = $source["notify_stock_qty"];
                    $data = array("source_code" => $source["source_code"], "sku" => $source["sku"], "notify_stock_qty" => $notify_stock_qty);
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableIlsnc, $data);
                }
            }


            $this->tableCsi = $this->getTable("cataloginventory_stock_item");
            $this->tableIsi = $this->getTable("inventory_source_item");

            foreach ($this->stocks as $stockId => $sources) {
                $go = false;
                foreach ($sources as $source) {
                    if (isset($this->fields[md5($productId)][$source])) {
                        $go = true;
                    }
                }
                if (!$go) {
                    continue;
                }

                if ($stockId == '1') {

                    $sku = "(SELECT sku from $this->tableCpe WHERE entity_id =" . $productId . ")";
                    $stockStatus = " IF ((SELECT SUM(quantity) FROM " . $this->tableIsi . " WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "')) > $this->minQty OR (backorders>0 AND use_config_backorders=0) "
                        . " OR (use_config_backorders=1 AND $this->backorders>0),1,0)";


                    $data = array(
                        "stock_id" => 1,

                        "is_in_stock" => $stockStatus,
                        "qty" => "(SELECT SUM(quantity) FROM " . $this->tableIsi . "  WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "'))",
                        "product_id" => $productId
                    );
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCsi, $data);
                } else {
                    $this->table = $this->getTable("inventory_stock_" . $stockId);
                    $sku = "(SELECT sku from $this->tableCpe WHERE entity_id =" . $productId . ")";

                    $stockStatus = " IF ((SELECT SUM(quantity) FROM " . $this->tableIsi . "  WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "')) > $this->minQty OR ((SELECT backorders FROM " . $this->tableCsi . " WHERE product_id=$productId) >0 AND (SELECT use_config_backorders FROM " . $this->tableCsi . " WHERE product_id=$productId)=0) "
                        . " OR ((SELECT use_config_backorders FROM " . $this->tableCsi . " WHERE product_id=$productId)=1 AND $this->backorders>0),1,0)";

                    $data = array(

                        "is_salable" => $stockStatus,
                        "quantity" => "(SELECT SUM(quantity) FROM " . $this->tableIsi . " WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "'))",
                        "sku" => $sku
                    );
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
                }
            }
        }
        parent::prepareQueries($productId, $profile);
    }


    /**
     * Get the dropdown options
     * @return array
     */
    public
    function getDropdown()
    {
        $dropdown = [];
        /* STOCK MAPPING */


        if ($this->_helperData->isMsiEnabled()) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $sources = $this->sourceRepositoryFactory->getList($searchCriteria);
            $i = 0;
            foreach ($sources->getItems() as $source) {
                $dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Quantity";
                $dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/quantity/" . $source->getSourceCode();
                $dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $dropdown['Multi Stock Inventory'][$i]['type'] = __("Stock for '" . $source->getName() . "'");
                $dropdown['Multi Stock Inventory'][$i]['value'] = $this->int;
                $i++;

                $dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Notify Qty";
                $dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/notify_stock_qty/" . $source->getSourceCode();
                $dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $dropdown['Multi Stock Inventory'][$i]['type'] = __("Notify the qty for '" . $source->getName() . "'");
                $dropdown['Multi Stock Inventory'][$i]['value'] = $this->int;
                $i++;

                $dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Use default for notify Qty";
                $dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/notify_stock_qty_use_default/" . $source->getSourceCode();
                $dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $dropdown['Multi Stock Inventory'][$i]['type'] = __("Use default for notify the qty for '" . $source->getName() . "'");
                $dropdown['Multi Stock Inventory'][$i]['value'] = $this->smallint;
                $dropdown['Multi Stock Inventory'][$i]['options'] = $this->_helperData->getBoolean();
                $i++;

                $dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Stock Status";
                $dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/status/" . $source->getSourceCode();
                $dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $dropdown['Multi Stock Inventory'][$i]['type'] = __("Stock status for notify the qty for '" . $source->getName() . "'");
                $dropdown['Multi Stock Inventory'][$i]['value'] = $this->smallint;
                $dropdown['Multi Stock Inventory'][$i]['options'] = $this->_helperData->getBoolean();
                $i++;
            };


        }

        return $dropdown;
    }



    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [5 => "cataloginventory_stock"];
    }
}